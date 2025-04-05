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
use Shopware\Core\Framework\Migration\Exception\InvalidMigrationClassException;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Util\Hasher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Intl\Exception\ResourceBundleNotFoundException;
use Twig\Environment;

class MigrationService
{
    public const MODE_DRY = 'dry';
    public const MODE_LIVE = 'live';
    public const MODE_FILE = 'file';
    public const MODE_CLEANUP = 'cleanup'; // Remove not processed migration files

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

    public function createMigration(
        string $pluginName,
        string $mode = self::MODE_DRY,
        bool $drop = false,
        bool $sort = false
    ): bool
    {
        try {
            $this->bundle = $this->kernel->getBundle($pluginName);
        } catch (\Exception $exception) {
            $this->log("Caught Exception, quitting...", "critical", [
                "message" => $exception->getMessage(), "plugin" => $pluginName
            ]);
            return false;
        }

        if ($mode === self::MODE_CLEANUP) {
            return $this->cleanup();
        }

        $pluginTables = $this->getPluginConstant(get_class($this->bundle), 'PLUGIN_TABLES');
        if (!$pluginTables) {
            $this->log("No tables found", "info", ["plugin" => $pluginName]);
            return false;
        }

        $actionRequired = false;

        foreach ($pluginTables as $table) {
            $this->log('Processing entity: ' . $table);

            $tableExists = EntityDefinitionQueryHelper::tableExists($this->connection, $table);

            if ($this->definitionInstanceRegistry->has($table)) {
                if (!$tableExists) {
                    $this->log('Table for definition not found, creating new one');
                }

                $entityDefinition = $this->definitionInstanceRegistry->getByEntityName($table);

                if (!str_contains($entityDefinition::class, $this->bundle->getNamespace())) {
                    throw new ResourceBundleNotFoundException(
                        sprintf("This entity is not a part of the plugin %s", $pluginName)
                    );
                }

                $actionRequired = $this->handleQueries(
                    $this->generateQueries($entityDefinition),
                    $entityDefinition,
                    $mode,
                    $drop,
                    $sort
                ) || $actionRequired;
            } else {
                if ($tableExists) {
                    $this->log('Definition not found, but table found. Maybe the table can be deleted');
                } else {
                    $this->log('Definition not found');
                }
            }
        }

        return $actionRequired;
    }

    private function handleQueries(
        array|string $queries,
        EntityDefinition $entityDefinition,
        string $mode,
        bool $drop = false,
        bool $sort = false
    ): bool
    {
        if (empty($queries)) {
            return false;
        }

        $queries = is_array($queries) ? $queries : [$queries];

        $queries = array_map(function ($query) {
            return $this->formatQuery($query);
        }, $queries);

        $queries = array_filter($queries, function ($query) {
            return !empty($query);
        });

        if (empty($queries)) {
            return false;
        }

        $operations = [];
        foreach ($queries as $query) {
            $operations = array_merge($operations, $this->parseQuery($query, $drop));
        }
        if (empty($operations)) {
            return false;
        }

        if ($sort) {
            $this->sortOperations($entityDefinition, $operations);
        }

        if ($mode === self::MODE_DRY) {
            $this->dryRun($operations);
            return false;
        } else if ($mode === self::MODE_LIVE) {
            return $this->execute($operations, $entityDefinition->getEntityName());
        } else {
            return $this->makeFile($operations, $entityDefinition->getEntityName());
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
                $matches[2],
                OperationStruct::TABLE,
                strtoupper($matches[1])
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

            $column = null;
            $sourceColumn = null;
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
                        $sourceColumn = $tokens[1];
                        $column = $tokens[2];
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
                $column,
                $sourceColumn
            );
        }

        return $result;
    }

    private function processMigrationDirectory(string $directory, callable $callback): bool
    {
        $classFiles = scandir($directory, \SCANDIR_SORT_ASCENDING);

        if ($classFiles) {
            $namespace = $this->bundle->getMigrationNamespace();

            foreach ($classFiles as $classFileName) {
                $path = sprintf("%s/%s", $directory, $classFileName);
                if (pathinfo($path, \PATHINFO_EXTENSION) !== 'php') {
                    continue;
                }

                $className = $namespace . '\\' . pathinfo($classFileName, \PATHINFO_FILENAME);

                if (!class_exists($className) && !trait_exists($className) && !interface_exists($className)) {
                    throw new InvalidMigrationClassException($className, $path);
                }

                if (!is_subclass_of($className, MigrationStep::class, true)) {
                    continue;
                }

                preg_match('/(\d{10,})/', $classFileName, $matches);
                $classTimestamp = (int) ($matches[1] ?? 0);
                if ($classTimestamp === 0) {
                    $this->log("The class have no valid timestamp, skipping...", "error", [
                        "class" => $className,
                        'file' => $classFileName,
                        'matches' => json_encode($matches)
                    ]);
                    continue;
                }

                $this->log(sprintf("Found migration class %s with timestamp %d", $className, $classTimestamp));

                if ($callback($path, $className, $classTimestamp)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function cleanup(): bool
    {
        $directory = $this->bundle->getMigrationPath();
        if (!$this->filesystem->exists($directory)) {
            return false;
        }

        $time = $this->now->modify("-3 days");
        $timestamp = $time->getTimestamp();
        $actionRequired = false;

        $this->processMigrationDirectory(
            $directory,
            function ($path, $className, $classTimestamp) use ($timestamp, &$actionRequired): bool {
                if ($classTimestamp > $timestamp) {
                    if (EntityDefinitionQueryHelper::migrationExists($this->connection, $className)) {
                        $this->log("--- already has been migrated");
                        $actionRequired = true;
                    }

                    if (!$this->isValidMigrationFile($path)) {
                        $this->log("Migration file is not auto generated, skipping...", "error");
                        return false;
                    }

                    $this->filesystem->remove($path);
                    $this->log(sprintf("Removed %s", $path));
                }
                return false;
            }
        );

        if ($actionRequired) {
            $this->log("Plugin have to be refreshed", "warning");
        }

        return $actionRequired;
    }

    private function isValidMigrationFile(string $path): bool
    {
        $content = file_get_contents($path);
        return str_contains($content, "use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper");
    }

    private function makeFile(array $operations, string $entity): bool
    {
        $directory = $this->bundle->getMigrationPath();
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        $operationHash = $this->getOperationHash($operations);

        $hasDuplicate = $this->processMigrationDirectory($directory, function ($path, $className) use ($operationHash): bool {
            $classOperationHash = $this->getPluginConstant($className, 'OPERATION_HASH');
            if ($classOperationHash && $classOperationHash === $operationHash) {
                $this->log(
                    "An other class with the same operations already exists, skipping...",
                    "info",
                    ["constant" => sprintf("%s::OPERATION_HASH", $className), "value" => $operationHash]
                );
                return true;
            }
            return false;
        });
        if ($hasDuplicate) {
            return false;
        }

        $namespace = $this->bundle->getMigrationNamespace();
        $timestamp = (string) $this->now->getTimestamp();
        $className = 'Migration' . $timestamp . ucfirst(str_replace('_', '', ucwords($entity, '_')));
        $path = $directory . '/' . $className . '.php';

        $stubPath = __DIR__ . '/stubs/migration.stub.twig';
        $stub = file_get_contents($stubPath);
        if ($stub === false) {
            throw DataAbstractionLayerException::migrationStubNotFound($stubPath);
        }

        $template = $this->twig->createTemplate($stub);

        $data = [
            'operationHash' => $operationHash,
            'namespace' => $namespace,
            'className' => $className,
            'timestamp' => $timestamp,
            'operations' => $operations,
        ];

        $content = $this->twig->render($template, $data);

        $this->filesystem->dumpFile($path, $content);

        $this->log('Migration file created: ' . $path, 'success');

        return true;
    }

    private function getOperationHash(array $operations): string
    {
        $parts = array_map(function (OperationStruct $operation) {
            return $operation->getQuery();
        }, $operations);

        return Hasher::hash($parts);
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

    private function dryRun(array $operations): void
    {
        /** @var OperationStruct $operation */
        foreach ($operations as $operation) {
            $this->log($operation->getQueryWithSorting());
        }
    }

    private function execute(array $operations, string $table): bool
    {
        /** @var OperationStruct $operation */
        foreach ($operations as $operation) {
            try {
                $this->handleOperation($operation, $table);
            } catch (\Exception $exception) {
                $this->log("Caught Exception, quitting...", "critical", $operation->jsonSerialize());
                return false;
            }
        }

        return true;
    }

    private function sortOperations(EntityDefinition $entityDefinition, array $operations): array
    {
        $sortedElTypes = [OperationStruct::TABLE, OperationStruct::COLUMN, OperationStruct::CONSTRAINT];
        $sortedOpTypes = [OperationStruct::DROP, OperationStruct::ADD, OperationStruct::CREATE, OperationStruct::MODIFY, OperationStruct::CHANGE];
        $sortedColumns = $this->getSortedStorageFields($entityDefinition->getFields());

        usort($operations, function(OperationStruct $a, OperationStruct $b) use ($sortedElTypes, $sortedOpTypes, $sortedColumns) {
            $posA = array_search($a->getElType(), $sortedElTypes);
            $posB = array_search($b->getElType(), $sortedElTypes);
            if ($posA !== $posB) {
                return $posA - $posB;
            }

            $posA = array_search($a->getOpType(), $sortedOpTypes);
            $posB = array_search($b->getOpType(), $sortedOpTypes);
            if ($posA !== $posB) {
                return $posA - $posB;
            }

            $posA = array_search($a->getColumn(), $sortedColumns);
            $posB = array_search($b->getColumn(), $sortedColumns);

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
        $query = $this->replaceBinaryValue($query);

        return $this->removeBadQueries($query);
    }

    private function addFkBackticks(string $query): string
    {
        return preg_replace('/\b(fk\.[A-Za-z0-9_]+\.[A-Za-z0-9_]+)\b/', '`$1`', $query);
    }
    function replaceBinaryValue(string $query): string
    {
        $pattern = "/(DEFAULT\s+)'(0x[a-f0-9]+)'/i";
        return preg_replace($pattern, "$1$2", $query);
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
        $storageFields = $fields->filter(function (Field $field) {
            return $field instanceof StorageAware && !$field->is(Runtime::class);
        });

        $storageFields->sort(function (StorageAware $a, StorageAware $b) {
            $order = $this->getFieldSortOrder($a) <=> $this->getFieldSortOrder($b);
            return $order !== 0 ? $order : strnatcasecmp($a->getStorageName(), $b->getStorageName());
        });

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

    private function log(string|\Stringable $message, $level = 'text', array $context = []): void
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
