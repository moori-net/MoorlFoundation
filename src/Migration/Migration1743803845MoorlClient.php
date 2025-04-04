<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743803845MoorlClient extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743803845;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_client CHANGE type type VARCHAR(255) DEFAULT 'ftp' NOT NULL;
ALTER TABLE moorl_client CHANGE name name VARCHAR(255) DEFAULT 'My Client' NOT NULL;
ALTER TABLE moorl_client CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;
SQL;

        // Try to execute all queries at once
        try {
            $connection->executeStatement($sql);
            return;
        } catch (\Exception) {
            if (!class_exists(EntityDefinitionQueryHelper::class)) {
                throw new MissingRequirementException('moorl/foundation', '1.6.50');
            }
        }

        // Try to execute all queries step by step
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'type')) {
            $sql = "ALTER TABLE moorl_client CHANGE type type VARCHAR(255) DEFAULT 'ftp' NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'name')) {
            $sql = "ALTER TABLE moorl_client CHANGE name name VARCHAR(255) DEFAULT 'My Client' NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_client', 'active')) {
            $sql = "ALTER TABLE moorl_client CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_client');
        }

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
