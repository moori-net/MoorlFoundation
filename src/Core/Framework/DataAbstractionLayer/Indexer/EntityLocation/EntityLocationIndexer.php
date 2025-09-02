<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityLocation;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\LocationServiceV2;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @deprecated: Use moorl.foundation.entity_auto_location tag instead
 */
class EntityLocationIndexer extends EntityIndexer
{
    protected string $entityName = "";

    public function __construct(
        protected Connection $connection,
        protected IteratorFactory $iteratorFactory,
        protected EntityRepository $repository,
        protected EventDispatcherInterface $eventDispatcher,
        protected LocationServiceV2 $locationServiceV2
    ) {
        $this->entityName = $repository->getDefinition()->getEntityName();
    }

    public function getName(): string
    {
        return $this->entityName . '.indexer';
    }

    public function iterate(?array $offset): ?EntityLocationIndexingMessage
    {
        $iterator = $this->getIterator($offset);

        $ids = $iterator->fetch();
        if (empty($ids)) {
            return null;
        }

        return new EntityLocationIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityLocationIndexingMessage
    {
        $ids = $event->getPrimaryKeys($this->entityName);
        if (empty($ids)) {
            return null;
        }

        return new EntityLocationIndexingMessage(array_values($ids), null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->eventDispatcher->dispatch(new EntityLocationIndexerEvent($ids, $this->entityName, $message->getContext()));
    }

    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }
}
