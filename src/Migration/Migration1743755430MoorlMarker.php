<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743755430MoorlMarker extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743755430;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_marker CHANGE name name VARCHAR(255) NOT NULL AFTER class_name;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_id` FOREIGN KEY (marker_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_shadow_id` FOREIGN KEY (marker_shadow_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE moorl_marker ADD CONSTRAINT `fk.moorl_marker.marker_retina_id` FOREIGN KEY (marker_retina_id) REFERENCES media (id) ON UPDATE CASCADE ON DELETE SET NULL;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_marker', 'name')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_marker', 'class_name')) {
                $sql = "ALTER TABLE moorl_marker CHANGE name name VARCHAR(255) NOT NULL AFTER class_name;";
            } else {
                $sql = "ALTER TABLE moorl_marker CHANGE name name VARCHAR(255) NOT NULL;";
            }
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

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
