<?php declare(strict_types=1);

namespace MoorlFoundation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

abstract class MoorlPlugin extends Plugin
{
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
}
