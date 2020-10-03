<?php

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Uuid\Uuid;

class PluginFoundation
{
    /**
     * @var DefinitionInstanceRegistry
     */
    private $definitionInstanceRegistry;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Array
     */
    private $languageIds;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        Connection $connection
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->connection = $connection;
    }

    public function getLanguageIdByLocale(string $locale): ?string
    {
        if (!isset($this->languageIds[$locale])) {
            $repo = $this->definitionInstanceRegistry->getRepository('language');
            $criteria = new Criteria();
            $criteria->addAssociation('locale');
            $criteria->addFilter(new EqualsFilter('locale.code', $locale));

            $this->languageIds[$locale] = $repo->search($criteria, $this->getContext())->first()->getId();
        }

        return $this->languageIds[$locale];
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context): void
    {
        $this->context = $context;
    }

    public function removeCmsSlots(array $types): void
    {
        $repo = $this->definitionInstanceRegistry->getRepository('cms_slot');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', $types));
        $result = $repo->searchIds($criteria, $this->getContext());
        if ($result->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $result->getIds());
        $repo->delete($ids, $this->getContext());
    }

    public function removeCmsBlocks(array $types): void
    {
        $repo = $this->definitionInstanceRegistry->getRepository('cms_block');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', $types));
        $result = $repo->searchIds($criteria, $this->getContext());
        if ($result->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $result->getIds());
        $repo->delete($ids, $this->getContext());
    }

    public function dropTables(array $tables): void
    {
        foreach ($tables as $table) {
            $this->connection->executeQuery('DROP TABLE IF EXISTS `' . $table . '`;');
        }
    }

    public function updateCustomFields(array $data, string $param): void
    {
        $this->removeCustomFields($param);
        $this->addCustomFields($data, $param);
    }

    public function removeCustomFields(string $param): void
    {
        $customFieldIds = $this->getCustomFieldIds($param);
        if ($customFieldIds->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $customFieldIds->getIds());
        $repo = $this->definitionInstanceRegistry->getRepository('custom_field');
        $repo->delete($ids, $this->getContext());
        $customFieldSetIds = $this->getCustomFieldSetIds($param);
        if ($customFieldSetIds->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $customFieldSetIds->getIds());
        $repo = $this->definitionInstanceRegistry->getRepository('custom_field_set');
        $repo->delete($ids, $this->getContext());
    }

    public function getCustomFieldIds(string $param): IdSearchResult
    {
        $repo = $this->definitionInstanceRegistry->getRepository('custom_field');
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('name', $param));
        return $repo->searchIds($criteria, $this->getContext());
    }

    public function getCustomFieldSetIds(string $param): IdSearchResult
    {
        $repo = $this->definitionInstanceRegistry->getRepository('custom_field_set');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $param));
        return $repo->searchIds($criteria, $this->getContext());
    }

    public function addCustomFields(array $data, string $param): void
    {
        $customFieldIds = $this->getCustomFieldIds($param);
        if ($customFieldIds->getTotal() !== 0) {
            return;
        }
        $repo = $this->definitionInstanceRegistry->getRepository('custom_field_set');
        $repo->create($data, $this->getContext());
    }

    public function removeEventActions(array $eventNames): void
    {
        foreach ($eventNames as $eventName) {
            $this->connection->executeQuery('DELETE FROM `event_action` WHERE `event_name` = :eventName;', ['eventName' => $eventName]);
        }
    }

    public function removeMailTemplates(array $names, $deleteAll = null): void
    {
        foreach ($names as $name) {
            $id = Uuid::fromHexToBytes(md5($name));
            $this->connection->executeQuery('DELETE FROM `mail_template` WHERE `id` = :id;', ['id' => $id]);
            if ($deleteAll) {
                $this->connection->executeQuery('DELETE FROM `mail_template_type` WHERE `id` = :id;', ['id' => $id]);
                $this->connection->executeQuery('DELETE FROM `mail_template_type_translation` WHERE `mail_template_type_id` = :id;', ['id' => $id]);
                $this->connection->executeQuery('DELETE FROM `mail_template` WHERE `mail_template_type_id` IS NULL;');
            }
        }
    }

    public function addMailTemplates(array $data): void
    {
        foreach ($data as $item) {
            $mailTemplateTypeId = Uuid::fromHexToBytes(md5($item['technical_name']));
            $mailTemplateId = $mailTemplateTypeId;
            /* After Refresh just Update the base mail template */
            $this->connection->insert(
                'mail_template',
                [
                    'id' => $mailTemplateId,
                    'system_default' => 1,
                    'mail_template_type_id' => $mailTemplateTypeId,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            foreach ($item['locale'] as $locale => $localeItem) {
                $languageId = $this->getLanguageIdByLocale($locale);
                if (!$languageId) {
                    continue;
                }
                $this->connection->insert(
                    'mail_template_translation',
                    [
                        'mail_template_id' => $mailTemplateId,
                        'language_id' => Uuid::fromHexToBytes($languageId),
                        'sender_name' => '{{ salesChannel.name }}',
                        'subject' => $localeItem['name'] . ' - {{ salesChannel.name }}',
                        'description' => $localeItem['description'],
                        'content_html' => $localeItem['content_html'],
                        'content_plain' => $localeItem['content_plain'],
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            }
            try {
                $this->connection->insert(
                    'event_action',
                    [
                        'id' => Uuid::randomBytes(),
                        'event_name' => $item['event_name'],
                        'action_name' => $item['action_name'],
                        'config' => json_encode(['mail_template_type_id' => md5($item['technical_name'])]),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            } catch (\Exception $exception) {
            }
            try {
                $this->connection->insert(
                    'mail_template_type',
                    [
                        'id' => $mailTemplateTypeId,
                        'technical_name' => $item['technical_name'],
                        'available_entities' => json_encode($item['availableEntities']),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
                foreach ($item['locale'] as $locale => $localeItem) {
                    $languageId = $this->getLanguageIdByLocale($locale);
                    if (!$languageId) {
                        continue;
                    }
                    $this->connection->insert(
                        'mail_template_type_translation',
                        [
                            'mail_template_type_id' => $mailTemplateTypeId,
                            'name' => $localeItem['name'],
                            'language_id' => Uuid::fromHexToBytes($languageId),
                            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                        ]
                    );
                }
            } catch (\Exception $exception) {
            }
        }
    }
}