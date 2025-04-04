<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Plugin\PluginException;
use Shopware\Core\Kernel;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PluginLifecycleHelper
{
    public static function build(ContainerBuilder $container, string|array $paths = []): void
    {
        if (class_exists(AbstractElasticsearchDefinition::class)) {
            $loader = new XmlFileLoader($container, new FileLocator($paths));
            $loader->load('services.xml');
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $path
     * @return void
     * @throws \Exception
     *
     * Copy from: https://developer.shopware.com/docs/guides/plugins/plugins/plugin-fundamentals/logging.html
     */
    public static function loadYaml(ContainerBuilder $container, string $path): void
    {
        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = \rtrim($path, '/') . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
    }

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

        if (self::c($plugin, 'INHERITANCES')) {
            self::updateInheritances($connection, self::c($plugin, 'INHERITANCES'));
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

        if (self::c($plugin, 'NAME')) {
            try {
                /* @var $dataService DataService */
                $dataService = $container->get(DataService::class);
                $dataService->remove(self::c($plugin, 'NAME'));
            } catch (\Exception) {
            }
        }

        self::removeMigrations($connection, $plugin);
    }

    public static function removeMigrations(Connection $connection, string $plugin): void
    {
        $class = new \ReflectionClass($plugin);
        $class = addcslashes($class->getNamespaceName(), '\\_%') . '%';
        $connection->executeStatement('DELETE FROM migration WHERE class LIKE :class', ['class' => $class]);
    }

    public static function updateInheritance(Connection $connection, string $table, string $propertyName): void
    {
        if (EntityDefinitionQueryHelper::columnExists($connection, $table, $propertyName)) {
            return;
        }

        $sql = sprintf("ALTER TABLE `%s` ADD COLUMN `%s` binary(16) NULL;", $table, $propertyName);
        $connection->executeStatement($sql);
    }

    public static function removeInheritance(Connection $connection, string $table, string $propertyName): void
    {
        if (!EntityDefinitionQueryHelper::columnExists($connection, $table, $propertyName)) {
            return;
        }

        $sql = sprintf("ALTER TABLE `%s` DROP `%s`;", $table, $propertyName);
        $connection->executeStatement($sql);
    }

    public static function updateInheritances(Connection $connection, array $inheritances): void
    {
        foreach ($inheritances as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                self::updateInheritance($connection, $table, $propertyName);
            }
        }
    }

    public static function removeInheritances(Connection $connection, array $inheritances): void
    {
        foreach ($inheritances as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                self::removeInheritance($connection, $table, $propertyName);
            }
        }
    }

    public static function removePluginTables(Connection $connection, array $pluginTables): void
    {
        self::removeForeignKeys($connection, $pluginTables);

        foreach (array_reverse($pluginTables) as $table) {
            $sql = sprintf('DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }
    }

    public static function removeForeignKeys(Connection $connection, array $pluginTables): void
    {
        foreach (array_reverse($pluginTables) as $table) {
            $foreignKeys = $connection->fetchAllAssociative(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = :table AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
                ['table' => $table]
            );

            foreach ($foreignKeys as $fk) {
                $constraint = $fk['CONSTRAINT_NAME'];
                $sql = sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`;", $table, $constraint);
                $connection->executeStatement($sql);
            }
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
