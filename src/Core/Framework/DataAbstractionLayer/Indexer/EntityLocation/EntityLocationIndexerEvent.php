<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityLocation;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class EntityLocationIndexerEvent extends NestedEvent
{
    private Context $context;
    private array $ids;
    private array $skip;
    private string $entityName;

    public function __construct(array $ids, string $entityName, Context $context, array $skip = [])
    {
        $this->context = $context;
        $this->ids = $ids;
        $this->entityName = $entityName;
        $this->skip = $skip;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getSkip(): array
    {
        return $this->skip;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }
}
