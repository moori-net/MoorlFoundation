<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexing;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;

class HtmlContentMediaFetcher
{
    private SymfonyStyle $console;
    private array $mediaCache = [];

    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly Connection $connection,
        private readonly MediaService $mediaService,
        private readonly FileSaver $fileSaver
    ) {
        $this->console = new ShopwareStyle(new ArgvInput(), new NullOutput());
    }

    public function update(array $ids, string $entityName, Context $context, array $fields = []): void
    {
        $definition = $this->definitionInstanceRegistry->getByEntityName($entityName);

        if (empty($fields)) {
            /** @var Field $field */
            foreach ($definition->getFields() as $field) {
                if (!$field instanceof StorageAware) {
                    continue;
                }

                if ($field->getFlag(AllowHtml::class)) {
                    $fields[] = $field->getStorageName();
                }
            }
        }

        if (empty($fields)) {
            return;
        }

        $this->updateLanguage($ids, $entityName, $context, $fields);
    }

    private function updateLanguage(array $ids, string $entityName, Context $context, array $fields): void
    {
        $languageId = Uuid::fromHexToBytes($context->getLanguageId());

        $foreignKey = sprintf("%s_id", $entityName);

        $sql = <<<SQL
SELECT `%s`,`%s` FROM `%s_translation` WHERE `language_id` = :languageId AND `%s` IN (:ids);
SQL;
        $sql = sprintf(
            $sql,
            $foreignKey,
            implode("`,`", $fields),
            $entityName,
            $foreignKey
        );

        $data = $this->connection->fetchAllAssociative(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids), 'languageId' => $languageId],
            ['ids' => ArrayParameterType::STRING]
        );

        $sqlTemplate = "UPDATE `%s_translation` SET %s WHERE `language_id` = :languageId AND `%s` = :%s ";
        $valueUpdateTemplate = "`%s` = :%s";

        foreach ($data as $item) {
            $setters = [];
            $parameters = [
                $foreignKey => $item[$foreignKey],
                'languageId' => $languageId,
            ];

            foreach ($item as $index => &$value) {
                if ($index === $foreignKey) {
                    continue;
                }

                $processedValue = $this->processContent($value, $entityName, $context);
                if ($processedValue === $value) {
                    continue;
                }

                $value = $processedValue;
                $parameters[$index] = $value;

                $setters[] = sprintf($valueUpdateTemplate, $index, $index);
            }

            if (!$setters) {
                continue;
            }

            $sql = sprintf(
                $sqlTemplate,
                $entityName,
                implode(",", $setters),
                $foreignKey,
                $foreignKey
            );

            $this->connection->executeStatement($sql, $parameters);
        }
    }

    private function processContent(?string $content, string $entityName, Context $context): ?string
    {
        if ($content === null || $content === '') {
            return $content;
        }

        $previousInternalErrors = \libxml_use_internal_errors(true);

        try {
            $doc = new \DOMDocument('1.0', 'UTF-8');

            if (!$doc->loadHTML(
                mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, 0xFFFFFF], 'UTF-8'),
                LIBXML_HTML_NODEFDTD
            )) {
                return $content;
            }

            $tags = $doc->getElementsByTagName('img');
            $hasChanges = false;
            foreach ($tags as $tag) {
                $originSrc = $tag->getAttribute('src');
                $originalSrc = $tag->hasAttribute('data-origin-src')
                    ? $tag->getAttribute('data-origin-src')
                    : $originSrc;
                $mediaId = $this->getMediaIdFromUrl($originSrc, $entityName, $context);
                if (!$mediaId) {
                    continue;
                }
                $newSrc = $this->getMediaUrl($mediaId, $context);
                if ($newSrc === $originSrc) {
                    continue;
                }
                $tag->setAttribute('src', $newSrc);
                $tag->setAttribute('data-origin-src', $originalSrc);
                $hasChanges = true;
            }
        } catch (\Throwable) {
            return $content;
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($previousInternalErrors);
        }

        if (!$hasChanges) {
            return $content;
        }

        return str_replace(['<body>', '</body>', '<html>', '</html>'], '', $doc->saveHTML());
    }

    private function getMediaUrl(string $id, Context $context): string
    {
        $criteria = new Criteria([$id]);
        $repository = $this->definitionInstanceRegistry->getRepository(MediaDefinition::ENTITY_NAME);
        /** @var MediaEntity $media */
        $media = $repository->search($criteria, $context)->first();
        return $media->getUrl();
    }

    private function getMediaIdFromUrl(string $name, string $entityName, Context $context): ?string
    {
        $name = trim($name);
        $name = str_replace("%22", "", $name);

        $name = $this->handleRelativeMediaUrl($name);

        if (array_key_exists($name, $this->mediaCache)) {
            return $this->mediaCache[$name];
        }

        if (!$this->isHttpUrl($name)) {
            $this->console->writeln(sprintf('Not Found or illegal media URL %s', $name));

            return null;
        }

        $path = (string) parse_url($name, PHP_URL_PATH);
        $basename = basename($path);
        $fileInfo = pathinfo($basename);
        if (empty($fileInfo['filename']) && empty($fileInfo['extension'])) {
            $this->console->writeln(sprintf('Not Found or illegal file extension %s', $name));

            return null;
        } elseif (empty($fileInfo['extension'])) {
            $fileInfo['extension'] = "png";
        }

        $filename = substr($fileInfo['filename'], 0, 200) . '-' . substr(hash('sha256', $name), 0, 16);
        $extension = $fileInfo['extension'];

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('fileName', $filename),
            new EqualsFilter('fileExtension', $extension),
        );

        $repository = $this->definitionInstanceRegistry->getRepository('media');
        $media = $repository->search($criteria, $context)->first();

        if ($media) {
            $mediaId = $media->getId();
        } else {
            $mediaId = null;
            $uploadedFile = null;

            try {
                $mediaId = $this->mediaService->createMediaInFolder(
                    $entityName,
                    $context,
                    false
                );
                $uploadedFile = $this->fetchFileFromURL($name, $extension);
                $this->fileSaver->persistFileToMedia(
                    $uploadedFile,
                    $filename,
                    $mediaId,
                    $context
                );
            } catch (\Throwable $exception) {
                $this->console->writeln(sprintf('Could not import media from %s: %s', $name, $exception->getMessage()));

                if ($mediaId) {
                    try {
                        $repository->delete([['id' => $mediaId]], $context);
                    } catch (\Throwable) {
                    }
                }
                $mediaId = null;
            } finally {
                if ($uploadedFile && is_file($uploadedFile->getFileName())) {
                    @unlink($uploadedFile->getFileName());
                }
            }
        }

        $this->mediaCache[$name] = $mediaId;

        return $mediaId;
    }

    private function fetchFileFromURL(string $url, string $extension): MediaFile
    {
        $tempFile = tempnam(sys_get_temp_dir(), '');
        if ($tempFile === false) {
            throw new \RuntimeException('Unable to create a temporary file for the media download.');
        }

        $request = new Request();
        $request->query->set('url', $url);
        $request->query->set('extension', $extension);
        $request->request->set('url', $url);
        $request->request->set('extension', $extension);
        $request->headers->set('content-type', 'application/json');

        try {
            return $this->mediaService->fetchFile($request, $tempFile);
        } catch (\Throwable $exception) {
            @unlink($tempFile);

            throw $exception;
        }
    }

    private function handleRelativeMediaUrl(string $name): string
    {
        if (str_contains($name, "http")) {
            return $name;
        }

        //$url = parse_url($this->activeImportUrl, PHP_URL_SCHEME).'://'.parse_url($this->activeImportUrl, PHP_URL_HOST);
        //$baseUrl = trim($url, '/');
        //return $baseUrl . $name;

        return $name;
    }

    private function isHttpUrl(string $url): bool
    {
        $parts = parse_url($url);

        return is_array($parts)
            && isset($parts['scheme'], $parts['host'])
            && in_array(strtolower($parts['scheme']), ['http', 'https'], true);
    }
}
