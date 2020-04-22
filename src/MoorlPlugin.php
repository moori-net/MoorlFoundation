<?php declare(strict_types=1);

namespace MoorlFoundation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

abstract class MoorlPlugin extends Plugin
{
    protected function addMailTemplates($data)
    {
        $connection = $this->container->get(Connection::class);

        foreach ($data as $item) {
            $mailTemplateTypeId = Uuid::fromHexToBytes(md5($item['technical_name']));

            $connection->insert(
                'mail_template_type',
                [
                    'id' => $mailTemplateTypeId,
                    'technical_name' => $item['technical_name'],
                    'available_entities' => json_encode($item['availableEntities']),
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $mailTemplateId = Uuid::randomBytes();

            $connection->insert(
                'mail_template',
                [
                    'id' => $mailTemplateId,
                    'mail_template_type_id' => $mailTemplateTypeId,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $connection->insert(
                'event_action',
                [
                    'id' => Uuid::randomBytes(),
                    'event_name' => $item['event_name'],
                    'action_name' => $item['action_name'],
                    'config' => json_encode(['mail_template_type_id' => md5($item['technical_name'])]),
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            foreach ($item['locale'] as $locale => $localeItem) {
                $languageId = $this->getLanguageIdByLocale($locale);

                $connection->insert(
                    'mail_template_type_translation',
                    [
                        'mail_template_type_id' => $mailTemplateTypeId,
                        'name' => $localeItem['name'],
                        'language_id' => $languageId,
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );

                $connection->insert(
                    'mail_template_translation',
                    [
                        'mail_template_id' => $mailTemplateId,
                        'language_id' => $languageId,
                        'sender_name' => '{{ salesChannel.name }}',
                        'subject' => $localeItem['name'] . ' - {{ salesChannel.name }}',
                        'description' => $localeItem['description'],
                        'content_html' => $localeItem['content_html'],
                        'content_plain' => $localeItem['content_plain'],
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            }
        }
    }

    protected function removeMailTemplates($ids)
    {
        $connection = $this->container->get(Connection::class);
        foreach ($ids as $id) {
            $connection->executeQuery('DELETE FROM `mail_template_type` WHERE `id` = :id;', ['id' => $id]);
            $connection->executeQuery('DELETE FROM `mail_template` WHERE `mail_template_type_id` = :id;', ['id' => $id]);
            $connection->executeQuery('DELETE FROM `mail_template_type_translation` WHERE `mail_template_type_id` = :id;', ['id' => $id]);
        }
    }

    protected function removeEventActions($eventNames)
    {
        $connection = $this->container->get(Connection::class);
        foreach ($eventNames as $eventName) {
            $connection->executeQuery('DELETE FROM `event_action` WHERE `event_name` = :eventName;', ['eventName' => $eventName]);
        }
    }

    protected function removeCmsBlocks(Context $context, $types)
    {
        $repo = $this->container->get('cms_block.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', $types));

        $result = $repo->searchIds($criteria, $context);

        if ($result->getTotal() == 0) {
            return;
        }

        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $result->getIds());

        $repo->delete($ids, $context);
    }

    protected function removeCmsSlots(Context $context, $types)
    {
        $repo = $this->container->get('cms_slot.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', $types));

        $result = $repo->searchIds($criteria, $context);

        if ($result->getTotal() == 0) {
            return;
        }

        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $result->getIds());

        $repo->delete($ids, $context);
    }

    protected function dropTables($tables)
    {
        $connection = $this->container->get(Connection::class);
        foreach ($tables as $table) {
            $connection->executeQuery('DROP TABLE IF EXISTS `' . $table . '`;');
        }
    }

    protected function addCustomFields(Context $context, $data, $param): void
    {
        $customFieldIds = self::getCustomFieldIds($context, $param);
        if ($customFieldIds->getTotal() !== 0) {
            return;
        }
        $repo = $this->container->get('custom_field_set.repository');
        $repo->create($data, $context);
    }

    protected function getCustomFieldIds(Context $context, $param): IdSearchResult
    {
        $repo = $this->container->get('custom_field.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('name', $param));
        return $repo->searchIds($criteria, $context);
    }

    protected function getCustomFieldSetIds(Context $context, $param): IdSearchResult
    {
        $repo = $this->container->get('custom_field_set.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $param));
        return $repo->searchIds($criteria, $context);
    }

    protected function removeCustomFields(Context $context, $param)
    {
        $customFieldIds = $this->getCustomFieldIds($context, $param);
        if ($customFieldIds->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $customFieldIds->getIds());
        $repo = $this->container->get('custom_field.repository');
        $repo->delete($ids, $context);
        $customFieldSetIds = $this->getCustomFieldSetIds($context, $param);
        if ($customFieldSetIds->getTotal() == 0) {
            return;
        }
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $customFieldSetIds->getIds());
        $repo = $this->container->get('custom_field_set.repository');
        $repo->delete($ids, $context);
    }

    private function getLanguageIdByLocale(string $locale): string
    {
        $connection = $this->container->get(Connection::class);
        $sql = <<<SQL
SELECT `language`.`id` 
FROM `language` 
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;
        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchColumn();
        if (!$languageId) {
            throw new \RuntimeException(sprintf('Language for locale "%s" not found.', $locale));
        }
        return $languageId;
    }
}
