<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\OperationStruct;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\SchemaBuilderExtension;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\DataAbstractionLayer\CompiledFieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class MigrationService
{
    private BundleInterface $bundle;
    private ?ShopwareStyle $io = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly KernelInterface $kernel,
        private readonly Filesystem $filesystem,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly SchemaBuilderExtension $schemaBuilder = new SchemaBuilderExtension(),
        private readonly \DateTimeImmutable $now = new \DateTimeImmutable(),
    )
    {
    }

    public function setIo(?ShopwareStyle $io): void
    {
        $this->io = $io;
    }

    public function createMigration(string $bundleName, bool $drop = false, bool $live = false, bool $sort = false): void
    {
        try {
            $this->bundle = $this->kernel->getBundle($bundleName);
        } catch (\Exception $exception) {
            $this->log("Caught Exception, quitting...", "critical", [
                "message" => $exception->getMessage(), "bundle" => $bundleName
            ]);
            throw $exception;
        }

        $pluginTables = $this->getPluginConstant(get_class($this->bundle), 'PLUGIN_TABLES');
        if (!$pluginTables) {
            $this->log("No tables found", "error", ["bundle" => $bundleName]);
            return;
        }

        foreach ($pluginTables as $table) {
            $this->log('Processing entity: ' . $table);

            if ($this->definitionInstanceRegistry->has($table)) {
                $entityDefinition = $this->definitionInstanceRegistry->getByEntityName($table);

                $this->handleQueries(
                    $this->generateQueries($entityDefinition),
                    $entityDefinition,
                    $drop,
                    $live
                );
            } else {
                $this->log('Definition not found', 'error');
            }
        }
    }

    private function handleQueries(
        array|string $queries,
        EntityDefinition $entityDefinition,
        bool $drop = false,
        bool $live = false
    ): void
    {
        if (empty($queries)) {
            return;
        }

        $queries = is_array($queries) ? $queries : [$queries];

        $queries = array_map(function ($query) {
            return $this->formatQuery($query);
        }, $queries);

        $queries = array_filter($queries, function ($query) {
            return !empty($query);
        });

        if (empty($queries)) {
            return;
        }

        $operations = [];
        foreach ($queries as $query) {
            $operations = array_merge($operations, $this->parseQuery($query, $drop));
        }
        if (empty($operations)) {
            return;
        }

        $this->sortOperations($entityDefinition, $operations);

        if ($live) {
            $this->execute($operations, $entityDefinition->getEntityName());
        } else {
            $this->makeFile($operations, $entityDefinition->getEntityName());
        }
    }

    private function splitOperations(string $string): array
    {
        $parts = [];
        $bracketCount = 0;
        $current = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $char = $string[$i];
            if ($char === '(') {
                $bracketCount++;
            } elseif ($char === ')') {
                $bracketCount--;
            }

            if ($char === ',' && $bracketCount === 0) {
                $parts[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    private function parseQuery(string $query, bool $drop): array
    {
        $query = rtrim($query, ";");

        if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+(.*)/i', $query, $matches)) {
            $table = $matches[1];
            $operationsPart = $matches[2];
        } elseif (preg_match('/(CREATE|DROP)\s+TABLE\s+`?(\w+)`?/i', $query, $matches)) {
            return [new OperationStruct(
                $query,
                $matches[1],
                OperationStruct::TABLE,
                strtoupper($matches[2])
            )];
        } else {
            $this->log(sprintf("Error while parsing %s", $query), 'error');
            return [new OperationStruct($query)];
        }

        $operations = $this->splitOperations($operationsPart);

        $result = [];
        foreach ($operations as $op) {
            $tokens = preg_split('/\s+/', trim($op));
            if (count($tokens) < 2) {
                continue;
            }

            $opType = strtoupper($tokens[0]);

            $column = '';
            $elType = OperationStruct::COLUMN;

            switch ($opType) {
                case OperationStruct::ADD:
                case OperationStruct::DROP:
                    $opTypeSuffix = $tokens[1];
                    if (strtoupper($opTypeSuffix) === 'FOREIGN') {
                        $column = $tokens[3];
                        $elType = OperationStruct::CONSTRAINT;
                    } elseif (in_array(strtoupper($opTypeSuffix), ['INDEX', 'UNIQUE', 'CONSTRAINT'])) {
                        $column = $tokens[2];
                        $elType = strtolower($opTypeSuffix);
                    } else {
                        $column = $opTypeSuffix;
                    }
                    break;
                case OperationStruct::CHANGE:
                    if (count($tokens) >= 3) {
                        $column = $tokens[1];
                    }
                    break;
                case OperationStruct::MODIFY:
                    if (count($tokens) >= 2) {
                        $column = $tokens[1];
                    }
                    break;
                default:
                    continue 2;
            }

            if (!$drop && $opType === OperationStruct::DROP && $elType !== OperationStruct::CONSTRAINT) {
                continue;
            }

            $column = trim($column, '`');

            $result[] = new OperationStruct(
                sprintf("ALTER TABLE %s %s", $table, $op),
                $table,
                $elType,
                $opType,
                $column
            );
        }

        return $result;
    }

    private function makeFile(array $operations, string $entity): void
    {
        $directory = $this->bundle->getMigrationPath();
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        $timestamp = (string) $this->now->getTimestamp();
        $namespace = $this->bundle->getMigrationNamespace();
        $className = 'Migration' . $timestamp . ucfirst(str_replace('_', '', ucwords($entity, '_')));
        $path = $directory . '/' . $className . '.php';

        $stubPath = __DIR__ . '/stubs/migration.stub.twig';
        $stub = file_get_contents($stubPath);
        if ($stub === false) {
            throw DataAbstractionLayerException::migrationStubNotFound($stubPath);
        }

        $template = $this->twig->createTemplate($stub);

        $data = [
            'namespace' => $namespace,
            'className' => $className,
            'timestamp' => $timestamp,
            'operations' => $operations,
        ];

        $content = $this->twig->render($template, $data);

        $this->filesystem->dumpFile($path, $content);

        $this->log('Migration file created: ' . $path, 'success');
    }

    private function handleOperation(OperationStruct $operation, string $table): void
    {
        if (in_array($operation->getElType(), OperationStruct::EL_TYPES)) {
            $condition = false;

            if ($operation->isColumn()) {
                if ($operation->isAdd()) {
                    $condition = !EntityDefinitionQueryHelper::columnExists($this->connection, $operation->getTable(), $operation->getColumn());
                } else {
                    $condition = EntityDefinitionQueryHelper::columnExists($this->connection, $operation->getTable(), $operation->getColumn());
                }
            } else if ($operation->isTable()) {
                if ($operation->isAdd()) {
                    $condition = !EntityDefinitionQueryHelper::tableExists($this->connection, $operation->getTable());
                } else {
                    $condition = EntityDefinitionQueryHelper::tableExists($this->connection, $operation->getTable());
                }
            } else if ($operation->isConstraint()) {
                if ($operation->isAdd()) {
                    $condition = !EntityDefinitionQueryHelper::constraintExists($this->connection, $operation->getTable(), $operation->getColumn());
                } else {
                    $condition = EntityDefinitionQueryHelper::constraintExists($this->connection, $operation->getTable(), $operation->getColumn());
                }
            }

            if (!$condition) {
                return;
            }
        }

        if ($operation->isSort()) {
            $condition = true;

            if ($operation->getAfterColumn()) {
                $condition = EntityDefinitionQueryHelper::columnExists($this->connection, $operation->getTable(), $operation->getAfterColumn());
            }

            if ($condition) {
                EntityDefinitionQueryHelper::tryExecuteStatement($this->connection, $operation->getQueryWithSorting(), $operation->getTable() ?: $table);
                return;
            }
        }

        EntityDefinitionQueryHelper::tryExecuteStatement($this->connection, $operation->getQuery(), $operation->getTable() ?: $table);
    }

    private function execute(array $operations, string $table): void
    {
        /** @var OperationStruct $operation */
        foreach ($operations as $operation) {
            try {
                $this->handleOperation($operation, $table);
            } catch (\Exception $exception) {
                $this->log("Caught Exception, quitting...", "critical", $operation->jsonSerialize());
                exit();
            }
        }
    }

    private function sortOperations(EntityDefinition $entityDefinition, array $operations): array
    {
        // Höchste Priorität: Element-Typen
        $sortedElTypes = [OperationStruct::TABLE, OperationStruct::COLUMN, OperationStruct::CONSTRAINT];
        // Mittlere Priorität: Operation-Typen
        $sortedOpTypes = [OperationStruct::DROP, OperationStruct::ADD, OperationStruct::CREATE, OperationStruct::MODIFY, OperationStruct::CHANGE];
        // Niedrigste Priorität: Reihenfolge der Spalten (wie in $sortedColumns definiert)
        $sortedColumns = $this->getSortedStorageFields($entityDefinition->getFields());

        usort($operations, function(OperationStruct $a, OperationStruct $b) use ($sortedElTypes, $sortedOpTypes, $sortedColumns) {
            // 1. Vergleich: Element-Typen
            $posA = array_search($a->getElType(), $sortedElTypes);
            $posB = array_search($b->getElType(), $sortedElTypes);
            if ($posA !== $posB) {
                return $posA - $posB;
            }

            // 2. Vergleich: Operation-Typen
            $posA = array_search($a->getOpType(), $sortedOpTypes);
            $posB = array_search($b->getOpType(), $sortedOpTypes);
            if ($posA !== $posB) {
                return $posA - $posB;
            }

            // 3. Vergleich: Spalten-Reihenfolge
            $posA = array_search($a->getColumn(), $sortedColumns);
            $posB = array_search($b->getColumn(), $sortedColumns);
            // Falls die Spalte nicht gefunden wird, wird sie ans Ende sortiert
            $posA = $posA === false ? PHP_INT_MAX : $posA;
            $posB = $posB === false ? PHP_INT_MAX : $posB;

            return $posA - $posB;
        });

        /** @var OperationStruct $operation */
        foreach ($operations as $operation) {
            if ($operation->getElType() !== OperationStruct::COLUMN) {
                continue;
            }

            $operation->setSort(true);

            $index = array_search($operation->getColumn(), $sortedColumns);
            $previousColumn = ($index !== false && $index > 0) ? $sortedColumns[$index - 1] : null;

            $operation->setAfterColumn($previousColumn);
        }

        return $operations;
    }

    private function getPluginConstant(string $plugin, string $constant): mixed
    {
        $constantName = $plugin . "::" . $constant;
        return defined($constantName) ? constant($constantName) : null;
    }

    private function formatQuery(string $query): ?string
    {
        $query = $this->addFkBackticks($query);

        return $this->removeBadQueries($query);
    }

    private function addFkBackticks(string $query): string
    {
        return preg_replace('/\b(fk\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+)\b/', '`$1`', $query);
    }

    private function removeBadQueries(string $query): ?string
    {
        if (preg_match('/^DROP INDEX\s+`primary`/i', $query)) {
            return null;
        } elseif (preg_match('/^ALTER\s+TABLE\s+(.*)\s+ADD\s+PRIMARY\s+KEY/i', $query)) {
            return null;
        }

        return $query;
    }

    private function getSortedStorageFields(CompiledFieldCollection $fields): array
    {
        // Filter and clone storage fields
        $storageFields = $fields->filter(function (Field $field) {
            return $field instanceof StorageAware && !$field->is(Runtime::class);
        });

        $storageFields->sort(function (StorageAware $a, StorageAware $b) {
            $order = $this->getFieldSortOrder($a) <=> $this->getFieldSortOrder($b);
            return $order !== 0 ? $order : strnatcasecmp($a->getStorageName(), $b->getStorageName());
        });

        // Return storage names as array
        return array_values($storageFields->fmap(function (StorageAware $field) {
            return $field->getStorageName();
        }));
    }

    private function getFieldSortOrder(StorageAware $field): int
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

    public function generateQueries(EntityDefinition $entityDefinition): array
    {
        $tableExists = $this->connection->createSchemaManager()->tablesExist($entityDefinition->getEntityName());
        if ($tableExists) {
            return $this->getAlterTableQueries($entityDefinition);
        }
        return $this->getCreateTableQueries($entityDefinition);
    }

    private function getAlterTableQueries(EntityDefinition $definition): array
    {
        $originalTableSchema = $this->connection->createSchemaManager()->introspectTable($definition->getEntityName());
        $this->dropIndexes($originalTableSchema);
        $tableSchema = $this->schemaBuilder->buildSchemaOfDefinition($definition);
        $this->dropIndexes($tableSchema);
        return $this->getPlatform()->getAlterTableSQL((new Comparator())->compareTables($originalTableSchema, $tableSchema));
    }

    private function getCreateTableQueries(EntityDefinition $definition): array
    {
        $tableSchema = $this->schemaBuilder->buildSchemaOfDefinition($definition);
        $this->dropIndexes($tableSchema);
        return $this->getPlatform()->getCreateTableSQL($tableSchema, AbstractPlatform::CREATE_INDEXES | AbstractPlatform::CREATE_FOREIGNKEYS);
    }

    private function getPlatform(): AbstractPlatform
    {
        $platform = $this->connection->getDatabasePlatform();
        if (!$platform instanceof AbstractPlatform) {
            throw DataAbstractionLayerException::databasePlatformInvalid();
        }
        return $platform;
    }

    private function dropIndexes(Table $table): void
    {
        foreach ($table->getIndexes() as $index) {
            if ($index->isPrimary()) {
                continue;
            }
            $table->dropIndex($index->getName());
        }
    }

    public function log(string|\Stringable $message, $level = 'info', array $context = []): void
    {
        if (method_exists($this->logger, $level)) {
            $this->logger->{$level}($message, $context);
        } else {
            $this->logger->info($message, $context);
        }

        if (!$this->io) {
            return;
        }
        array_unshift($context, $message);

        if (method_exists($this->io, $level)) {
            $this->io->{$level}($context);
        } else {
            $this->io->info($context);
        }
    }
}
