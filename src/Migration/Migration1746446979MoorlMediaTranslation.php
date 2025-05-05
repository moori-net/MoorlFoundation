<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746446979MoorlMediaTranslation extends MigrationStep
{
    public const OPERATION_HASH = 'dddbd89a15750c12641425eaba0ca15d';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746446979;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_media_translation (cover_id BINARY(16) DEFAULT NULL, media_id BINARY(16) DEFAULT NULL, name VARCHAR(255) NOT NULL, embedded_id VARCHAR(255) DEFAULT NULL, embedded_url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, moorl_media_id BINARY(16) NOT NULL, language_id BINARY(16) NOT NULL, PRIMARY KEY(moorl_media_id, language_id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.cover_id` FOREIGN KEY (cover_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.moorl_media_id` FOREIGN KEY (moorl_media_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.language_id` FOREIGN KEY (language_id) REFERENCES language (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_media_translation', '')) {
            $sql = "CREATE TABLE moorl_media_translation (cover_id BINARY(16) DEFAULT NULL, media_id BINARY(16) DEFAULT NULL, name VARCHAR(255) NOT NULL, embedded_id VARCHAR(255) DEFAULT NULL, embedded_url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, moorl_media_id BINARY(16) NOT NULL, language_id BINARY(16) NOT NULL, PRIMARY KEY(moorl_media_id, language_id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_translation', 'fk.moorl_media_translation.cover_id')) {
            $sql = "ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.cover_id` FOREIGN KEY (cover_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_translation', 'fk.moorl_media_translation.media_id')) {
            $sql = "ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_translation', 'fk.moorl_media_translation.moorl_media_id')) {
            $sql = "ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.moorl_media_id` FOREIGN KEY (moorl_media_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_translation');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_translation', 'fk.moorl_media_translation.language_id')) {
            $sql = "ALTER TABLE moorl_media_translation ADD CONSTRAINT `fk.moorl_media_translation.language_id` FOREIGN KEY (language_id) REFERENCES language (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_translation');
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
