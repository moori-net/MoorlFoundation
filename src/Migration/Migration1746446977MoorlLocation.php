<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class Migration1746446977MoorlLocation extends MigrationStep
{
    public const OPERATION_HASH = 'd8c573ab25a1bdbb3a2cc32136be9c08';
    public const PLUGIN_VERSION = '1.7.0';

    public function getCreationTimestamp(): int
    {
        return 1746446977;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_location CHANGE location_lat location_lat DOUBLE PRECISION DEFAULT NULL;
ALTER TABLE moorl_location CHANGE location_lon location_lon DOUBLE PRECISION DEFAULT NULL;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'location_lat')) {
            $sql = "ALTER TABLE moorl_location CHANGE location_lat location_lat DOUBLE PRECISION DEFAULT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_location');
        }

        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_location', 'location_lon')) {
            $sql = "ALTER TABLE moorl_location CHANGE location_lon location_lon DOUBLE PRECISION DEFAULT NULL;";
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_location');
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
