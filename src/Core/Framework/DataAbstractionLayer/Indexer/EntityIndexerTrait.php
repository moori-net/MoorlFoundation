<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;

trait EntityIndexerTrait
{
    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->getIterator($offset);
        $ids = $iterator->fetch();
        if (empty($ids)) {
            return null;
        }

        return new EntityIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    protected function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        // TODO: Implement getDecorated() method.
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $entityEvent = $event->getEventByEntityName($this->repository->getDefinition()->getEntityName());
        if (!$entityEvent) {
            return null;
        }

        foreach ($entityEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }

            $payload = $result->getPayload();
            if (isset($payload['id'])) {
                $ids[] = $payload['id'];
            }
        }

        if (empty($ids)) {
            return null;
        }

        return new EntityIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function handle(EntityIndexingMessage $message): void
    {
        // TODO: Implement handle() method.
    }
}
