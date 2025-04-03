<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Shopware\Core\Framework\Struct\Struct;

class OperationStruct extends Struct
{
    public const COLUMN = 'column';
    public const CONSTRAINT = 'constraint';
    public const TABLE = 'table';
    public const KEY = 'key';
    public const ADD = 'ADD';
    public const CREATE = 'CREATE';
    public const MODIFY = 'MODIFY';
    public const CHANGE = 'CHANGE';
    public const DROP = 'DROP';
    public const FIRST = 'FIRST';
    public const EL_TYPES = ['column', 'constraint', 'table'];
    public const EL_TYPES_ALL = ['column', 'constraint', 'table', 'index', 'unique'];
    public const OP_TYPES_ADD = ['ADD', 'CREATE'];
    public const OP_TYPES_CHANGE = ['MODIFY', 'CHANGE'];
    public const OP_TYPES_DROP = ['DROP'];
    public const OP_TYPES_ALL = ['ADD', 'CREATE', 'MODIFY', 'CHANGE', 'DROP'];

    public const INDEX = 'index';
    public const UNIQUE = 'unique';

    public function __construct(
        private readonly string $query,
        private readonly ?string $table = null,
        private readonly ?string $elType = null,
        private readonly ?string $opType = null,
        private readonly ?string $column = null,
        private ?string $afterColumn = null,
        private bool $sort = false
    )
    {
        if ($elType && !in_array($elType, self::EL_TYPES_ALL)) {
            throw new \RuntimeException(sprintf(
                "Forbidden element type %s in %s. Allowed: %s",
                $elType,
                self::class,
                implode(", ", self::EL_TYPES_ALL)
            ));
        }

        if ($opType && !in_array($opType, self::OP_TYPES_ALL)) {
            throw new \RuntimeException(sprintf(
                "Forbidden operation type %s in %s. Allowed: %s",
                $opType,
                self::class,
                implode(", ", self::OP_TYPES_ALL)
            ));
        }
    }

    public function isSort(): bool
    {
        return $this->sort;
    }

    public function setSort(bool $sort): void
    {
        $this->sort = $sort;
    }

    public function isAdd(): bool
    {
        return $this->opType && in_array($this->opType, self::OP_TYPES_ADD);
    }

    public function isColumn(): bool
    {
        return $this->elType && $this->elType === self::COLUMN;
    }

    public function isTable(): bool
    {
        return $this->elType && $this->elType === self::TABLE;
    }

    public function isConstraint(): bool
    {
        return $this->elType && $this->elType === self::CONSTRAINT;
    }

    public function setAfterColumn(?string $afterColumn): void
    {
        $this->afterColumn = $afterColumn;
    }

    public function getQuery(): string
    {
        return $this->query . ";";
    }

    public function getQueryWithSorting(): string
    {
        if ($this->sort && $this->elType && $this->elType === self::COLUMN && $this->column) {
            return $this->query . " " . ($this->afterColumn ? "AFTER {$this->afterColumn}" : self::FIRST) . ";";
        }

        return $this->query;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function getElType(): ?string
    {
        return $this->elType;
    }

    public function getOpType(): ?string
    {
        return $this->opType;
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }

    public function getAfterColumn(): ?string
    {
        return $this->afterColumn;
    }

    public function getApiAlias(): string
    {
        return 'dbal_operation';
    }
}
