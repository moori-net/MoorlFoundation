<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\MigrationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginMigrationHelper
{
    public static function update(string $plugin, ContainerInterface $container): void
    {
        $migrationService = $container->get(MigrationService::class);
        $migrationService->createMigration(
            self::getPluginConstant($plugin, 'NAME'),
            false,
            true,
            true
        );
    }

    public static function uninstall(string $plugin, ContainerInterface $container): void
    {
        $pluginTables = self::getPluginConstant($plugin, 'PLUGIN_TABLES');
        if (!$pluginTables) {
            return;
        }

        $connection = $container->get(Connection::class);

        PluginLifecycleHelper::removePluginTables($connection, $pluginTables);
    }

    private static function getPluginConstant(string $plugin, string $constant): mixed
    {
        $constantName = $plugin . "::" . $constant;
        return defined($constantName) ? constant($constantName) : null;
    }
}
