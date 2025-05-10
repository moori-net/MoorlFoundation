<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746883166MoorlMediaTranslation extends MigrationStep
{
    public const OPERATION_HASH = '5500139b30aa455ea9280c40cd95527e';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746883166;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_media_translation (language_id BINARY(16) NOT NULL, moorl_media_id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(moorl_media_id, language_id)) DEFAULT CHARACTER SET utf8mb4;
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
            $sql = "CREATE TABLE moorl_media_translation (language_id BINARY(16) NOT NULL, moorl_media_id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(moorl_media_id, language_id)) DEFAULT CHARACTER SET utf8mb4;";
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
