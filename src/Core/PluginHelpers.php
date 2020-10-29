<?php

namespace MoorlFoundation\Core;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Doctrine\DBAL\Connection;

class PluginHelpers
{
    public static function scrambleWord(string $word): string
    {
        if (strlen($word) < 2)
            return $word;
        else
            return $word{0} . str_shuffle(substr($word, 1, -1)) . $word{strlen($word) - 1};
    }

    public static function scrambleText(string $text): string
    {
        return preg_replace('/(\w+)/e', 'self::scrambleWord("\1")', $text);
    }

    public function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    public static function getNestedVar(&$context) {
        foreach ($context as $name => $item) {
            self::assignArrayByPath($context, $name, $item);
        }
    }

    public static function removeCmsBlocks($container, $context, $types)
    {
        $repo = $container->get('cms_block.repository');

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

    public static function removeCmsSlots($container, $context, $types)
    {
        $repo = $container->get('cms_slot.repository');

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

    public static function dropTables($container, $context, $tables)
    {
        $connection = $container->get(Connection::class);

        foreach ($tables as $table) {
            $connection->executeQuery('DROP TABLE IF EXISTS `' . $table . '`;');
        }
    }
}
