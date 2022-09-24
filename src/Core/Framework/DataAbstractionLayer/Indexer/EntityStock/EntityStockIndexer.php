<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityStock;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityStockIndexer extends EntityIndexer
{
    private IteratorFactory $iteratorFactory;
    private EntityRepository $entityRepository;
    private EventDispatcherInterface $eventDispatcher;
    private EntityStockUpdater $entityStockUpdater;
    private string $entityName;

    public function __construct(
        IteratorFactory $iteratorFactory,
        EntityRepository $entityRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityStockUpdater $entityStockUpdater
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->entityRepository = $entityRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityStockUpdater = $entityStockUpdater;

        $this->entityName = $this->entityRepository->getDefinition()->getEntityName();
    }

    public function getName(): string
    {
        return $this->entityName . '.indexer';
    }

    public function iterate(/*?array */$offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->entityRepository->getDefinition(), $offset);

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

        $this->entityStockUpdater->update($ids, $event->getContext());

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
        $this->entityStockUpdater->update($ids, $context);

        $this->eventDispatcher->dispatch(new EntityStockIndexerEvent($this->entityName, $ids, $context));
    }
}
