<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743755430MoorlLocation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743755430;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_location CHANGE location_lat location_lat DOUBLE PRECISION DEFAULT '0' AFTER id;
ALTER TABLE moorl_location CHANGE location_lon location_lon DOUBLE PRECISION DEFAULT '0' AFTER location_lat;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'location_lat')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'id')) {
                $sql = "ALTER TABLE moorl_location CHANGE location_lat location_lat DOUBLE PRECISION DEFAULT '0' AFTER id;";
            } else {
                $sql = "ALTER TABLE moorl_location CHANGE location_lat location_lat DOUBLE PRECISION DEFAULT '0';";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_location');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'location_lon')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'location_lat')) {
                $sql = "ALTER TABLE moorl_location CHANGE location_lon location_lon DOUBLE PRECISION DEFAULT '0' AFTER location_lat;";
            } else {
                $sql = "ALTER TABLE moorl_location CHANGE location_lon location_lon DOUBLE PRECISION DEFAULT '0';";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_location');
        }

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
