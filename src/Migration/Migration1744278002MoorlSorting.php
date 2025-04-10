<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744278002MoorlSorting extends MigrationStep
{
    public const OPERATION_HASH = '9a81fa38fa18617bb5125b78e56f39dd';
    public const PLUGIN_VERSION = '1.6.50';

    public function getCreationTimestamp(): int
    {
        return 1744278002;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
UPDATE `moorl_sorting` SET `priority` = 0 WHERE `priority` IS NULL;
ALTER TABLE moorl_sorting CHANGE priority priority INT DEFAULT 0 NOT NULL;
UPDATE `moorl_sorting` SET `active` = 0 WHERE `active` IS NULL;
ALTER TABLE moorl_sorting CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;
UPDATE `moorl_sorting` SET `locked` = 0 WHERE `locked` IS NULL;
ALTER TABLE moorl_sorting CHANGE locked locked TINYINT(1) DEFAULT 0;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'priority')) {
            $sql = "UPDATE `moorl_sorting` SET `priority` = 0 WHERE `priority` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'priority')) {
            $sql = "ALTER TABLE moorl_sorting CHANGE priority priority INT DEFAULT 0 NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'active')) {
            $sql = "UPDATE `moorl_sorting` SET `active` = 0 WHERE `active` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'active')) {
            $sql = "ALTER TABLE moorl_sorting CHANGE active active TINYINT(1) DEFAULT 0 NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'locked')) {
            $sql = "UPDATE `moorl_sorting` SET `locked` = 0 WHERE `locked` IS NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting', 'locked')) {
            $sql = "ALTER TABLE moorl_sorting CHANGE locked locked TINYINT(1) DEFAULT 0;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting');
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
