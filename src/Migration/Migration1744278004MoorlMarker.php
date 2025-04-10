<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1744278004MoorlMarker extends MigrationStep
{
    public const OPERATION_HASH = '10e82a8dce09fb43563b2ce790986903';
    public const PLUGIN_VERSION = '1.6.50';

    public function getCreationTimestamp(): int
    {
        return 1744278004;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_marker CHANGE name name VARCHAR(255) NOT NULL;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_id` FOREIGN KEY (marker_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_shadow_id` FOREIGN KEY (marker_shadow_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_retina_id` FOREIGN KEY (marker_retina_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_marker', 'name')) {
            $sql = "ALTER TABLE moorl_marker CHANGE name name VARCHAR(255) NOT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_marker');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_marker', 'fk.moorl_marker.marker_id')) {
            $sql = "ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_id` FOREIGN KEY (marker_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_marker');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_marker', 'fk.moorl_marker.marker_shadow_id')) {
            $sql = "ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_shadow_id` FOREIGN KEY (marker_shadow_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_marker');
        }

        if (!EntityDefinitionQueryHelper::constraintExists($connection, 'moorl_marker', 'fk.moorl_marker.marker_retina_id')) {
            $sql = "ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_retina_id` FOREIGN KEY (marker_retina_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_marker');
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
