<?php declare(strict_types=1);

namespace MoorlFoundation;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MoorlFoundation extends Plugin
{
    final public const NAME = 'MoorlFoundation';
    final public const DATA_CREATED_AT = '2001-11-11 11:11:11.111';
    final public const SHOPWARE_TABLES = [];
    final public const INHERITANCES = [];
    final public const PLUGIN_TABLES = [
        'moorl_cms_element_config',
        'moorl_location',
        'moorl_location_cache',
        'moorl_sorting',
        'moorl_sorting_translation',
        'moorl_marker',
        'moorl_client',
        'moorl_image_map',
        'moorl_image_map_translation',
        'moorl_image_map_item',
        'moorl_image_map_item_translation',
    ];

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        /* @var $dataService DataService */
        $dataService = $this->container->get(DataService::class);
        $dataService->install(self::NAME);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->uninstallTrait();
    }

    private function uninstallTrait(): void
    {
        $connection = $this->container->get(Connection::class);

        foreach (array_reverse(self::PLUGIN_TABLES) as $table) {
            $sql = sprintf('DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }

        foreach (array_reverse(self::SHOPWARE_TABLES) as $table) {
            $sql = sprintf("DELETE FROM `%s` WHERE `created_at` = '%s';", $table, self::DATA_CREATED_AT);

            try {
                $connection->executeStatement($sql);
            } catch (\Exception) {
                continue;
            }
        }

        foreach (self::INHERITANCES as $table => $propertyNames) {
            foreach ($propertyNames as $propertyName) {
                $sql = sprintf("ALTER TABLE `%s` DROP `%s`;", $table, $propertyName);

                try {
                    $connection->executeStatement($sql);
                } catch (\Exception) {
                    continue;
                }
            }
        }
    }
}
