<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746446978MoorlMedia extends MigrationStep
{
    public const OPERATION_HASH = 'e9c30a2643b1bef1c69ab2556425ec8a';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746446978;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_media (id BINARY(16) NOT NULL, product_stream_id BINARY(16) DEFAULT NULL, parent_id BINARY(16) DEFAULT NULL, active TINYINT(1) DEFAULT 0, technical_name VARCHAR(255) DEFAULT NULL, duration INT DEFAULT 0, embedded_type VARCHAR(255) DEFAULT 'auto' NOT NULL, config JSON DEFAULT NULL, custom_fields JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.parent_id` FOREIGN KEY (parent_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE RESTRICT;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_media', '')) {
            $sql = "CREATE TABLE moorl_media (id BINARY(16) NOT NULL, product_stream_id BINARY(16) DEFAULT NULL, parent_id BINARY(16) DEFAULT NULL, active TINYINT(1) DEFAULT 0, technical_name VARCHAR(255) DEFAULT NULL, duration INT DEFAULT 0, embedded_type VARCHAR(255) DEFAULT 'auto' NOT NULL, config JSON DEFAULT NULL, custom_fields JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media', 'fk.moorl_media.parent_id')) {
            $sql = "ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.parent_id` FOREIGN KEY (parent_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media', 'fk.moorl_media.product_stream_id')) {
            $sql = "ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.product_stream_id` FOREIGN KEY (product_stream_id) REFERENCES product_stream (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
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
