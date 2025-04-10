<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744278005MoorlClient extends MigrationStep
{
    public const OPERATION_HASH = '99862fa085c02b3f775d06194fff3853';
    public const PLUGIN_VERSION = '1.6.50';

    public function getCreationTimestamp(): int
    {
        return 1744278005;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
UPDATE `moorl_client` SET `type` = 'ftp' WHERE `type` IS NULL;
ALTER TABLE moorl_client CHANGE type type VARCHAR(255) DEFAULT 'ftp' NOT NULL;
UPDATE `moorl_client` SET `name` = 'My Client' WHERE `name` IS NULL;
ALTER TABLE moorl_client CHANGE name name VARCHAR(255) DEFAULT 'My Client' NOT NULL;
UPDATE `moorl_client` SET `active` = 0 WHERE `active` IS NULL;
ALTER TABLE moorl_client CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;
SQL;

        // Try to execute all queries at once
        try {
            $connection->executeStatement($sql);
            $this->additionalCustomUpdate($connection);
            return;
        } catch (\Exception) {
            if (!class_exists(EntityDefinitionQueryHelper::class)) {
                throw new MissingRequirementException('moorl/foundation', '1.6.50');
            }
        }

        // Try to execute all queries step by step
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'type')) {
            $sql = "UPDATE `moorl_client` SET `type` = 'ftp' WHERE `type` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'type')) {
            $sql = "ALTER TABLE moorl_client CHANGE type type VARCHAR(255) DEFAULT 'ftp' NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'name')) {
            $sql = "UPDATE `moorl_client` SET `name` = 'My Client' WHERE `name` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'name')) {
            $sql = "ALTER TABLE moorl_client CHANGE name name VARCHAR(255) DEFAULT 'My Client' NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'active')) {
            $sql = "UPDATE `moorl_client` SET `active` = 0 WHERE `active` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'active')) {
            $sql = "ALTER TABLE moorl_client CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        $this->additionalCustomUpdate($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }

    private function additionalCustomUpdate(Connection $connection): void
    {
        // Add custom update if necessary
    }
}
