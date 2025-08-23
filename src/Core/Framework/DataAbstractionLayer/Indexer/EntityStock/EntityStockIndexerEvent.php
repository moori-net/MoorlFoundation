<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityStock;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class EntityStockIndexerEvent extends NestedEvent
{
    private readonly string $entityName;

    public function __construct(string $entityName, private readonly array $ids, private readonly Context $context)
    {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}
