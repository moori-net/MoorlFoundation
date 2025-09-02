<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityLocation;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

/**
 * @deprecated: Use moorl.foundation.entity_auto_location tag instead
 */
class EntityLocationIndexerEvent extends NestedEvent
{
    public function __construct(
        private readonly array $ids,
        private string $entityName,
        private readonly Context $context,
        private readonly array $skip = []
    )
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
