<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;

trait PluginTrait
{
    private function dropTables(?array $tables = null): void
    {
        if (!$tables) {
            $tables = self::PLUGIN_TABLES;
        }

        $connection = $this->container->get(Connection::class);

        foreach ($tables as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeUpdate($sql);
        }
    }
}
