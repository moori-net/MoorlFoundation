<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityStock;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class EntityStockIndexerEvent extends NestedEvent
{
    private Context $context;
    private array $ids;
    private string $entityName;

    public function __construct(string $entityName, array $ids, Context $context)
    {
        $this->context = $context;
        $this->ids = $ids;
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
