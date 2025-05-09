<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Framework\Uuid\Uuid;

class EntityDefinitionQueryHelper
{
    public static function tryExecuteStatement(
        Connection $connection,
        string $sql,
        ?string $table = null,
        ?string $column = null,
        array $codes = [],
        array $ids = []
    ): void
    {
        try {
            $connection->executeStatement($sql);
        } catch (Exception $exception) {
            usleep(100000);

            self::handleDbalException(
                $exception,
                $connection,
                $sql,
                $table,
                $column,
                $codes,
                $ids
            );

            usleep(100000);

            $connection->executeStatement($sql);
        }
    }

    public static function handleDbalException(
        Exception $exception,
        Connection $connection,
        ?string $sql = null,
        ?string $table = null,
        ?string $column = null,
        array $codes = [],
        array $ids = []
    ): void
    {
        // Just fix pre-defined codes to secure prevent changes in Shopware tables
        if (count($codes) > 0) {
            if (!in_array($exception->getCode(), $codes, true)) {
                throw $exception;
            }
        }

        if ($sql && !$table) {
            $pattern = '/\b(?:ALTER\s+TABLE|CREATE\s+TABLE|INSERT\s+INTO|UPDATE|DELETE\s+FROM|DROP\s+TABLE)\s+`?(?<table>[a-zA-Z0-9_]+)`?/i';
            if (preg_match($pattern, $sql, $matches)) {
                $table = $matches['table'] ?? null;
            }
        }

        if (!$table) {
            throw $exception;
        }

        if ($exception instanceof NotNullConstraintViolationException) {
            self::updateNotNullTableData($connection, $sql, $table, $column);
            return;
        }

        switch ($exception->getCode()) {
            case 1062:
                // Error number: 1062; SQLSTATE: 23000
                // Message: Integrity constraint violation: 1062 Duplicate entry '...'
                // for key 'product_visibility.uniq.product_id__sales_channel_id'
                if (preg_match("/for key '([^']+)\.uniq\.([^\']+)'/", $exception->getMessage(), $matches)) {
                    $errorTable = $matches[1];
                } else {
                    throw $exception;
                }
                $column = self::resolveForeignKey($connection, $errorTable, $table) ?? sprintf("%s_id", $table);
                if (!self::columnExists($connection, $errorTable, $column)) {
                    throw $exception;
                }

                self::removeDuplicateEntries($connection, $errorTable, $column, $ids);
                break;

            case 1170:
                // Error number: 1170; Symbol: ER_BLOB_KEY_WITHOUT_LENGTH; SQLSTATE: 42000
                // Message: BLOB/TEXT column '%s' used in key specification without a key length
                if (!$column && preg_match("/BLOB\/TEXT column '([^']+)'/", $exception->getMessage(), $matches)) {
                    $column = $matches[1];
                }
                self::dropIndexIfExists($connection, $table, $column);
                break;

            case 1061:
                // Error number: 1061; Symbol: ER_DUP_KEYNAME; SQLSTATE: 42000
                // Message: Duplicate key name '%s'
                if (!$column && preg_match("/Duplicate key name '([^']+)'/", $exception->getMessage(), $matches)) {
                    $column = $matches[1];
                }
                self::dropIndexIfExists($connection, $table, $column);
                break;

            case 1822:
                // Error number: 1822; Symbol: ER_FK_NO_INDEX_PARENT; SQLSTATE: HY000
                // Message: Failed to add the foreign key constraint. Missing index for constraint '%s' in the referenced table '%s'
                if (preg_match("/in the referenced table '([^']+)'/", $exception->getMessage(), $matches)) {
                    self::addPrimaryKeys($connection, $matches[1]);
                }
                break;

            case 1005:
                // Error number: 1005; Symbol: ER_CANT_CREATE_TABLE; SQLSTATE: HY000
                // Message: Can't create table '%s' (errno: %d - %s)
                // InnoDB reports this error when a table cannot be created. If the error message refers to error 150, table creation failed because a foreign key constraint was not correctly formed.
                // If the error message refers to error −1, table creation probably failed because the table includes a column name that matched the name of an internal InnoDB table.
                if (preg_match("/REFERENCES (\S+)/", $sql, $matches)) {
                    self::dropAndAddPrimaryKeys($connection, $matches[1]);
                }
                break;

            case 1217:
            case 1451:
                // Error number: 1217; Symbol: ER_ROW_IS_REFERENCED; SQLSTATE: 23000
                // Error number: 1451; Symbol: ER_ROW_IS_REFERENCED_2; SQLSTATE: 23000
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
                break;

            case 1452:
            case 1216:
                // Error number: 1452; Symbol: ER_NO_REFERENCED_ROW_2; SQLSTATE: 23000
                // Error number: 1216; Symbol: ER_NO_REFERENCED_ROW; SQLSTATE: 23000
                // Message: Cannot add or update a child row: a foreign key constraint fails (%s)
                // InnoDB reports this error when you try to add a row but there is no parent row, and a foreign key constraint fails. Add the parent row first.
                if (!$sql) {
                    throw $exception;
                }
                self::removeInvalidForeignKeys($connection, $sql, $table);
                break;
        }
    }

    public static function removeDuplicateEntries(Connection $connection, string $table, string $column, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s IN (:ids);",
            self::quote($table),
            self::quote($column)
        );

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

        $isNullable = self::isColumnNullable($connection, $table, $column);
        if (!$isNullable) {
            if (!$fallbackTable) {
                return;
            }

            $fallbackId = self::getAnyForeignKeyValue($connection, $fallbackTable, $ids);
            if (!$fallbackId) {
                return;
            }

            $sql = sprintf(
                "UPDATE %s SET %s = UNHEX(:fallbackId) WHERE %s IN (:ids);",
                self::quote($table),
                self::quote($column),
                self::quote($column)
            );

            $connection->executeStatement(
                $sql,
                ['fallbackId' => $fallbackId, 'ids' => Uuid::fromHexToBytesList($ids),],
                ['fallbackId' => ParameterType::STRING, 'ids' => ArrayParameterType::STRING]
            );

            return;
        }

        // Standardfall: FK darf NULL sein
        $sql = sprintf(
            "UPDATE %s SET %s = NULL WHERE %s IN (:ids);",
            self::quote($table),
            self::quote($column),
            self::quote($column)
        );

        $connection->executeStatement(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );
    }


    public static function removeInvalidForeignKeys(Connection $connection, string $query, string $table)
    {
        preg_match('/FOREIGN KEY \((.*?)\) REFERENCES (\w+)/', $query, $matches);
        $foreignKeyColumn = trim($matches[1], '` ');
        $referencedTable = trim($matches[2], '` ');

        $sql = sprintf(
            "UPDATE %s SET %s = NULL WHERE %s IS NOT NULL AND %s NOT IN (SELECT `id` FROM %s);",
            self::quote($table),
            self::quote($foreignKeyColumn),
            self::quote($foreignKeyColumn),
            self::quote($foreignKeyColumn),
            self::quote($referencedTable)
        );

        $connection->executeStatement($sql);
    }

    public static function addPrimaryKeys(
        Connection $connection,
        string $table,
        array $pks = ['id', 'version_id']
    ): void
    {
        $sql = sprintf(
            "ALTER TABLE %s ADD PRIMARY KEY `PRIMARY` (%s), DROP INDEX `PRIMARY`;",
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

    public static function dropAndAddPrimaryKeys(
        Connection $connection,
        string $table,
        array $pks = ['id', 'version_id']
    ): void
    {
        $sql = sprintf(
            "ALTER TABLE %s DROP PRIMARY KEY, ADD PRIMARY KEY (%s) USING BTREE;",
            self::quote($table),
            implode(', ', array_map(self::quote(...), $pks))
        );
        $connection->executeStatement($sql);
    }

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
        $connection->executeStatement(
            $sql,
            ['class' => $class]);
    }

    public static function dropIndexIfExists(Connection $connection, string $table, string $column): void
    {
        $sql = sprintf(
            "ALTER TABLE %s DROP INDEX %s",
            self::quote($table),
            self::quote($column)
        );
        $connection->executeStatement($sql);
    }

    public static function updateNotNullTableData(Connection $connection, string $query, string $table, ?string $column): void
    {
        if (preg_match('/CHANGE\s+\S+\s+(\S+).*?DEFAULT\s+(.*)\s+NOT\s+NULL\b/i', $query, $match)) {
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
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = '%s' AND CONSTRAINT_NAME = '%s'",
            $table,
            $constraint
        );
        $result = $connection->fetchOne($sql);

        return !empty($result);
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

    public static function getAnyForeignKeyValue(Connection $connection, string $table, array $excludedIds = []): ?string
    {
        $sql = sprintf("SELECT HEX(id) FROM %s WHERE id NOT IN (:ids) LIMIT 1", self::quote($table));

        return $connection->fetchOne($sql,
            ['ids' => Uuid::fromHexToBytesList($excludedIds)],
            ['ids' => ArrayParameterType::STRING]
        ) ?: null;
    }

    public static function quote(string $string): string
    {
        $string = str_replace("`", "", $string); // prevent double backticks

        return sprintf("`%s`", trim($string));
    }
}
