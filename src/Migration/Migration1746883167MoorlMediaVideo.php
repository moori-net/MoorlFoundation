<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746883167MoorlMediaVideo extends MigrationStep
{
    public const OPERATION_HASH = 'bd2f22c412230b959fcb01f0af4be36f';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746883167;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE moorl_media_video (id BINARY(16) NOT NULL, media_id BINARY(16) NOT NULL, moorl_media_id BINARY(16) NOT NULL, min_width VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;
ALTER TABLE moorl_media_video ADD CONSTRAINT `fk.moorl_media_video.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE moorl_media_video ADD CONSTRAINT `fk.moorl_media_video.moorl_media_id` FOREIGN KEY (moorl_media_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE CASCADE;
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
        if (!EntityDefinitionQueryHelper::tableExists($connection, 'moorl_media_video', '')) {
            $sql = "CREATE TABLE moorl_media_video (id BINARY(16) NOT NULL, media_id BINARY(16) NOT NULL, moorl_media_id BINARY(16) NOT NULL, min_width VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_video');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_video', 'fk.moorl_media_video.media_id')) {
            $sql = "ALTER TABLE moorl_media_video ADD CONSTRAINT `fk.moorl_media_video.media_id` FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_video');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media_video', 'fk.moorl_media_video.moorl_media_id')) {
            $sql = "ALTER TABLE moorl_media_video ADD CONSTRAINT `fk.moorl_media_video.moorl_media_id` FOREIGN KEY (moorl_media_id) REFERENCES moorl_media (id) ON UPDATE CASCADE ON DELETE CASCADE;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media_video');
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
