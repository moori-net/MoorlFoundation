<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;

class EntityDefinitionQueryHelper
{
    public static function tryExecuteStatement(
        Connection $connection,
        string $sql,
        ?string $table = null,
        ?string $column = null
    ): void
    {

        $connection->executeStatement($sql);return;

        try {
            $connection->executeStatement($sql);
        } catch (Exception $exception) {
            self::handleDbalException($exception, $connection, $sql, $table, $column);

            $connection->executeStatement($sql);
        }
    }

    public static function handleDbalException(
        Exception $exception,
        Connection $connection,
        string $sql,
        ?string $table = null,
        ?string $column = null
    ): void
    {
        if (!$table) {
            // Extrahiere den Tabellennamen anhand gängiger SQL-Befehle
            $pattern = '/\b(?:ALTER\s+TABLE|CREATE\s+TABLE|INSERT\s+INTO|UPDATE|DELETE\s+FROM|DROP\s+TABLE)\s+`?(?<table>[a-zA-Z0-9_]+)`?/i';
            if (preg_match($pattern, $sql, $matches)) {
                $table = $matches['table'] ?? null;
            }
        }

        if (!$table) {
            throw $exception;
        }

        if ($exception instanceof NotNullConstraintViolationException) {
            // Falls eine Spalte auf NOT NULL gesetzt ist und NULL-Werte enthält,
            self::updateNotNullTableData($connection, $sql, $table, $column);
        } elseif ($exception->getCode() === 1170) {
            // Falls ein Unique-Key hinzugefügt wird, aber bereits ein Index mit gleichem Namen existiert
            if (!$column) {
                if (preg_match("/BLOB\/TEXT column '([^']+)'/", $exception->getMessage(), $matches)) {
                    $column = $matches[1];
                }
            }
            self::dropIndexIfExists($connection, $table, $column);
        } elseif ($exception->getCode() === 1061) {
            // Falls ein Foreign-Key hinzugefügt wird, aber bereits ein Index mit gleichem Namen existiert
            if (!$column) {
                if (preg_match("/Duplicate key name '([^']+)'/", $exception->getMessage(), $matches)) {
                    $column = $matches[1];
                }
            }
            self::dropIndexIfExists($connection, $table, $column);
        } elseif ($exception->getCode() === 1822) {
            // Fehler in der SQL-Abfrage (1822): Failed to add the foreign key constraint. Missing index for constraint
            if (preg_match("/in the referenced table '([^']+)'/", $exception->getMessage(), $matches)) {
                self::makeVersionPrimaryKey($connection, $matches[1]);
            }
        }
    }

    public static function makeVersionPrimaryKey(Connection $connection, string $table)
    {
        $sql = sprintf(
            "ALTER TABLE %s ADD PRIMARY KEY `PRIMARY` (`id`, `version_id`), DROP INDEX `PRIMARY`;",
            self::quote($table)
        );
        $connection->executeStatement($sql);
    }

    public static function migrationExists(Connection $connection, string $class): bool
    {
        $class = addcslashes($class, '\\_%') . '%';
        $sql = "SELECT * FROM `migration` WHERE `class` LIKE :class";
        return $connection->executeStatement($sql, ['class' => $class]) > 0;
    }

    public static function addMigration(Connection $connection, string $class): void
    {
        $sql = <<<SQL
INSERT INTO `migration` 
    (`class`, `creation_timestamp`, `update`)
VALUES 
    (:class, :creation_timestamp, NOW())
ON DUPLICATE KEY UPDATE `update` = NOW();
SQL;
        $connection->executeStatement(
            $sql,
            ['class' => $class, 'creation_timestamp' => time()]
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

    public static function quote(string $string): string
    {
        return sprintf("`%s`", trim($string));
    }
}
