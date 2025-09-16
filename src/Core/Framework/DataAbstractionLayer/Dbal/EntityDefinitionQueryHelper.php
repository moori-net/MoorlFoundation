<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\Uuid\Uuid;

final class EntityDefinitionQueryHelper
{
    public const PRIVACY_PROTECTED_TABLES = [
        CustomerDefinition::ENTITY_NAME
    ];

    // =========================
    // === PUBLIC INTERFACE ===
    // =========================

    public static function tryExecuteStatement(
        Connection $connection,
        string $sql,
        ?string $table = null,
        ?string $column = null,
        array $codes = [],
        array $ids = [],
        int $maxAttempts = 5
    ): void {
        $attempt = 0;

        while ($attempt++ < $maxAttempts) {
            try {
                $connection->executeStatement($sql);
                return;
            } catch (Exception $exception) {
                if ($attempt === $maxAttempts) {
                    throw $exception;
                }

                usleep(100000);

                $handled = self::handleDbalException(
                    $exception,
                    $connection,
                    $sql,
                    $table,
                    $column,
                    $codes,
                    $ids
                );
                if ($handled === true) {
                    return;
                }

                usleep(100000);
            }
        }
    }

    // ==================================
    // === EXCEPTION HANDLING LOGIC ====
    // ==================================

    public static function handleDbalException(
        Exception $exception,
        Connection $connection,
        ?string $sql = null,
        ?string $table = null,
        ?string $column = null,
        array $codes = [],
        array $ids = []
    ): bool {
        if (!empty($codes) && !in_array($exception->getCode(), $codes, true)) {
            throw $exception;
        }

        if ($sql && !$table) {
            $pattern = '/\\b(?:ALTER\\s+TABLE|CREATE\\s+TABLE|INSERT\\s+INTO|UPDATE|DELETE\\s+FROM|DROP\\s+TABLE)\\s+`?(?<table>[a-zA-Z0-9_]+)`?/i';
            if (preg_match($pattern, $sql, $matches)) {
                $table = $matches['table'] ?? null;
            }
        }

        if (!$table) {
            throw $exception;
        }

        if ($exception instanceof NotNullConstraintViolationException) {
            self::updateNotNullTableData($connection, $sql, $table, $column);
            return false; // Source query not executed, try again
        }

        switch ($exception->getCode()) {
            case 1062:
                self::handleDuplicateEntryException($exception, $connection, $table, $ids);
                break;

            case 1170:
                self::handleBlobKeyLengthException($exception, $connection, $table);
                break;

            case 1061:
                self::handleIndexException($exception, $connection, $table);
                break;

            case 1822:
                self::handleMissingIndexOnFK($exception, $connection);
                break;

            case 1005:
                self::handleCreateTableFailure($sql, $connection);
                break;

            case 1217:
            case 1451:
                self::handleDeleteWithFKViolation($exception, $connection, $table, $ids);
                break;

            case 1452:
            case 1216:
                self::removeInvalidForeignKeys($connection, $sql, $table);
                break;

            case 1832:
                self::handleCannotChangeColumnUsedInFK($exception, $connection, $sql, $table);
                return true; // Source query executed
        }

        return false; // Source query not executed, try again
    }

    // ======================================
    // === HANDLER: SPECIFIC EXCEPTIONS ====
    // ======================================

    private static function handleDuplicateEntryException(Exception $exception, Connection $connection, string $table, array $ids): void {
        if (!preg_match("/for key '([^']+)\\.uniq\\.([^']+)'/", $exception->getMessage(), $matches)) {
            throw $exception;
        }

        $errorTable = $matches[1];
        $column = self::resolveForeignKey($connection, $errorTable, $table) ?? sprintf("%s_id", $table);

        if (!self::columnExists($connection, $errorTable, $column)) {
            throw $exception;
        }

        self::removeDuplicateEntries($connection, $errorTable, $column, $ids);
    }

    private static function handleBlobKeyLengthException(Exception $exception, Connection $connection, string $table): void
    {
        if (preg_match("/BLOB\\/TEXT column '([^']+)'/", $exception->getMessage(), $matches)) {
            $column = $matches[1];
            self::dropIndexIfExists($connection, $table, $column);
        }
    }

    private static function handleIndexException(Exception $exception, Connection $connection, string $table): void {
        if (preg_match("/Duplicate key name '([^']+)'|BLOB\\/TEXT column '([^']+)'/", $exception->getMessage(), $matches)) {
            $column = $matches[1] ?? $matches[2] ?? null;
            if ($column) {
                self::dropIndexIfExists($connection, $table, $column);
            }
        }
    }

    private static function handleMissingIndexOnFK(Exception $exception, Connection $connection): void {
        if (preg_match("/in the referenced table '([^']+)'/", $exception->getMessage(), $matches)) {
            self::addPrimaryKeys($connection, $matches[1]);
        }
    }

    private static function handleCreateTableFailure(?string $sql, Connection $connection): void {
        if (preg_match("/REFERENCES (\\S+)/", $sql, $matches)) {
            self::dropAndAddPrimaryKeys($connection, $matches[1]);
        }
    }

    private static function handleDeleteWithFKViolation(Exception $exception, Connection $connection, string $table, array $ids): void {
        if (preg_match(
            '/fails \(`[^`]+`\.`(?<table>[^`]+)`, CONSTRAINT `(?<constraint>[^`]+)` FOREIGN KEY \(`(?<column>[^`,]+)`/',
            $exception->getMessage(),
            $matches
        )) {
            $errorTable = $matches['table'];
            $column = str_replace('version_', '', $matches['column']);
        } else {
            throw $exception;
        }

        self::removeForeignKeyReferences($connection, $errorTable, $column, $ids, $table);
    }

    private static function handleCannotChangeColumnUsedInFK(Exception $exception, Connection $connection, string $sql, string $table): void {
        if (!preg_match(
            "/Cannot change column '[^']+': used in a foreign key constraint '([^']+)'/i",
            $exception->getMessage(),
            $matches
        )) {
            throw $exception;
        }

        $constraintName = $matches[1];

        $fkMeta = self::getForeignKeyMetaByName($connection, $table, $constraintName);
        if (!$fkMeta) {
            throw $exception;
        }

        self::dropForeignKey($connection, $table, $constraintName);

        $connection->executeStatement($sql);

        self::addForeignKeyFromMeta($connection, $table, $constraintName, $fkMeta);
    }

    // ============================
    // === FOREIGN KEY ACTIONS ===
    // ============================

    public static function removeDuplicateEntries(Connection $connection, string $table, string $column, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $sql = sprintf("DELETE FROM %s WHERE %s IN (:ids);", self::quote($table), self::quote($column));

        $connection->executeStatement(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );
    }

    public static function removeForeignKeyReferences(
        Connection $connection,
        string $table,
        string $column,
        array $ids,
        ?string $fallbackTable = null
    ): void {
        if (empty($ids)) {
            return;
        }

        // Die IDs sollten alle ein gültiges HEX Format haben
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new \RuntimeException("Error: Id format have to be a valid UUID");
            }
        }

        $bytesList = Uuid::fromHexToBytesList($ids);
        $isNullable = self::isColumnNullable($connection, $table, $column);

        if ($isNullable) {
            // Setze ungültige IDs auf NULL
            $sql = sprintf(
                "UPDATE %s SET %s = NULL WHERE %s IN (:ids);",
                self::quote($table),
                self::quote($column),
                self::quote($column)
            );

            $connection->executeStatement(
                $sql,
                ['ids' => $bytesList],
                ['ids' => ArrayParameterType::STRING]
            );

            return;
        }

        $fallbackId = null;
        if ($fallbackTable) {
            $fallbackId = self::getAnyForeignKeyValue($connection, $fallbackTable, $ids);
        }

        if ($fallbackId) {
            // Setze ungültige IDs auf die nächstbeste gültige Fallback ID
            $sql = sprintf(
                "UPDATE %s SET %s = UNHEX(:fallbackId) WHERE %s IN (:ids);",
                self::quote($table),
                self::quote($column),
                self::quote($column)
            );

            $connection->executeStatement(
                $sql,
                ['fallbackId' => $fallbackId, 'ids' => $bytesList],
                ['fallbackId' => ParameterType::STRING, 'ids' => ArrayParameterType::STRING]
            );

            return;
        }

        self::removeDuplicateEntries($connection, $table, $column, $ids);
    }

    public static function removeInvalidForeignKeys(Connection $connection, string $query, string $table): void
    {
        preg_match('/FOREIGN KEY \((.*?)\) REFERENCES (\w+)/', $query, $matches);
        if (!isset($matches[1]) || !isset($matches[2])) {
            throw new \RuntimeException(sprintf(
                "Error: Database inconsistency cannot be resolved and must be corrected manually. Query: %s; Table: %s",
                $query,
                $table
            ));
        }

        $foreignKeyColumn = trim($matches[1], '` ');
        $referencedTable = trim($matches[2], '` ');

        $invalidIds = $connection->fetchFirstColumn(sprintf(
            'SELECT LOWER(HEX(%s)) FROM %s WHERE %s IS NOT NULL AND %s NOT IN (SELECT `id` FROM %s);',
            self::quote($foreignKeyColumn),
            self::quote($table),
            self::quote($foreignKeyColumn),
            self::quote($foreignKeyColumn),
            self::quote($referencedTable)
        ));

        self::removeForeignKeyReferences($connection, $table, $foreignKeyColumn, $invalidIds, $referencedTable);
    }

    public static function addForeignKeyFromMeta(Connection $connection, string $table, string $constraintName, array $fkMeta): void
    {
        $cols     = implode(', ', array_map(self::quote(...), $fkMeta['columns']));
        $refCols  = implode(', ', array_map(self::quote(...), $fkMeta['ref_columns']));
        $refTable = self::quote($fkMeta['ref_table']);
        $onUpdate = strtoupper($fkMeta['update_rule'] ?? 'RESTRICT');
        $onDelete = strtoupper($fkMeta['delete_rule'] ?? 'RESTRICT');

        $sql = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            self::quote($table),
            self::quote($constraintName),
            $cols,
            $refTable,
            $refCols,
            $onDelete,
            $onUpdate
        );

        $connection->executeStatement($sql);
    }

    // ============================
    // === MIGRATION UTILITIES ====
    // ============================

    public static function migrationExists(Connection $connection, string $class): bool
    {
        $class = addcslashes($class, '\\_%') . '%';
        $sql = "SELECT * FROM `migration` WHERE `class` LIKE :class";
        return $connection->executeStatement($sql, ['class' => $class]) > 0;
    }

    public static function addMigration(Connection $connection, string $class, string $message = ""): void
    {
        $sql = <<<SQL
INSERT INTO `migration`
    (`class`, `creation_timestamp`, `update`, `message`)
VALUES
    (:class, :creation_timestamp, NOW(), :class)
ON DUPLICATE KEY UPDATE `update` = NOW();
SQL;
        $connection->executeStatement(
            $sql,
            ['class' => $class, 'creation_timestamp' => time(), 'message' => $message]
        );
    }

    public static function removeMigration(Connection $connection, string $class): void
    {
        $class = addcslashes($class, '\\_%') . '%';
        $sql = "DELETE FROM `migration` WHERE `class` LIKE :class";
        $connection->executeStatement($sql, ['class' => $class]);
    }

    // ===============================
    // === SCHEMA INTROSPECTION =====
    // ===============================

    public static function resolveForeignKey(Connection $connection, string $table, string $referencedTable): ?string
    {
        $sql = <<<SQL
SELECT COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = :table
  AND REFERENCED_TABLE_NAME = :referencedTable
  AND REFERENCED_COLUMN_NAME = 'id'
  AND CONSTRAINT_SCHEMA = DATABASE()
LIMIT 1
SQL;

        return $connection->fetchOne($sql, [
            'table' => $table,
            'referencedTable' => $referencedTable,
        ]) ?: null;
    }

    public static function constraintExists(Connection $connection, string $table, string $constraint): bool
    {
        $sql = sprintf(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '%s' AND CONSTRAINT_NAME = '%s'",
            $table,
            $constraint
        );
        return !empty($connection->fetchOne($sql));
    }

    public static function columnExists(Connection $connection, string $table, string $column): bool
    {
        $sql = sprintf(
            "SHOW COLUMNS FROM %s WHERE `Field` LIKE '%s'",
            self::quote($table),
            $column
        );
        return !empty($connection->fetchOne($sql));
    }

    public static function tableExists(Connection $connection, string $table): bool
    {
        $sql = sprintf("SHOW TABLES LIKE '%s'", $table);
        return !empty($connection->fetchOne($sql));
    }

    public static function isColumnNullable(Connection $connection, string $table, string $column): bool
    {
        $sql = "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table AND COLUMN_NAME = :column AND TABLE_SCHEMA = DATABASE()";
        $result = $connection->fetchOne($sql, [
            'table' => $table,
            'column' => $column
        ]);

        return strtoupper((string) $result) === 'YES';
    }

    public static function getForeignKeyMetaByName(Connection $connection, string $table, string $constraintName): ?array
    {
        $sql = <<<SQL
SELECT
  k.COLUMN_NAME,
  k.REFERENCED_TABLE_NAME,
  k.REFERENCED_COLUMN_NAME,
  rc.UPDATE_RULE,
  rc.DELETE_RULE,
  k.POSITION_IN_UNIQUE_CONSTRAINT
FROM information_schema.KEY_COLUMN_USAGE k
JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
  ON rc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
 AND rc.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
WHERE k.TABLE_SCHEMA = DATABASE()
  AND k.TABLE_NAME = :table
  AND k.CONSTRAINT_NAME = :cname
ORDER BY COALESCE(k.POSITION_IN_UNIQUE_CONSTRAINT, 1)
SQL;

        $rows = $connection->fetchAllAssociative(
            $sql,
            ['table' => $table, 'cname' => $constraintName]
        );

        if (!$rows) {
            return null;
        }

        $meta = [
            'columns'     => [],
            'ref_table'   => $rows[0]['REFERENCED_TABLE_NAME'],
            'ref_columns' => [],
            'update_rule' => $rows[0]['UPDATE_RULE'] ?: 'RESTRICT',
            'delete_rule' => $rows[0]['DELETE_RULE'] ?: 'RESTRICT',
        ];

        foreach ($rows as $r) {
            $meta['columns'][]     = $r['COLUMN_NAME'];
            $meta['ref_columns'][] = $r['REFERENCED_COLUMN_NAME'];
        }
        return $meta;
    }

    // =======================
    // === SQL UTILITIES ====
    // =======================

    public static function getAnyForeignKeyValue(Connection $connection, string $table, array $excludedIds = []): ?string
    {
        if (in_array($table, self::PRIVACY_PROTECTED_TABLES, true)) {
            return null;
        }

        $sql = sprintf("SELECT HEX(id) FROM %s WHERE id NOT IN (:ids) LIMIT 1", self::quote($table));

        return $connection->fetchOne(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($excludedIds)],
            ['ids' => ArrayParameterType::STRING]
        ) ?: null;
    }

    public static function dropForeignKey(Connection $connection, string $table, string $constraintName): void
    {
        $sql = sprintf("ALTER TABLE %s DROP FOREIGN KEY %s", self::quote($table), self::quote($constraintName));
        $connection->executeStatement($sql);
    }

    public static function dropIndexIfExists(Connection $connection, string $table, string $column): void
    {
        $sql = sprintf("ALTER TABLE %s DROP INDEX %s", self::quote($table), self::quote($column));
        $connection->executeStatement($sql);
    }

    public static function updateNotNullTableData(Connection $connection, string $query, string $table, ?string $column): void
    {
        if (preg_match('/CHANGE\\s+\\S+\\s+(\\S+).*?DEFAULT\\s+(.*)\\s+NOT\\s+NULL\\b/i', $query, $match)) {
            $column  = $match[1] ?? $column;
            $default = $match[2] ?? "0";
            $sql = sprintf(
                "UPDATE %s SET %s = %s WHERE %s IS NULL;",
                self::quote($table),
                self::quote($column),
                $default,
                self::quote($column)
            );
            $connection->executeStatement($sql);
        }
    }

    public static function addPrimaryKeys(Connection $connection, string $table, array $pks = ['id', 'version_id']): void
    {
        $sql = sprintf(
            "ALTER TABLE %s ADD PRIMARY KEY `PRIMARY` (%s), DROP INDEX `PRIMARY`;",
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

    public static function dropAndAddPrimaryKeys(Connection $connection, string $table, array $pks = ['id', 'version_id']): void
    {
        $sql = sprintf(
            "ALTER TABLE %s DROP PRIMARY KEY, ADD PRIMARY KEY (%s) USING BTREE;",
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

    public static function quote(string $string): string
    {
        $string = str_replace("`", "", $string);
        return sprintf("`%s`", trim($string));
    }
}
