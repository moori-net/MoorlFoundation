<?php declare(strict_types=1);

namespace MoorlFoundation;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class MoorlFoundation extends Plugin
{
    final public const NAME = 'MoorlFoundation';
    final public const DATA_CREATED_AT = '2001-11-11 11:11:11.111';
    final public const SHOPWARE_TABLES = [];
    final public const PLUGIN_TABLES = [
        'moorl_cms_element_config',
        'moorl_location',
        'moorl_location_cache',
        'moorl_sorting',
        'moorl_sorting_translation',
        'moorl_marker',
        'moorl_client',
    ];

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        /* @var $dataService DataService */
        $dataService = $this->container->get(DataService::class);
        $dataService->install(self::NAME);
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $this->dropTables();
    }

    private function dropTables(): void
    {
        $connection = $this->container->get(Connection::class);

        foreach (self::PLUGIN_TABLES as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeStatement($sql);
        }
    }
}
