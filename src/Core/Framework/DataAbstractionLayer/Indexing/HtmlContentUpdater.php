<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexing;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\Language\LanguageEntity;

class HtmlContentUpdater
{
    public function __construct(
        private readonly DefinitionInstanceRegistry $registry,
        private readonly Connection $connection
    )
    {
    }

    public function update(array $ids, string $entityName, Context $context, array $fields = []): void
    {
        $definition = $this->registry->getByEntityName($entityName);

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

        return;

        /* Maybe use multiple languages */

        $languageRepository = $this->registry->getRepository(LanguageDefinition::ENTITY_NAME);
        $languages = $languageRepository->search(new Criteria(), $context);

        /** @var LanguageEntity $language */
        foreach ($languages as $language) {
            $context = new Context(
                new SystemSource(),
                [],
                Defaults::CURRENCY,
                array_filter([$language->getId(), $language->getParentId(), Defaults::LANGUAGE_SYSTEM]),
                Defaults::LIVE_VERSION
            );

            $this->updateLanguage($ids, $entityName, $context, $fields);
        }
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

                $value = $this->processContent($value);

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

    private function processContent(?string $content): ?string
    {
        if (!$content) {
            return null;
        }

        try {
            $slugs = [];

            $doc = new \DOMDocument('1.0', 'UTF-8');
            \libxml_use_internal_errors(TRUE);

            $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD);
            $xPath = new \DOMXPath($doc);

            $tags = $xPath->query('//h2|//h3|//h4|//h5|//h6');
            foreach ($tags as $tag) {
                /* Skip if already written */
                if ($tag->getAttribute('id')) {
                    continue;
                }
                if ($tag->hasAttribute('data-no-id')) {
                    continue;
                }

                $slug = $this->slugify($tag->textContent);
                if (in_array($slug, $slugs)) {
                    $slug = Uuid::randomHex();
                }
                $slugs[] = $slug;

                $tag->setAttribute('id', $slug);
            }

            return str_replace(['<body>','</body>','<html>','</html>'],'', $doc->saveHTML());
        } catch (\Exception) {
            return $content;
        }
    }

    private function slugify(string $content): string
    {
        $slugify = new Slugify([
            'lowercase' => true,
            'separator' => '-'
        ]);

        return $slugify->slugify($content);
    }
}
