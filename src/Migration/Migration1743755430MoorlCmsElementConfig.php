<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743755430MoorlCmsElementConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743755430;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_cms_element_config CHANGE name name VARCHAR(255) NOT NULL AFTER custom_fields;
ALTER TABLE moorl_cms_element_config CHANGE type type VARCHAR(255) NOT NULL AFTER name;
ALTER TABLE moorl_cms_element_config CHANGE created_at created_at DATETIME NOT NULL AFTER type;
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
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'custom_fields')) {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE name name VARCHAR(255) NOT NULL AFTER custom_fields;";
            } else {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE name name VARCHAR(255) NOT NULL;";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'type')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'name')) {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE type type VARCHAR(255) NOT NULL AFTER name;";
            } else {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE type type VARCHAR(255) NOT NULL;";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'created_at')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_cms_element_config', 'type')) {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE created_at created_at DATETIME NOT NULL AFTER type;";
            } else {
                $sql = "ALTER TABLE moorl_cms_element_config CHANGE created_at created_at DATETIME NOT NULL;";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_cms_element_config');
        }

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
