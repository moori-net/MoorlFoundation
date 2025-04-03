<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;

class EntityDefinitionQueryHelper
{
    public static function tryExecuteStatement(Connection $connection, string $sql, ?string $table = null): void
    {
        try {
            $connection->executeStatement($sql);
        } catch (NotNullConstraintViolationException $exception) {
            self::updateNotNullTableData($connection, $sql, $table);
            $connection->executeStatement($sql);
        }
    }

    public static function updateNotNullTableData(Connection $connection, string $query, ?string $table): void
    {
        if (!$table) {
            return;
        }

        preg_match_all('/CHANGE\s+\S+\s+(\S+).*?DEFAULT\s+\'([^\']*)\'.*?NOT\s+NULL\b/i', $query, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $column = $match[1];
            $default = $match[2] ?? "0";
            $sql = sprintf("UPDATE `%s` SET `%s` = '%s' WHERE `%s` IS NULL;", $table, $column, $default, $column);
            $connection->executeStatement($sql);
        }
    }

    public static function constraintExists(Connection $connection, string $table, string $constraint): bool
    {
        $sql = <<<SQL
SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = :table AND CONSTRAINT_NAME = :constraint;
SQL;
        $result = $connection->fetchOne($sql, ['table' => $table, 'constraint' => $constraint]);

        return !empty($result);
    }

    public static function columnExists(Connection $connection, string $table, string $column): bool
    {
        $table = $connection->quoteIdentifier($table);
        $sql = "SHOW COLUMNS FROM {$table} WHERE `Field` LIKE :column";
        $result = $connection->fetchOne($sql, ['column' => $column]);

        return !empty($result);
    }

    public static function tableExists(Connection $connection, string $table): bool
    {
        return !empty(
            $connection->fetchOne(
                'SHOW TABLES LIKE :table',
                [
                    'table' => $table,
                ]
            )
        );
    }
}
