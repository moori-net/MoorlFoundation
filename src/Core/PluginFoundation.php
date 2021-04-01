<?php

namespace MoorlFoundation\Core;

use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\MailTemplate\MailTemplateActions;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Finder\Finder;
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

    /**
     * @var FilesystemInterface|null
     */
    private $filesystem;

    /**
     * @var string|null
     */
    private $projectDir;

    /**
     * @var SystemConfigService|null
     */
    private $systemConfigService;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        Connection $connection,
        ?FilesystemInterface $filesystem = null,
        ?string $projectDir = null,
        ?SystemConfigService $systemConfigService = null
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
        $this->systemConfigService = $systemConfigService;
    }

    public function removeCmsPages($ids)
    {
        foreach ($ids as $id) {
            $id = Uuid::fromHexToBytes(md5($id));

            $this->executeUpdate('DELETE FROM `cms_page_translation` WHERE `cms_page_id` = :id;', ['id' => $id]);
            $this->executeUpdate('DELETE FROM `cms_page` WHERE `id` = :id;', ['id' => $id]);
        }
    }

    public function addCmsPages($data)
    {
        foreach ($data as $item) {
            $cmsPageId = Uuid::fromHexToBytes(md5($item['technical_name']));

            $this->connection->insert(
                'cms_page',
                [
                    'id' => $cmsPageId,
                    'type' => $item['type'],
                    'entity' => $item['entity'],
                    'locked' => $item['locked'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            foreach ($item['locale'] as $locale => $localeItem) {
                $languageId = $this->getLanguageIdByLocale($locale);
                if (!$languageId) {
                    continue;
                }
                $this->connection->insert(
                    'cms_page_translation',
                    [
                        'cms_page_id' => $cmsPageId,
                        'name' => $localeItem['name'],
                        'language_id' => Uuid::fromHexToBytes($languageId),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            }
        }
    }

    public function addShippingMethods($data)
    {
        foreach ($data as $item) {
            $shippingId = Uuid::fromHexToBytes(md5($item['technical_name']));
            $deliveryTimeId = $this->getAnyEntityId('delivery_time');
            $ruleId = $this->getAnyEntityId('rule');

            $this->connection->insert(
                'shipping_method',
                [
                    'id' => $shippingId,
                    'availability_rule_id' => $ruleId,
                    'delivery_time_id' => $deliveryTimeId,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $this->connection->insert(
                'shipping_method_price',
                [
                    'id' => Uuid::randomBytes(),
                    'shipping_method_id' => $shippingId,
                    'calculation' => 1,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            foreach ($item['locale'] as $locale => $localeItem) {
                $languageId = $this->getLanguageIdByLocale($locale);
                if (!$languageId) {
                    continue;
                }
                $this->connection->insert(
                    'shipping_method_translation',
                    [
                        'shipping_method_id' => $shippingId,
                        'name' => $localeItem['name'],
                        'description' => $localeItem['description'],
                        'language_id' => Uuid::fromHexToBytes($languageId),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            }
        }
    }

    public function removeShippingMethods($ids)
    {
        foreach ($ids as $id) {
            $id = Uuid::fromHexToBytes(md5($id));

            $this->connection->executeUpdate('DELETE FROM `order_delivery` WHERE `shipping_method_id` = :id;', ['id' => $id]);
            $this->connection->executeUpdate('DELETE FROM `shipping_method_price` WHERE `shipping_method_id` = :id;', ['id' => $id]);
            $this->connection->executeUpdate('DELETE FROM `shipping_method_tag` WHERE `shipping_method_id` = :id;', ['id' => $id]);
            $this->connection->executeUpdate('DELETE FROM `shipping_method_translation` WHERE `shipping_method_id` = :id;', ['id' => $id]);
            $this->connection->executeUpdate('DELETE FROM `sales_channel_shipping_method` WHERE `shipping_method_id` = :id;', ['id' => $id]);
            $this->connection->executeUpdate('DELETE FROM `shipping_method` WHERE `id` = :id;', ['id' => $id]);
        }
    }

    public function removePluginConfig(string $pluginName): void
    {
        $repo = $this->definitionInstanceRegistry->getRepository('system_config');
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('configurationKey', $pluginName));
        $configIds = $repo->searchIds($criteria, $this->getContext());

        if ($configIds->getTotal() > 0) {
            $ids = array_map(static function ($id) {
                return ['id' => $id];
            }, $configIds->getIds());
            $repo->delete($ids, $this->getContext());
        }
    }

    public function removePluginSnippets(string $pluginName): void
    {
        if (!$this->systemConfigService->get('MoorlFoundation.config.snippets')) {
            return;
        }

        $repo = $this->definitionInstanceRegistry->getRepository('snippet');

        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('translationKey', $pluginName));
        $snippetIds = $repo->searchIds($criteria, $this->getContext());

        if ($snippetIds->getTotal() > 0) {
            $ids = array_map(static function ($id) {
                return ['id' => $id];
            }, $snippetIds->getIds());

            $repo->delete($ids, $this->getContext());
        }
    }

    public function removeAssetsFromPlugin(string $targetDir): void
    {
        $this->filesystem->deleteDir($targetDir);
    }

    public function createDir(string $targetDir): void
    {
        $this->filesystem->createDir($targetDir);
    }

    public function copyAssetsFromPlugin(string $originDir, string $targetDir): void
    {
        $this->filesystem->createDir($targetDir);

        $files = Finder::create()
            ->ignoreDotFiles(false)
            ->files()
            ->in($originDir)
            ->getIterator();

        foreach ($files as $file) {
            $fs = fopen($file->getPathname(), 'rb');
            $this->filesystem->putStream($targetDir . '/' . $file->getRelativePathname(), $fs);
            if (is_resource($fs)) {
                fclose($fs);
            }
        }
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getAppUrl(): string
    {
        return getenv('APP_URL');
    }

    public function executeUpdate(string $sql, array $params = [])
    {
        try {
            $this->connection->executeUpdate($sql, $params);
        } catch (\Exception $exception) {}
    }

    public function executeQuery(string $sql, array $params = [])
    {
        try {
            $this->connection->executeUpdate($sql, $params);
        } catch (\Exception $exception) {}
    }

    public function getLanguageIdByLocale(string $locale): ?string
    {
        if (!isset($this->languageIds[$locale])) {
            $repo = $this->definitionInstanceRegistry->getRepository('language');
            $criteria = new Criteria();
            $criteria->addAssociation('locale');
            $criteria->addFilter(new EqualsFilter('locale.code', $locale));

            $language = $repo->search($criteria, $this->getContext())->first();

            $this->languageIds[$locale] = $language ? $language->getId() : null;
        }

        return $this->languageIds[$locale];
    }

    public function addSeoUrlTemplate($data)
    {
        $repo = $this->definitionInstanceRegistry->getRepository('seo_url_template');
        $repo->upsert([$data], $this->getContext());
    }

    public function removeSeoUrlTemplate($entityname)
    {
        $this->connection->executeUpdate('DELETE FROM `seo_url_template` WHERE `entity_name` = :name;', [
            'name' => $entityname
        ]);
    }

    public function removeMediaFolder($technicalName)
    {
        $repo = $this->definitionInstanceRegistry->getRepository('media_folder');
        $criteria = new Criteria([md5($technicalName)]);
        $mediaFolderEntity = $repo->search($criteria, $this->getContext())->first();

        if (!$mediaFolderEntity) {
            return;
        }

        $mediaFolderId = Uuid::fromHexToBytes($mediaFolderEntity->getId());
        $mediaDefaultFolderId = Uuid::fromHexToBytes($mediaFolderEntity->getDefaultFolderId());
        $mediaFolderConfigurationId = Uuid::fromHexToBytes($mediaFolderEntity->getConfigurationId());

        $this->connection->executeUpdate('DELETE FROM `media_folder_configuration_media_thumbnail_size` WHERE `media_folder_configuration_id` = :id;', ['id' => $mediaFolderConfigurationId]);
        $this->connection->executeUpdate('DELETE FROM `media_folder_configuration` WHERE `id` = :id;', ['id' => $mediaFolderConfigurationId]);
        $this->connection->executeUpdate('DELETE FROM `media_default_folder` WHERE `id` = :id;', ['id' => $mediaDefaultFolderId]);
        $this->connection->executeUpdate('DELETE FROM `media_folder` WHERE `id` = :id;', ['id' => $mediaFolderId]);
    }

    public function addMediaFolder($data)
    {
        $repo = $this->definitionInstanceRegistry->getRepository('media_folder');
        $criteria = new Criteria([md5($data['technical_name'])]);
        $result = $repo->searchIds($criteria, $this->getContext());

        if ($result->getTotal() != 0) {
            return;
        }

        $repo = $this->definitionInstanceRegistry->getRepository('media_thumbnail_size');
        $mediaThumbnailSizeCollection = $repo->search(new Criteria(), $this->getContext())->getEntities();

        $mediaFolderId = Uuid::fromHexToBytes(md5($data['technical_name']));
        $mediaDefaultFolderId = Uuid::randomBytes();
        $mediaFolderConfigurationId = Uuid::randomBytes();

        $this->connection->insert(
            'media_default_folder',
            [
                'id' => $mediaDefaultFolderId,
                'association_fields' => json_encode($data['association_fields']),
                'entity' => $data['entity'],
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $this->connection->insert(
            'media_folder_configuration',
            [
                'id' => $mediaFolderConfigurationId,
                'media_thumbnail_sizes_ro' => serialize($mediaThumbnailSizeCollection),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $this->connection->insert(
            'media_folder',
            [
                'id' => $mediaFolderId,
                'default_folder_id' => $mediaDefaultFolderId,
                'name' => $data['name'],
                'media_folder_configuration_id' => $mediaFolderConfigurationId,
                'use_parent_configuration' => 0,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        foreach ($mediaThumbnailSizeCollection as $mediaThumbnailSizeEntity) {
            $mediaThumbnailSizeId = Uuid::fromHexToBytes($mediaThumbnailSizeEntity->getId());

            $this->connection->insert(
                'media_folder_configuration_media_thumbnail_size',
                [
                    'media_folder_configuration_id' => $mediaFolderConfigurationId,
                    'media_thumbnail_size_id' => $mediaThumbnailSizeId
                ]
            );
        }
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
        if (!$this->systemConfigService->get('MoorlFoundation.config.cmsElements')) {
            return;
        }
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
        if (!$this->systemConfigService->get('MoorlFoundation.config.cmsElements')) {
            return;
        }
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
            $this->connection->executeUpdate('DROP TABLE IF EXISTS `' . $table . '`;');
        }
        return;

        if ($this->systemConfigService->get('MoorlFoundation.config.renameTables')) {
            foreach ($tables as $table) {
                /* BUG: Constraints will not be renamed! */
                $this->connection->executeUpdate('RENAME TABLE `' . $table . '` TO `x_' . $table . '`;');
            }
        } else {
            foreach ($tables as $table) {
                $this->connection->executeUpdate('DROP TABLE IF EXISTS `' . $table . '`;');
            }
        }
    }

    public function updateCustomFields(array $data, string $param): void
    {
        $this->removeCustomFields($param);
        $this->addCustomFields($data, $param);
    }

    public function removeCustomFields(string $param): void
    {
        if (!$this->systemConfigService->get('MoorlFoundation.config.customFields')) {
            return;
        }

        $customFieldIds = $this->getCustomFieldIds($param);
        if ($customFieldIds->getTotal() > 0) {
            $ids = array_map(static function ($id) {
                return ['id' => $id];
            }, $customFieldIds->getIds());
            $repo = $this->definitionInstanceRegistry->getRepository('custom_field');
            $repo->delete($ids, $this->getContext());
        }

        $customFieldSetIds = $this->getCustomFieldSetIds($param);
        if ($customFieldSetIds->getTotal() > 0) {
            $ids = array_map(static function ($id) {
                return ['id' => $id];
            }, $customFieldSetIds->getIds());
            $repo = $this->definitionInstanceRegistry->getRepository('custom_field_set');
            $repo->delete($ids, $this->getContext());
        }

        $snippetIds = $this->getSnippetIds("customFields." . $param);
        if ($snippetIds->getTotal() > 0) {
            $ids = array_map(static function ($id) {
                return ['id' => $id];
            }, $snippetIds->getIds());
            $repo = $this->definitionInstanceRegistry->getRepository('snippet');
            $repo->delete($ids, $this->getContext());
        }
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

    public function getSnippetIds(string $param): IdSearchResult
    {
        $repo = $this->definitionInstanceRegistry->getRepository('snippet');
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('translationKey', $param));
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
            $this->connection->executeUpdate('DELETE FROM `event_action` WHERE `event_name` = :eventName;', ['eventName' => $eventName]);
        }
    }

    public function removeMailTemplates(array $names, $deleteAll = null): void
    {
        if (!$this->systemConfigService->get('MoorlFoundation.config.mailTemplates')) {
            return;
        }

        foreach ($names as $name) {
            $id = Uuid::fromHexToBytes(md5($name));
            $this->connection->executeUpdate('DELETE FROM `mail_template` WHERE `id` = :id;', ['id' => $id]);
            if ($deleteAll) {
                $this->connection->executeUpdate('DELETE FROM `mail_template_type` WHERE `id` = :id;', ['id' => $id]);
                //$this->connection->executeUpdate('DELETE FROM `mail_template_type_translation` WHERE `mail_template_type_id` = :id;', ['id' => $id]);
                $this->connection->executeUpdate('DELETE FROM `mail_template` WHERE `mail_template_type_id` IS NULL;');
            }
        }
    }

    public function addMailTemplates(array $data): void
    {
        foreach ($data as $item) {
            $mailTemplateTypeId = Uuid::fromHexToBytes(md5($item['technical_name']));
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

            /* After Refresh just Update the base mail template */
            $mailTemplateId = $mailTemplateTypeId;
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
                        'action_name' => isset($item['action_name']) ? $item['action_name'] : MailTemplateActions::MAIL_TEMPLATE_MAIL_SEND_ACTION,
                        'config' => json_encode([
                            'mail_template_type_id' => md5($item['technical_name']),
                            'mail_template_id' => md5($item['technical_name'])
                        ]),
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            } catch (\Exception $exception) {
            }
        }
    }

    private function getAnyEntityId(string $entity): string
    {
        $sql = 'SELECT `id` FROM `' . $entity . '` LIMIT 1';
        $id = $this->connection->executeUpdate($sql)->fetchColumn();
        if (!$id) {
            throw new \RuntimeException(sprintf('Entity "%s" not found.', $entity));
        }
        return $id;
    }
}