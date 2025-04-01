<?php declare(strict_types=1);

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\CompiledFieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Util\Hasher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginMigrationHelper
 *
 * Unterstützt bei der Aktualisierung und Migration von Plugin-Tabellen.
 */
class PluginMigrationHelper
{
    public const ENGINE = 'InnoDB';
    public const CHARSET = 'utf8mb4';
    public const COLLATE = 'utf8mb4_unicode_ci';
    public const UNUSED = 'unused column';

    public static ?string $after = null;
    public static array $queryLog = [];
    public static array $hashes = [];

    /**
     * Aktualisiert alle Tabellen, die im Plugin definiert sind.
     *
     * @param string $plugin
     * @param ContainerInterface $container
     */
    public static function update(string $plugin, ContainerInterface $container): void
    {
        $pluginTables = self::getPluginConstant($plugin, 'PLUGIN_TABLES');
        if (!$pluginTables) {
            return;
        }

        $connection = $container->get(Connection::class);
        $definitionRegistry = $container->get(DefinitionInstanceRegistry::class);

        $entityDefinitions = [];
        foreach ($pluginTables as $table) {
            if ($definitionRegistry->has($table)) {
                $entityDefinition = $definitionRegistry->getByEntityName($table);

                self::sortByInstance($entityDefinition->getFields());
                self::commentUnusedTableColumns($connection, $entityDefinition);

                if (self::isMigrationNecessary($connection, $entityDefinition)) {
                    $entityDefinitions[] = $entityDefinition;
                }
            }
        }

        self::updateDefinitions($connection, $entityDefinitions);

        foreach ($entityDefinitions as $entityDefinition) {
            self::addMigrationEntry($connection, $entityDefinition);
        }
    }

    /**
     * Entfernt die Migrationseinträge eines Plugins.
     *
     * @param string $plugin
     * @param ContainerInterface $container
     */
    public static function uninstall(string $plugin, ContainerInterface $container): void
    {
        $pluginTables = self::getPluginConstant($plugin, 'PLUGIN_TABLES');
        if (!$pluginTables) {
            return;
        }

        $connection = $container->get(Connection::class);
        self::removeMigrationEntry($connection, $plugin);
    }

    /**
     * Aktualisiert Felder, Primär- und Fremdschlüssel der angegebenen Entity-Definitionen.
     *
     * @param Connection $connection
     * @param EntityDefinition[] $entityDefinitions
     */
    public static function updateDefinitions(Connection $connection, array $entityDefinitions): void
    {
        // Update Felder und Primärschlüssel
        foreach ($entityDefinitions as $entityDefinition) {
            self::updateFields($connection, $entityDefinition);
            self::$after = null;
            self::updatePrimaryKeys($connection, $entityDefinition);
            self::$after = null;
        }

        // Update Fremdschlüssel
        foreach ($entityDefinitions as $entityDefinition) {
            self::updateForeignKeys($connection, $entityDefinition);
            self::$after = null;
        }
    }

    /**
     * Aktualisiert die Spalten der Tabelle anhand der Entity-Definition.
     *
     * @param Connection $connection
     * @param EntityDefinition $entityDefinition
     */
    public static function updateFields(Connection $connection, EntityDefinition $entityDefinition): void
    {
        $table = $entityDefinition->getEntityName();
        foreach ($entityDefinition->getFields() as $field) {
            if (!self::$after) {
                self::createTableIfNotExists($connection, $table, $field);
            }
            self::createColumnIfNotExists($connection, $table, $field);
        }
    }

    /**
     * Fügt Primärschlüssel hinzu.
     *
     * @param Connection $connection
     * @param EntityDefinition $entityDefinition
     */
    public static function updatePrimaryKeys(Connection $connection, EntityDefinition $entityDefinition): void
    {
        $table = $entityDefinition->getEntityName();
        $primaryKeys = [];
        $currentPrimaryKeys = self::getIndexColumns($connection, $table);

        foreach ($entityDefinition->getFields() as $field) {
            if (!self::isMigratableField($field)) {
                continue;
            }

            if ($field->is(PrimaryKey::class)) {
                if (in_array($field->getStorageName(), $currentPrimaryKeys, true)) {
                    continue;
                }
                $primaryKeys[] = sprintf("`%s`", $field->getStorageName());
            }
        }

        if (empty($primaryKeys)) {
            return;
        }

        $sql = "ALTER TABLE `:table` ADD PRIMARY KEY (" . implode(", ", $primaryKeys) . ");";
        self::executeStatement($connection, $sql, ['table' => $table]);
    }

    /**
     * Fügt Fremdschlüssel anhand der Entity-Definition hinzu.
     *
     * @param Connection $connection
     * @param EntityDefinition $entityDefinition
     */
    public static function updateForeignKeys(Connection $connection, EntityDefinition $entityDefinition): void
    {
        $isMappingDefinition = $entityDefinition instanceof MappingEntityDefinition;
        $table = $entityDefinition->getEntityName();

        foreach ($entityDefinition->getFields() as $field) {
            self::createForeignKey($connection, $table, $field, $isMappingDefinition);
        }
    }

    /**
     * Erstellt eine Tabelle, sofern diese noch nicht existiert.
     *
     * @param Connection $connection
     * @param string $table
     * @param Field $field
     */
    public static function createTableIfNotExists(Connection $connection, string $table, Field $field): void
    {
        if (!self::isMigratableField($field)) {
            return;
        }

        if (EntityDefinitionQueryHelper::tableExists($connection, $table)) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `:table` (" . self::getFieldSpecs($field) .
            ") ENGINE = :db_engine DEFAULT CHARSET = :db_charset COLLATE = :db_collate;";
        self::executeStatement($connection, $sql, ['table' => $table]);
    }

    /**
     * Fügt eine Spalte zur Tabelle hinzu, sofern diese noch nicht existiert.
     *
     * @param Connection $connection
     * @param string $table
     * @param Field $field
     */
    public static function createColumnIfNotExists(Connection $connection, string $table, Field $field): void
    {
        if (!self::isMigratableField($field)) {
            return;
        }

        $column = $field->getStorageName();
        if (EntityDefinitionQueryHelper::columnExists($connection, $table, $column)) {
            $sql = "ALTER TABLE `:table` CHANGE `:column` " . self::getFieldSpecs($field) . ";";
        } else {
            $sql = "ALTER TABLE `:table` ADD " . self::getFieldSpecs($field) . ";";
        }

        self::executeStatement($connection, $sql, [
            'table'  => $table,
            'column' => $column,
        ]);

        self::$after = $column;
    }

    /**
     * Erstellt einen Fremdschlüssel für das gegebene Feld.
     *
     * @param Connection $connection
     * @param string $table
     * @param Field $field
     * @param bool $isMappingDefinition
     */
    public static function createForeignKey(
        Connection $connection,
        string $table,
        Field $field,
        bool $isMappingDefinition
    ): void {
        if (
            !($field instanceof FkField || $field instanceof ManyToOneAssociationField)
            || $field->is(NoConstraint::class)
        ) {
            return;
        }

        $column = $field->getStorageName();
        $onDelete = null;

        if ($field instanceof FkField) {
            $onDelete = $isMappingDefinition ? 'CASCADE' : ($field->is(Required::class) ? 'CASCADE' : 'SET NULL');
        } elseif ($field instanceof ManyToOneAssociationField) {
            if ($field->is(CascadeDelete::class)) {
                $onDelete = 'CASCADE';
            } elseif ($field->is(RestrictDelete::class)) {
                $onDelete = 'RESTRICT';
            } elseif ($field->is(SetNullOnDelete::class)) {
                $onDelete = 'SET NULL';
            }
        }

        if (!$onDelete || self::constraintExists($connection, $table, $column)) {
            return;
        }

        $sql = <<<SQL
ALTER TABLE `:table` ADD CONSTRAINT `fk.:table.:column`
    FOREIGN KEY (`:column`)
    REFERENCES `:reference_entity` (`:reference_field`)
    ON DELETE :on_delete ON UPDATE CASCADE;
SQL;
        self::executeStatement($connection, $sql, [
            'table'            => $table,
            'column'           => $column,
            'reference_entity' => $field->getReferenceEntity(),
            'reference_field'  => $field->getReferenceField(),
            'on_delete'        => $onDelete,
        ]);
    }

    /**
     * Erzeugt die SQL-Spezifikation für ein Feld.
     *
     * @param StorageAware $field
     * @return string|null
     */
    public static function getFieldSpecs(StorageAware $field): ?string
    {
        $spec = "`" . $field->getStorageName() . "` ";

        if ($field instanceof IdField) {
            $spec .= "BINARY(16)";
        } elseif ($field instanceof FkField) {
            $spec .= "BINARY(16)";
        } elseif ($field instanceof BoolField) {
            $spec .= "TINYINT(1)";
        } elseif ($field instanceof DateField || $field instanceof DateTimeField) {
            $spec .= "DATETIME(3)";
        } elseif ($field instanceof IntField) {
            $spec .= "INT(11)";
        } elseif ($field instanceof StringField) {
            $spec .= sprintf("VARCHAR(%d)", $field->getMaxLength());
        } elseif ($field instanceof LongTextField) {
            $spec .= "LONGTEXT";
        } elseif ($field instanceof JsonField) {
            $spec .= "JSON";
        } elseif ($field instanceof FloatField) {
            $spec .= "DOUBLE";
        }

        if (
            $field->is(Required::class)
            || $field instanceof BoolField
            || $field instanceof IntField
            || $field instanceof FloatField
        ) {
            $spec .= " NOT NULL";
        }

        if (self::$after) {
            $spec .= " AFTER `:after`";
        } else {
            //$spec .= " FIRST";
        }

        return $spec;
    }

    /**
     * Führt ein SQL-Statement aus und ersetzt Platzhalter.
     *
     * @param Connection $connection
     * @param string $sql
     * @param array $params
     * @param array $types
     * @return int|string
     *
     * @throws \RuntimeException Bei einem Fehler während der Ausführung.
     */
    private static function executeStatement(
        Connection $connection,
        string $sql,
        array $params = [],
        array $types = []
    ): int|string {
        $params = array_merge($params, [
            'db_engine'  => self::ENGINE,
            'db_charset' => self::CHARSET,
            'db_collate' => self::COLLATE,
            'after'      => self::$after ?: "",
        ]);

        foreach ($params as $key => $value) {
            $sql = str_replace(":" . $key, $value, $sql);
        }

        $types = array_merge($types, [
            'id'               => ParameterType::BINARY,
            'db_engine'        => ParameterType::STRING,
            'db_charset'       => ParameterType::STRING,
            'db_collate'       => ParameterType::STRING,
            'table'            => ParameterType::STRING,
            'column'           => ParameterType::STRING,
            'reference_entity' => ParameterType::STRING,
            'reference_field'  => ParameterType::STRING,
            'after'            => self::$after ? ParameterType::STRING : ParameterType::NULL,
        ]);

        self::$queryLog[] = $sql;

        try {
            return $connection->executeStatement($sql, $params, $types);
        } catch (\Exception $exception) {
            if ($exception->getCode() === 1138) {
                $sql = "UPDATE `:table` SET `:column` = '0' WHERE `:column` IS NULL;" . $sql;

                return self::executeStatement($connection, $sql, $params);
            } else {
                dd(end(self::$queryLog));
            }
        }
    }

    /**
     * Überprüft, ob ein Fremdschlüssel bereits existiert.
     *
     * @param Connection $connection
     * @param string $table
     * @param string $storageName
     * @return bool
     */
    public static function constraintExists(Connection $connection, string $table, string $storageName): bool
    {
        $constraint = sprintf("fk.%s.%s", $table, $storageName);
        $exists = $connection->fetchAssociative(
            "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = :constraint AND TABLE_NAME = :table;",
            [
                'constraint' => $constraint,
                'table'      => $table,
            ]
        );
        return !empty($exists);
    }

    /**
     * Ermittelt die Spalten eines Index.
     *
     * @param Connection $connection
     * @param string $table
     * @param string $keyName
     * @return array
     */
    public static function getIndexColumns(Connection $connection, string $table, string $keyName = "PRIMARY"): array
    {
        $data = $connection->fetchAllAssociative(
            "SHOW INDEXES FROM `" . $table . "` WHERE Key_name = '" . $keyName . "';"
        );
        return array_values(array_map(static fn(array $row) => $row['Column_name'], $data));
    }

    public static function getTableColumns(Connection $connection, string $table): array
    {
        $data = $connection->fetchAllAssociative(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table;",
            ['table' => $table]
        );
        return $data;
    }

    /**
     * Holt den Wert einer Plugin-Konstanten.
     *
     * @param string $plugin
     * @param string $constant
     * @return mixed
     */
    private static function getPluginConstant(string $plugin, string $constant): mixed
    {
        $constantName = $plugin . "::" . $constant;
        return defined($constantName) ? constant($constantName) : null;
    }

    /**
     * Erzeugt einen Hash basierend auf der Entity-Definition.
     *
     * @param EntityDefinition $entityDefinition
     * @return string
     */
    public static function hashEntityDefinition(EntityDefinition $entityDefinition): string
    {
        $entityName = $entityDefinition->getEntityName();
        if (isset(self::$hashes[$entityName])) {
            return self::$hashes[$entityName];
        }

        $parts = [$entityName];
        foreach ($entityDefinition->getFields() as $field) {
            if (!self::isMigratableField($field)) {
                continue;
            }
            $parts[] = [
                $field->getStorageName(),
                $field->getFlags(),
            ];
        }
        return self::$hashes[$entityName] = Hasher::hash($parts);
    }

    /**
     * Fügt oder aktualisiert den Eintrag in der Migrations-Tabelle.
     *
     * @param Connection $connection
     * @param EntityDefinition $entityDefinition
     */
    public static function addMigrationEntry(Connection $connection, EntityDefinition $entityDefinition): void
    {
        $hash = self::hashEntityDefinition($entityDefinition);
        $sql = <<<SQL
INSERT INTO `migration` (`class`, `creation_timestamp`, `update`, `message`)
VALUES (:class, :creation_timestamp, NOW(), :message)
ON DUPLICATE KEY UPDATE `update` = NOW(), `message` = :message;
SQL;
        $connection->executeStatement($sql, [
            'class'             => $entityDefinition::class,
            'creation_timestamp'=> time(),
            'message'           => $hash,
        ]);
    }

    /**
     * Prüft, ob für die Entity-Definition eine Migration notwendig ist.
     *
     * @param Connection $connection
     * @param EntityDefinition $entityDefinition
     * @return bool
     */
    public static function isMigrationNecessary(Connection $connection, EntityDefinition $entityDefinition): bool
    {
        $hash = self::hashEntityDefinition($entityDefinition);
        $sql = "SELECT * FROM `migration` WHERE `class` = :class AND `message` = :message";
        $stmt = $connection->executeQuery($sql, [
            'class'   => $entityDefinition::class,
            'message' => $hash,
        ]);

        return ($stmt->rowCount() === 0);
    }

    /**
     * Entfernt Migrations-Einträge für das angegebene Plugin.
     *
     * @param Connection $connection
     * @param string $plugin
     */
    public static function removeMigrationEntry(Connection $connection, string $plugin): void
    {
        $reflection = new \ReflectionClass($plugin);
        $namespaceName = str_ireplace('\\', '\\\\', $reflection->getNamespaceName()) . '%';
        $sql = "DELETE FROM `migration` WHERE `class` LIKE :namespace";
        $connection->executeQuery($sql, [
            'namespace' => $namespaceName,
        ]);
    }

    /**
     * Prüft, ob ein Feld migriert werden soll.
     *
     * @param Field $field
     * @return bool
     */
    private static function isMigratableField(Field $field): bool
    {
        return $field instanceof StorageAware && !$field->is(Runtime::class);
    }

    public static function sortByInstance(CompiledFieldCollection $fields): void
    {
        $fields->sort(function (Field $a, Field $b) {
            return self::getFieldSortOrder($a) <=> self::getFieldSortOrder($b);
        });
    }

    public static function filterByMigratable(CompiledFieldCollection $fields): CompiledFieldCollection
    {
        return $fields->filter(function (Field $field) {
            return self::isMigratableField($field);
        });
    }

    private static function getFieldSortOrder(Field $field): int
    {
        if ($field instanceof IdField) {
            return 1;
        } elseif ($field instanceof FkField) {
            return 2;
        } elseif ($field instanceof BoolField) {
            return 3;
        } elseif ($field instanceof IntField) {
            return 4;
        } elseif ($field instanceof FloatField) {
            return 5;
        } elseif ($field instanceof StringField) {
            return 6;
        } elseif ($field instanceof LongTextField) {
            return 7;
        } elseif ($field instanceof JsonField) {
            return 8;
        } elseif ($field instanceof DateField || $field instanceof DateTimeField) {
            if ($field->getStorageName() === 'created_at') {
                return 10;
            } elseif ($field->getStorageName() === 'updated_at') {
                return 11;
            }
            return 9;
        }
        return 12;
    }

    private static function commentUnusedTableColumns(Connection $connection, EntityDefinition $entityDefinition): void
    {
        $table = $entityDefinition->getEntityName();

        $allColumns = self::getTableColumns($connection, $table);

        $availableColumns = [];
        /** @var StorageAware $field */
        foreach (self::filterByMigratable($entityDefinition->getFields()) as $field) {
            $availableColumns[] = $field->getStorageName();
        }

        foreach ($allColumns as $column) {
            if (in_array($column['COLUMN_NAME'], $availableColumns)) {
                continue;
            }

            $sql = "ALTER TABLE `:table` CHANGE `:column` `:column` " . $column['COLUMN_TYPE'] . " COMMENT ':comment';";


            self::executeStatement($connection, $sql, [
                'table'  => $table,
                'column' => $column['COLUMN_NAME'],
                'comment' => self::UNUSED,
            ]);
        }
    }
}
