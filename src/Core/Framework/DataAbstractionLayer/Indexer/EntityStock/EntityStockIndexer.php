<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityStock;

use Moorl\MultiStock\Core\Content\Product\Subscriber\MultiStockStockUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityStockIndexer extends EntityIndexer
{
    private IteratorFactory $iteratorFactory;
    private EntityRepositoryInterface $repository;
    private EventDispatcherInterface $eventDispatcher;
    private MultiStockStockUpdater $stockUpdater;
    private string $entityName;

    public function __construct(
        IteratorFactory $iteratorFactory,
        EntityRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        MultiStockStockUpdater $stockUpdater
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
        $this->stockUpdater = $stockUpdater;

        $this->entityName = $this->repository->getDefinition()->getEntityName();
    }

    public function getName(): string
    {
        return $this->entityName . '.indexer';
    }

    public function iterate(/*?array */$offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new EntityStockIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $ids = $event->getPrimaryKeys($this->entityName);

        if (empty($ids)) {
            return null;
        }

        $this->stockUpdater->update($ids, $event->getContext());

        return new EntityStockIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        $ids = array_unique(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        $context = $message->getContext();
        $this->stockUpdater->update($ids, $context);

        $this->eventDispatcher->dispatch(new EntityStockIndexerEvent($ids, $context));
    }
}
