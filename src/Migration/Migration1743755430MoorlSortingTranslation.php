<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

class Migration1743755430MoorlSortingTranslation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743755430;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE moorl_sorting_translation CHANGE label label VARCHAR(255) NOT NULL AFTER moorl_sorting_id;
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
        if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting_translation', 'label')) {
            if (EntityDefinitionQueryHelper::columnExists($connection, 'moorl_sorting_translation', 'moorl_sorting_id')) {
                $sql = "ALTER TABLE moorl_sorting_translation CHANGE label label VARCHAR(255) NOT NULL AFTER moorl_sorting_id;";
            } else {
                $sql = "ALTER TABLE moorl_sorting_translation CHANGE label label VARCHAR(255) NOT NULL;";
            }
            EntityDefinitionQueryHelper::tryExecuteStatement($connection, $sql, 'moorl_sorting_translation');
        }

    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
