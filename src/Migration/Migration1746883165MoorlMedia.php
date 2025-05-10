<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746883165MoorlMedia extends MigrationStep
{
    public const OPERATION_HASH = '87f2da443817ac6c22456cd005e34c24';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746883165;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_media (id BINARY(16) NOT NULL, cover_id BINARY(16) DEFAULT NULL, media_id BINARY(16) DEFAULT NULL, active TINYINT(1) DEFAULT 0, duration INT DEFAULT 0, config JSON DEFAULT NULL, custom_fields JSON DEFAULT NULL, background_color VARCHAR(255) DEFAULT NULL, embedded_id VARCHAR(255) DEFAULT NULL, embedded_url VARCHAR(255) DEFAULT NULL, technical_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT 'auto' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.cover_id` FOREIGN KEY (cover_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
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
            $sql = "CREATE TABLE moorl_media (id BINARY(16) NOT NULL, cover_id BINARY(16) DEFAULT NULL, media_id BINARY(16) DEFAULT NULL, active TINYINT(1) DEFAULT 0, duration INT DEFAULT 0, config JSON DEFAULT NULL, custom_fields JSON DEFAULT NULL, background_color VARCHAR(255) DEFAULT NULL, embedded_id VARCHAR(255) DEFAULT NULL, embedded_url VARCHAR(255) DEFAULT NULL, technical_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT 'auto' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media', 'fk.moorl_media.cover_id')) {
            $sql = "ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.cover_id` FOREIGN KEY (cover_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media', 'fk.moorl_media.media_id')) {
            $sql = "ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
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
