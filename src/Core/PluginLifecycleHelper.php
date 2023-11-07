<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginLifecycleHelper
{
    public static function update(string $plugin, ContainerInterface $container): void
    {
        $connection = $container->get(Connection::class);

        if (self::c($plugin, 'NAME')) {
            try {
                /* @var $dataService DataService */
                $dataService = $container->get(DataService::class);
                $dataService->install(self::c($plugin, 'NAME'));
            } catch (\Exception) {
            }
        }

        if ($plugin::INHERITANCES && is_array($plugin::INHERITANCES)) {
            self::updateInheritances($connection, $plugin::INHERITANCES);
        }
    }

    public static function uninstall(string $plugin, ContainerInterface $container): void
    {
        $connection = $container->get(Connection::class);

        if (self::c($plugin, 'PLUGIN_TABLES')) {
            self::removePluginTables($connection, self::c($plugin, 'PLUGIN_TABLES'));
        }

        if (self::c($plugin, 'SHOPWARE_TABLES') && self::c($plugin, 'DATA_CREATED_AT')) {
            self::removePluginData($connection, self::c($plugin, 'SHOPWARE_TABLES'), self::c($plugin, 'DATA_CREATED_AT'));
        }

        if (self::c($plugin, 'INHERITANCES')) {
            self::removeInheritances($connection, self::c($plugin, 'INHERITANCES'));
        }
    }

    public static function updateInheritance(Connection $connection, string $table, string $propertyName): void
    {
        $sql = sprintf("ALTER TABLE `%s` ADD COLUMN `%s` binary(16) NULL;", $table, $propertyName);
        $connection->executeStatement($sql);
    }

    public static function removeInheritance(Connection $connection, string $table, string $propertyName): void
    {
        $sql = sprintf("ALTER TABLE `%s` DROP `%s`;", $table, $propertyName);
        $connection->executeStatement($sql);
    }

    public static function updateInheritances(Connection $connection, array $inheritances): void
    {
        foreach ($inheritances as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                if (!EntityDefinitionQueryHelper::columnExists($connection, $table, $propertyName)) {
                    self::updateInheritance($connection, $table, $propertyName);
                }
            }
        }
    }

    public static function removeInheritances(Connection $connection, array $inheritances): void
    {
        foreach ($inheritances as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                if (EntityDefinitionQueryHelper::columnExists($connection, $table, $propertyName)) {
                    self::removeInheritance($connection, $table, $propertyName);
                }
            }
        }
    }

    public static function removePluginTables(Connection $connection, array $pluginTables): void
    {
        foreach (array_reverse($pluginTables) as $table) {
            $sql = sprintf('DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }
    }

    public static function removePluginData(Connection $connection, array $shopwareTables, string $createdAt): void
    {
        foreach (array_reverse($shopwareTables) as $table) {
            $sql = sprintf("DELETE FROM `%s` WHERE `created_at` = '%s';", $table, $createdAt);

            try {
                $connection->executeStatement($sql);
            } catch (\Exception) {
                continue;
            }
        }
    }

    private static function c(string $plugin, string $constant): mixed
    {
        if (defined($plugin . "::" . $constant)) {
            return constant($plugin . "::" . $constant);
        }
        return null;
    }
}
