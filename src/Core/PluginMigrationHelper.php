<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\SchemaBuilderExtension;
use Shopware\Core\Framework\DataAbstractionLayer\CompiledFieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\MigrationQueryGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginMigrationHelper
{
    public static function update(string $plugin, ContainerInterface $container): void
    {
        $pluginTables = self::getPluginConstant($plugin, 'PLUGIN_TABLES');
        if (!$pluginTables) {
            return;
        }

        $connection = $container->get(Connection::class);
        $definitionRegistry = $container->get(DefinitionInstanceRegistry::class);
        $schemaBuilder = new SchemaBuilderExtension();
        $migrationQueryGenerator = new MigrationQueryGenerator($connection, $schemaBuilder);

        foreach ($pluginTables as $table) {
            if ($definitionRegistry->has($table)) {
                $entityDefinition = $definitionRegistry->getByEntityName($table);

                $queries = $migrationQueryGenerator->generateQueries($entityDefinition);
                if (empty($queries)) {
                    continue;
                }

                foreach ($queries as $query) {
                    self::execute($connection, $query, $table);
                }

                $query = self::generateSortingQueries(
                    $connection,
                    $table,
                    self::getSortedStorageFields($entityDefinition->getFields())
                );

                self::execute(
                    $connection,
                    $query,
                    $table
                );
            }
        }
    }

    private static function execute(Connection $connection, string $query, string $table): void
    {
        $query = self::formatQuery($query);
        if (!$query) {
            return;
        }

        try {
            $connection->executeStatement($query);
        } catch (NotNullConstraintViolationException $exception) {
            self::updateNotNullTableData($connection, $query, $table);
            $connection->executeStatement($query);
        } catch (\Exception $exception) {
            dump($exception->getMessage());
            dd($query);
        }
    }

    public static function updateNotNullTableData(Connection $connection, string $query, string $table): void
    {
        preg_match_all(
            '/CHANGE\s+\S+\s+(\S+).*?DEFAULT\s+\'([^\']*)\'.*?NOT\s+NULL\b/i',
            $query,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $column = $match[1];
            $default = $match[2] ?? "0";
            $sql = "UPDATE `{$table}` SET `{$column}` = '{$default}' WHERE `{$column}` IS NULL;";

            self::execute($connection, $sql, $table);
        }
    }

    public static function uninstall(string $plugin, ContainerInterface $container): void
    {
        $pluginTables = self::getPluginConstant($plugin, 'PLUGIN_TABLES');
        if (!$pluginTables) {
            return;
        }

        $connection = $container->get(Connection::class);

        PluginLifecycleHelper::removePluginTables($connection, $pluginTables);
    }

    private static function generateSortingQueries(Connection $connection, string $table, array $sortedColumns): string
    {
        $tableExists = $connection->createSchemaManager()->tablesExist([$table]);
        if (!$tableExists) {
            return "";
        }

        $columnsData = $connection->fetchAllAssociative("SHOW COLUMNS FROM `{$table}`");
        $columns = [];
        foreach ($columnsData as $row) {
            $columns[$row['Field']] = $row;
        }

        $buildDefinition = function(array $col): string {
            $definition = $col['Type'];
            $definition .= ($col['Null'] === 'NO') ? ' NOT NULL' : ' NULL';
            if ($col['Default'] !== null) {
                $definition .= " DEFAULT '" . addslashes($col['Default']) . "'";
            }
            if (!empty($col['Extra'])) {
                $definition .= " " . strtoupper($col['Extra']);
            }
            return $definition;
        };

        $alterClauses = [];
        $previousColumn = null;

        foreach ($sortedColumns as $colName) {
            if (!isset($columns[$colName])) {
                continue;
            }

            $definition = $buildDefinition($columns[$colName]);

            if ($previousColumn === null) {
                $alterClauses[] = "MODIFY COLUMN `{$colName}` {$definition} FIRST";
            } else {
                $alterClauses[] = "MODIFY COLUMN `{$colName}` {$definition} AFTER `{$previousColumn}`";
            }

            $previousColumn = $colName;
        }

        $query = "ALTER TABLE `{$table}`" . implode(",", $alterClauses) . ";";

        return $query;
    }

    private static function getPluginConstant(string $plugin, string $constant): mixed
    {
        $constantName = $plugin . "::" . $constant;
        return defined($constantName) ? constant($constantName) : null;
    }

    private static function formatQuery(string $query): ?string
    {
        $query = self::addFkBackticks($query);

        return self::removeBadQueries($query);
    }

    private static function addFkBackticks(string $query): string
    {
        return preg_replace('/\b(fk\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+)\b/', '`$1`', $query);
    }

    private static function removeBadQueries(string $query): ?string
    {
        if (preg_match('/^DROP INDEX\s+`primary`/i', $query)) {
            return null;
        } elseif (preg_match('/^ALTER\s+TABLE\s+(.*)\s+ADD\s+PRIMARY\s+KEY/i', $query)) {
            return null;
        }

        return $query;
    }

    private static function getSortedStorageFields(CompiledFieldCollection $fields): array
    {
        // Filter and clone storage fields
        $storageFields = $fields->filter(function (Field $field) {
            return $field instanceof StorageAware && !$field->is(Runtime::class);
        });

        // Sort by name
        $storageFields->sort(function (StorageAware $a, StorageAware $b) {
            return strnatcasecmp($a->getStorageName(), $b->getStorageName());
        });

        // Sort by type
        $storageFields->sort(function (StorageAware $a, StorageAware $b) {
            return self::getFieldSortOrder($a) <=> self::getFieldSortOrder($b);
        });

        // Return storage names as array
        return array_values($storageFields->fmap(function (StorageAware $field) {
            return $field->getStorageName();
        }));
    }

    private static function getFieldSortOrder(StorageAware $field): int
    {
        $index = 1;
        foreach (SchemaBuilderExtension::$fieldMapping as $class => $type) {
            if (get_class($field) === $class) {
                return $index;
            }
            $index++;
        }

        foreach (SchemaBuilderExtension::$fieldMapping as $class => $type) {
            if ($field instanceof $class) {
                return $index;
            }
            $index++;
        }

        return $index;
    }
}
