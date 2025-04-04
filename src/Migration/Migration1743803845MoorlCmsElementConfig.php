<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743803845MoorlCmsElementConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743803845;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_cms_element_config CHANGE name name VARCHAR(255) NOT NULL;
ALTER TABLE moorl_cms_element_config CHANGE type type VARCHAR(255) NOT NULL;
ALTER TABLE moorl_cms_element_config CHANGE created_at created_at DATETIME NOT NULL;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'name')) {
            $sql = "ALTER TABLE moorl_cms_element_config CHANGE name name VARCHAR(255) NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'type')) {
            $sql = "ALTER TABLE moorl_cms_element_config CHANGE type type VARCHAR(255) NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'created_at')) {
            $sql = "ALTER TABLE moorl_cms_element_config CHANGE created_at created_at DATETIME NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
