<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Refactored helper with handler registry, context DTO, safer INFORMATION_SCHEMA queries,
 * robust FK detection (LEFT JOIN), and consistent UUID handling.
 */
final class EntityDefinitionQueryHelperV2
{
    public const PRIVACY_PROTECTED_TABLES = [
        CustomerDefinition::ENTITY_NAME,
    ];

    /** MySQL error codes */
    private const ER_DUP_ENTRY               = 1062;
    private const ER_BLOB_CANT_HAVE_DEFAULT  = 1170;
    private const ER_DUP_KEYNAME             = 1061;
    private const ER_FK_NEEDS_INDEX          = 1822;
    private const ER_CANNOT_ADD_FK           = 1005;
    private const ER_ROW_IS_REFERENCED_1     = 1217;
    private const ER_ROW_IS_REFERENCED_2     = 1451;
    private const ER_NO_REFERENCED_ROW_1     = 1216;
    private const ER_NO_REFERENCED_ROW_2     = 1452;
    private const ER_CANNOT_CHANGE_COLUMN    = 1832;

    /** @var array<int, callable(Exception, DbalErrorContext): bool> */
    private static array $HANDLERS = [
        self::ER_DUP_ENTRY               => [self::class, 'hDuplicateEntry'],
        self::ER_BLOB_CANT_HAVE_DEFAULT  => [self::class, 'hBlobKeyLength'],
        self::ER_DUP_KEYNAME             => [self::class, 'hIndexProblem'],
        self::ER_FK_NEEDS_INDEX          => [self::class, 'hMissingIndexOnFK'],
        self::ER_CANNOT_ADD_FK           => [self::class, 'hCreateTableFailure'],
        self::ER_ROW_IS_REFERENCED_1     => [self::class, 'hDeleteWithFKViolation'],
        self::ER_ROW_IS_REFERENCED_2     => [self::class, 'hDeleteWithFKViolation'],
        self::ER_NO_REFERENCED_ROW_1     => [self::class, 'hNoReferencedRow'],
        self::ER_NO_REFERENCED_ROW_2     => [self::class, 'hNoReferencedRow'],
        self::ER_CANNOT_CHANGE_COLUMN    => [self::class, 'hCannotChangeColumnUsedInFK'],
    ];

    // =========================
    // === PUBLIC INTERFACE ===
    // =========================

    /**
     * Tries to run $sql with limited retries. If a known DBAL exception occurs, we attempt a fix via handlers.
     *
     * @return void
     */
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

                // Give handlers a chance to resolve the problem
                $handled = self::handleDbalException($exception, $connection, $sql, $table, $column, $codes, $ids);

                if ($handled === true) {
                    // Source SQL already executed by handler (rare) -> stop
                    return;
                }

                // Exponential backoff with jitter
                $delay = (int) (100000 * (2 ** ($attempt - 1)) + random_int(0, 50000));
                usleep($delay);
                // Loop will retry executing $sql
            }
        }
    }

    // ==================================
    // === EXCEPTION HANDLING LOGIC ====
    // ==================================

    /**
     * Dispatch to a handler based on MySQL error code. Returns true if the original SQL has already been executed
     * within the handler and must NOT be retried. Returns false otherwise (caller should retry the original SQL).
     */
    public static function handleDbalException(
        Exception $exception,
        Connection $connection,
        ?string $sql = null,
        ?string $table = null,
        ?string $column = null,
        array $codes = [],
        array $ids = []
    ): bool {
        if ($codes && !in_array($exception->getCode(), $codes, true)) {
            throw $exception;
        }

        // Try to infer table name from the SQL if not provided
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
            self::updateNotNullTableData($connection, $sql ?? '', $table, $column);
            return false; // original SQL should be retried
        }

        $ctx = new DbalErrorContext(
            connection: $connection,
            sql: $sql,
            table: $table,
            column: $column,
            codes: $codes,
            ids: $ids
        );

        $handler = self::$HANDLERS[$exception->getCode()] ?? null;
        if (!$handler) {
            return false;
        }

        return $handler($exception, $ctx);
    }

    // ======================================
    // === HANDLER: SPECIFIC EXCEPTIONS ====
    // ======================================

    private static function hDuplicateEntry(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (!preg_match("/for key '([^']+)\\.uniq\\.([^']+)'/", $exception->getMessage(), $m)) {
            // Cannot parse -> bubble up to retry and likely fail again -> caller can surface
            throw $exception;
        }

        $errorTable = $m[1];
        $column = self::resolveForeignKey($ctx->connection, $errorTable, $ctx->table) ?? sprintf('%s_id', $ctx->table);

        if (!self::columnExists($ctx->connection, $errorTable, $column)) {
            throw $exception;
        }

        self::removeDuplicateEntries($ctx->connection, $errorTable, $column, $ctx->ids);
        return false;
    }

    private static function hBlobKeyLength(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (preg_match("/BLOB\\/TEXT column '([^']+)'/", $exception->getMessage(), $m)) {
            $column = $m[1];
            self::dropIndexIfExists($ctx->connection, $ctx->table, $column);
        }
        return false;
    }

    private static function hIndexProblem(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (preg_match("/Duplicate key name '([^']+)'|BLOB\\/TEXT column '([^']+)'/", $exception->getMessage(), $m)) {
            $indexOrColumn = $m[1] ?? $m[2] ?? null;
            if ($indexOrColumn) {
                self::dropIndexIfExists($ctx->connection, $ctx->table, $indexOrColumn);
            }
        }
        return false;
    }

    private static function hMissingIndexOnFK(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (preg_match("/in the referenced table '([^']+)'/", $exception->getMessage(), $m)) {
            self::addPrimaryKeysIfMissing($ctx->connection, $m[1]);
        }
        return false;
    }

    private static function hCreateTableFailure(Exception $exception, DbalErrorContext $ctx): bool
    {
        if ($ctx->sql && preg_match("/REFERENCES (\\S+)/", $ctx->sql, $m)) {
            self::dropAndAddPrimaryKeys($ctx->connection, trim($m[1], '`" '));
        }
        return false;
    }

    private static function hDeleteWithFKViolation(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (preg_match(
            '/fails \(`[^`]+`\.`(?<table>[^`]+)`, CONSTRAINT `(?<constraint>[^`]+)` FOREIGN KEY \(`(?<column>[^`,]+)`/',
            $exception->getMessage(),
            $m
        )) {
            $errorTable = $m['table'];
            $column = str_replace('version_', '', $m['column']);
        } else {
            throw $exception;
        }

        self::removeForeignKeyReferences($ctx->connection, $errorTable, $column, $ctx->ids, $ctx->table);
        return false;
    }

    private static function hNoReferencedRow(Exception $exception, DbalErrorContext $ctx): bool
    {
        // Repairs broken FK values in $ctx->table based on $ctx->sql
        self::removeInvalidForeignKeys($ctx->connection, $ctx->sql ?? '', $ctx->table);
        return false;
    }

    private static function hCannotChangeColumnUsedInFK(Exception $exception, DbalErrorContext $ctx): bool
    {
        if (!preg_match(
            "/Cannot change column '[^']+': used in a foreign key constraint '([^']+)'/i",
            $exception->getMessage(),
            $m
        )) {
            throw $exception;
        }

        $constraintName = $m[1];
        $fkMeta = self::getForeignKeyMetaByName($ctx->connection, $ctx->table, $constraintName);
        if (!$fkMeta) {
            throw $exception;
        }

        self::dropForeignKey($ctx->connection, $ctx->table, $constraintName);

        // Execute original ALTER after dropping FK
        if ($ctx->sql) {
            $ctx->connection->executeStatement($ctx->sql);
        }

        self::addForeignKeyFromMeta($ctx->connection, $ctx->table, $constraintName, $fkMeta);
        return true; // source SQL already executed
    }

    // ============================
    // === FOREIGN KEY ACTIONS ===
    // ============================

    public static function removeDuplicateEntries(Connection $connection, string $table, string $column, array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        self::assertHexUuidList($ids);

        $sql = sprintf('DELETE FROM %s WHERE %s IN (:ids);', self::quote($table), self::quote($column));
        $connection->executeStatement(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );
    }

    /**
     * If $column is nullable → set to NULL for the given (invalid) ids.
     * If NOT NULL → try fallback id from $fallbackTable; if none, delete the rows.
     */
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
        self::assertHexUuidList($ids);

        $bytesList = Uuid::fromHexToBytesList($ids);
        $isNullable = self::isColumnNullable($connection, $table, $column);

        if ($isNullable) {
            $sql = sprintf(
                'UPDATE %s SET %s = NULL WHERE %s IN (:ids);',
                self::quote($table),
                self::quote($column),
                self::quote($column)
            );
            $connection->executeStatement($sql, ['ids' => $bytesList], ['ids' => ArrayParameterType::STRING]);
            return;
        }

        $fallbackId = null;
        if ($fallbackTable) {
            $fallbackId = self::getAnyForeignKeyValue($connection, $fallbackTable, $ids);
        }

        if ($fallbackId) {
            $sql = sprintf(
                'UPDATE %s SET %s = UNHEX(:fallbackId) WHERE %s IN (:ids);',
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

        // no fallback → delete rows
        self::removeDuplicateEntries($connection, $table, $column, $ids);
    }

    /**
     * Detects invalid foreign keys for the FK mentioned in $query and repairs via removeForeignKeyReferences().
     */
    public static function removeInvalidForeignKeys(Connection $connection, string $query, string $table): void
    {
        // Parse FK column + referenced table/column from ALTER/CREATE query
        $m = [];
        if (!preg_match('/FOREIGN\s+KEY\s*\(([^)]+)\)\s*REFERENCES\s*([`"\w\.]+)\s*\(([^)]+)\)/i', $query, $m)) {
            throw new \RuntimeException(sprintf(
                'Error: Database inconsistency cannot be resolved and must be corrected manually. Query: %s; Table: %s',
                $query,
                $table
            ));
        }
        $fkColumn   = trim($m[1], '`" ');
        $refTable   = trim($m[2], '`" ');
        $refColumn  = trim($m[3], '`" ');

        // Validate via INFORMATION_SCHEMA (fallback to discovered values)
        $fk = self::getFkTarget($connection, $table, null, $fkColumn);
        $refTable  = $fk['ref_table'] ?? $refTable;
        $refColumn = $fk['ref_col']   ?? $refColumn ?? 'id';

        // Collect invalid ids as lowercase hex using LEFT JOIN (index friendly)
        $sql = sprintf(
            'SELECT LOWER(HEX(t.%1$s))
               FROM %2$s t
          LEFT JOIN %3$s r ON t.%1$s = r.%4$s
              WHERE t.%1$s IS NOT NULL
                AND r.%4$s IS NULL',
            self::quote($fkColumn),
            self::quote($table),
            self::quote($refTable),
            self::quote($refColumn)
        );
        $invalidIds = $connection->fetchFirstColumn($sql);

        self::removeForeignKeyReferences($connection, $table, $fkColumn, $invalidIds, $refTable);
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
    // === MIGRATION UTILITIES ===
    // ============================

    public static function migrationExists(Connection $connection, string $class): bool
    {
        $classLike = addcslashes($class, '\\_%') . '%';
        $sql = 'SELECT 1 FROM `migration` WHERE `class` LIKE :class LIMIT 1';
        return (bool) $connection->fetchOne($sql, ['class' => $classLike]);
    }

    public static function addMigration(Connection $connection, string $class, string $message = ''): void
    {
        $sql = <<<'SQL'
INSERT INTO `migration` (`class`, `creation_timestamp`, `update`, `message`)
VALUES (:class, :creation_timestamp, NOW(), :message)
ON DUPLICATE KEY UPDATE `update` = NOW(), `message` = VALUES(`message`);
SQL;
        $connection->executeStatement(
            $sql,
            ['class' => $class, 'creation_timestamp' => time(), 'message' => $message]
        );
    }

    public static function removeMigration(Connection $connection, string $class): void
    {
        $like = addcslashes($class, '\\_%') . '%';
        $sql = 'DELETE FROM `migration` WHERE `class` LIKE :class';
        $connection->executeStatement($sql, ['class' => $like]);
    }

    // ===============================
    // === SCHEMA INTROSPECTION =====
    // ===============================

    public static function resolveForeignKey(Connection $connection, string $table, string $referencedTable): ?string
    {
        $sql = <<<'SQL'
SELECT COLUMN_NAME
  FROM information_schema.KEY_COLUMN_USAGE
 WHERE TABLE_NAME = :table
   AND REFERENCED_TABLE_NAME = :ref
   AND REFERENCED_COLUMN_NAME = 'id'
   AND CONSTRAINT_SCHEMA = DATABASE()
 LIMIT 1
SQL;
        return $connection->fetchOne($sql, ['table' => $table, 'ref' => $referencedTable]) ?: null;
    }

    public static function constraintExists(Connection $connection, string $table, string $constraint): bool
    {
        $sql = <<<'SQL'
SELECT 1
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = :t
   AND CONSTRAINT_NAME = :c
 LIMIT 1
SQL;
        return (bool) $connection->fetchOne($sql, ['t' => $table, 'c' => $constraint]);
    }

    public static function columnExists(Connection $connection, string $table, string $column): bool
    {
        $sql = <<<'SQL'
SELECT 1
  FROM INFORMATION_SCHEMA.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = :t
   AND COLUMN_NAME  = :c
 LIMIT 1
SQL;
        return (bool) $connection->fetchOne($sql, ['t' => $table, 'c' => $column]);
    }

    public static function tableExists(Connection $connection, string $table): bool
    {
        $sql = <<<'SQL'
SELECT 1
  FROM INFORMATION_SCHEMA.TABLES
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = :t
 LIMIT 1
SQL;
        return (bool) $connection->fetchOne($sql, ['t' => $table]);
    }

    public static function primaryKeyExists(Connection $connection, string $table): bool
    {
        $sql = <<<'SQL'
SELECT 1
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = :t
   AND CONSTRAINT_TYPE = 'PRIMARY KEY'
 LIMIT 1
SQL;
        return (bool) $connection->fetchOne($sql, ['t' => $table]);
    }

    public static function isColumnNullable(Connection $connection, string $table, string $column): bool
    {
        $sql = <<<'SQL'
SELECT IS_NULLABLE
  FROM INFORMATION_SCHEMA.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE()
   AND TABLE_NAME   = :t
   AND COLUMN_NAME  = :c
SQL;
        $result = $connection->fetchOne($sql, ['t' => $table, 'c' => $column]);
        return strtoupper((string) $result) === 'YES';
    }

    public static function getForeignKeyMetaByName(Connection $connection, string $table, string $constraintName): ?array
    {
        $sql = <<<'SQL'
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
        $rows = $connection->fetchAllAssociative($sql, ['table' => $table, 'cname' => $constraintName]);
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

    /** Resolve referenced table/column for a given FK column or constraint */
    private static function getFkTarget(Connection $connection, string $table, ?string $constraintName, ?string $column): ?array
    {
        $sql = <<<'SQL'
SELECT k.REFERENCED_TABLE_NAME AS ref_table,
       k.REFERENCED_COLUMN_NAME AS ref_col,
       k.COLUMN_NAME AS col
  FROM information_schema.KEY_COLUMN_USAGE k
 WHERE k.TABLE_SCHEMA = DATABASE()
   AND k.TABLE_NAME   = :t
   AND (:cname IS NULL OR k.CONSTRAINT_NAME = :cname)
   AND (:col   IS NULL OR k.COLUMN_NAME    = :col)
   AND k.REFERENCED_TABLE_NAME IS NOT NULL
 LIMIT 1
SQL;
        $row = $connection->fetchAssociative($sql, ['t' => $table, 'cname' => $constraintName, 'col' => $column]);
        return $row ?: null;
    }

    // =======================
    // === SQL UTILITIES ====
    // =======================

    /**
     * Returns a HEX(id) from $table that is not in $excludedIds. Respects PRIVACY_PROTECTED_TABLES.
     */
    public static function getAnyForeignKeyValue(Connection $connection, string $table, array $excludedIds = []): ?string
    {
        if (in_array($table, self::PRIVACY_PROTECTED_TABLES, true)) {
            return null;
        }

        if (empty($excludedIds)) {
            $sql = sprintf('SELECT HEX(id) FROM %s LIMIT 1', self::quote($table));
            return $connection->fetchOne($sql) ?: null;
        }

        $sql = sprintf('SELECT HEX(id) FROM %s WHERE id NOT IN (:ids) LIMIT 1', self::quote($table));
        return $connection->fetchOne(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($excludedIds)],
            ['ids' => ArrayParameterType::STRING]
        ) ?: null;
    }

    public static function dropForeignKey(Connection $connection, string $table, string $constraintName): void
    {
        $sql = sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', self::quote($table), self::quote($constraintName));
        $connection->executeStatement($sql);
    }

    /** Drops either by exact index name, or by first index that covers the given column */
    public static function dropIndexIfExists(Connection $connection, string $table, string $indexNameOrColumn): void
    {
        // Try by exact index name
        $has = (bool) $connection->fetchOne(
            'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND INDEX_NAME = :i LIMIT 1',
            ['t' => $table, 'i' => $indexNameOrColumn]
        );
        if ($has) {
            $connection->executeStatement(sprintf('ALTER TABLE %s DROP INDEX %s', self::quote($table), self::quote($indexNameOrColumn)));
            return;
        }

        // Try by column → pick a covering index name
        $idx = $connection->fetchOne(
            'SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1',
            ['t' => $table, 'c' => $indexNameOrColumn]
        );
        if ($idx) {
            $connection->executeStatement(sprintf('ALTER TABLE %s DROP INDEX %s', self::quote($table), self::quote((string) $idx)));
        }
    }

    /**
     * If the incoming ALTER sets NOT NULL with a DEFAULT, we normalize NULLs to the default first.
     */
    public static function updateNotNullTableData(Connection $connection, string $query, string $table, ?string $column): void
    {
        if (preg_match('/CHANGE\s+\S+\s+(\S+).*?DEFAULT\s+([^\s]+)\s+NOT\s+NULL\b/i', $query, $match)) {
            $col     = trim($match[1] ?? (string) $column, '`" ');
            $default = $match[2] ?? '0';
            $sql = sprintf(
                'UPDATE %s SET %s = %s WHERE %s IS NULL;',
                self::quote($table),
                self::quote($col),
                $default,
                self::quote($col)
            );
            $connection->executeStatement($sql);
        }
    }

    public static function addPrimaryKeysIfMissing(Connection $connection, string $table, array $pks = ['id', 'version_id']): void
    {
        if (self::primaryKeyExists($connection, $table)) {
            return;
        }
        $sql = sprintf(
            'ALTER TABLE %s ADD PRIMARY KEY (%s);',
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

    public static function dropAndAddPrimaryKeys(Connection $connection, string $table, array $pks = ['id', 'version_id']): void
    {
        $sql = sprintf(
            'ALTER TABLE %s DROP PRIMARY KEY, ADD PRIMARY KEY (%s) USING BTREE;',
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

    /** Quote identifier with backticks (keeps it static-usable). Do NOT pass values here. */
    public static function quote(string $identifier): string
    {
        $identifier = str_replace('`', '', $identifier);
        return sprintf('`%s`', trim($identifier));
    }

    // =====================
    // === SMALL HELPERS ===
    // =====================

    private static function assertHexUuidList(array $ids): void
    {
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new \RuntimeException('Error: Ids must be 32-char hex UUIDs');
            }
        }
    }
}
