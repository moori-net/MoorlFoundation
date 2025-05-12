<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1747034800MoorlMedia extends MigrationStep
{
    public const OPERATION_HASH = '7548dde8fb869bd58f02f2303da56027';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1747034800;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_media ADD media_folder_id BINARY(16) DEFAULT NULL;
ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.media_folder_id` FOREIGN KEY (media_folder_id) REFERENCES media_folder (id) ON UPDATE CASCADE ON DELETE RESTRICT;
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
        if (!EntityDefinitionQueryHelper::columnExists($connection, 'moorl_media', 'media_folder_id')) {
            $sql = "ALTER TABLE moorl_media ADD media_folder_id BINARY(16) DEFAULT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_media');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_media', 'fk.moorl_media.media_folder_id')) {
            $sql = "ALTER TABLE moorl_media ADD CONSTRAINT `fk.moorl_media.media_folder_id` FOREIGN KEY (media_folder_id) REFERENCES media_folder (id) ON UPDATE CASCADE ON DELETE RESTRICT;";
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
