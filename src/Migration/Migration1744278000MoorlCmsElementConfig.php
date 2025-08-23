<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744278000MoorlCmsElementConfig extends MigrationStep
{
    public const OPERATION_HASH = 'a5c916bbdc349d647ceaa1cdfd1b05f7';
    public const PLUGIN_VERSION = '1.6.50';

    public function getCreationTimestamp(): int
    {
        return 1744278000;
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
            $this->additionalCustomUpdate($connection);
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
