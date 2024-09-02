<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexing;

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

class HtmlContentMediaFetcher
{
    private ?SymfonyStyle $console = null;
    private array $mediaCache = [];

    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly Connection $connection,
        private readonly MediaService $mediaService,
        private readonly FileSaver $fileSaver
    )
    {
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
        $versionId = Uuid::fromHexToBytes($context->getVersionId());
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
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        $sqlTemplate = "UPDATE `%s_translation` SET %s WHERE `language_id` = :languageId AND `%s` = :%s ";
        $valueUpdateTemplate = "`%s` = :%s";

        foreach ($data as $item) {
            $setters = [];

            foreach ($item as $index => &$value) {
                if ($index === $foreignKey) {
                    continue;
                }

                $value = $this->processContent($value, $entityName, $context);

                $setters[] = sprintf($valueUpdateTemplate, $index, $index);
            }

            $sql = sprintf(
                $sqlTemplate,
                $entityName,
                implode(",", $setters),
                $foreignKey,
                $foreignKey
            );

            $this->connection->executeStatement($sql, [...$item, ...[
                'languageId' => $languageId
            ]]);
        }
    }

    private function processContent(?string $content, string $entityName, Context $context): ?string
    {
        if (!$content) {
            return null;
        }

        try {
            $doc = new \DOMDocument('1.0', 'UTF-8');
            \libxml_use_internal_errors(TRUE);

            $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

            $tags = $doc->getElementsByTagName('img');
            foreach ($tags as $tag) {
                $originSrc = $tag->getAttribute('src');
                $mediaId = $this->getMediaIdFromUrl($originSrc, $entityName, $context);
                if (!$mediaId) {
                    continue;
                }
                $newSrc = $this->getMediaUrl($mediaId, $context);
                if ($newSrc === $originSrc) {
                    continue;
                }
                $tag->setAttribute('src', $newSrc);
                $tag->setAttribute('data-origin-src', $originSrc);
            }
        } catch (\Exception) {
            return $content;
        }

        return $doc->saveHTML();
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

        if (isset($this->mediaCache[$name])) {
            return $this->mediaCache[$name];
        }

        if (!str_contains($name, "http")) {
            return null;
        }

        try {
            $rawHeaders = get_headers($name, true);
        } catch (\Exception $exception) {
            $this->console->writeln(sprintf("%s %s", $exception->getMessage(), $name));
            return null;
        }

        if (!is_array($rawHeaders)) {
            return null;
        }

        $headers = [];
        foreach ($rawHeaders as $k => $v) {
            $headers[strtolower((string) $k)] = $v;
        }

        if (!empty($headers['location'])) {
            $this->console->writeln(sprintf("Path redirected from %s to %s", $name, $headers['location']));
            return $this->getMediaIdFromUrl($headers['location'], $entityName, $context);
        }

        $contentType = null;
        if (isset($headers['content-type'])) {
            $contentType = $headers['content-type'];
        }

        if ($contentType) {
            if (is_array($contentType)) {
                $type = explode("/", (string) $contentType[0]);
            } else {
                $type = explode("/", (string) $contentType);
            }

            $type = $type[0];

            if (!in_array($type, ['image', 'video'])) {
                $this->console->writeln(sprintf("Type not supported %s %s", $name, json_encode($headers)));
                return null;
            }
        } else {
            $this->console->writeln(sprintf("Not Found %s", $name));
            return null;
        }

        $name = str_replace('http:', 'https:', $name);
        $query = explode("?", $name);
        $basename = basename($query[0]);
        $fileInfo = pathinfo($basename);
        if (empty($fileInfo['filename']) && empty($fileInfo['extension'])) {
            $this->console->writeln(sprintf("Not Found or illegal file extension %s", $name));
            return null;
        } elseif (empty($fileInfo['extension'])) {
            $fileInfo['extension'] = "png";
        }

        $filename = $fileInfo['filename'];
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
            $mediaId = $this->mediaService->createMediaInFolder(
                $entityName,
                $context,
                false
            );

            try {
                $uploadedFile = $this->fetchFileFromURL($query[0], $extension);
                $this->fileSaver->persistFileToMedia(
                    $uploadedFile,
                    $filename,
                    $mediaId,
                    $context
                );
            } catch (\Exception) {
                $mediaId = null;
            }
        }

        $this->mediaCache[$name] = $mediaId;

        return $mediaId;
    }

    private function fetchFileFromURL(string $url, string $extension): MediaFile
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->query->set('url', $url);
        $request->query->set('extension', $extension);
        $request->request->set('url', $url);
        $request->request->set('extension', $extension);
        $request->headers->set('content-type', 'application/json');

        return $this->mediaService->fetchFile($request);
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
}
