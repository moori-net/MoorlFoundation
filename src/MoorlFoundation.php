<?php declare(strict_types=1);

namespace MoorlFoundation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class MoorlFoundation extends Plugin
{
    public const NAME = 'MoorlFoundation';

    public const DATA_CREATED_AT = '2001-11-11 11:11:11.111';

    public const PLUGIN_TABLES = [
        'moorl_cms_element_config',
        'moorl_location',
        'moorl_sorting',
        'moorl_sorting_translation',
    ];

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
            $connection->executeUpdate($sql);
        }
    }
}
