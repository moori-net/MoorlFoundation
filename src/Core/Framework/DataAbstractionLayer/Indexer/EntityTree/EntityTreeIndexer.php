<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityTree;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityBreadcrumb\EntityBreadcrumbUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\ChildCountUpdater;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\TreeUpdater;
use Shopware\Core\Framework\Uuid\Uuid;

class EntityTreeIndexer extends EntityIndexer
{
    protected IteratorFactory $iteratorFactory;
    protected Connection $connection;
    protected EntityRepository $repository;
    protected ?ChildCountUpdater $childCountUpdater;
    protected ?TreeUpdater $treeUpdater;
    protected ?EntityBreadcrumbUpdater $breadcrumbUpdater;

    protected string $entityName;

    public function __construct(
        Connection $connection,
        IteratorFactory $iteratorFactory,
        EntityRepository $repository,
        ?ChildCountUpdater $childCountUpdater = null,
        ?TreeUpdater $treeUpdater = null,
        ?EntityBreadcrumbUpdater $breadcrumbUpdater = null
    ) {
        $this->connection = $connection;
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->childCountUpdater = $childCountUpdater;
        $this->treeUpdater = $treeUpdater;
        $this->breadcrumbUpdater = $breadcrumbUpdater;

        $this->entityName = $repository->getDefinition()->getEntityName();
    }

    public function getName(): string
    {
        return $this->entityName . '.indexer';
    }

    public function iterate(/*?array */$offset): ?EntityIndexingMessage
    {
        $iterator = $this->getIterator($offset);

        $ids = $iterator->fetch();
        if (empty($ids)) {
            return null;
        }

        return new EntityTreeIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $elementEvent = $event->getEventByEntityName($this->entityName);
        if (!$elementEvent) {
            return null;
        }

        $ids = $elementEvent->getIds();
        $idsWithChangedParentIds = [];
        foreach ($elementEvent->getWriteResults() as $result) {
            if (!$result->getExistence()) {
                continue;
            }
            $state = $result->getExistence()->getState();

            if (isset($state['parent_id'])) {
                $ids[] = Uuid::fromBytesToHex($state['parent_id']);
            }

            $payload = $result->getPayload();
            if (\array_key_exists('parentId', $payload)) {
                if ($payload['parentId'] !== null) {
                    $ids[] = $payload['parentId'];
                }
                $idsWithChangedParentIds[] = $payload['id'];
            }
        }

        if (empty($ids)) {
            return null;
        }

        if ($idsWithChangedParentIds !== []) {
            $this->treeUpdater->batchUpdate(
                $idsWithChangedParentIds,
                $this->entityName,
                $event->getContext()
            );
        }

        $children = $this->fetchChildren($ids, $event->getContext()->getVersionId());

        $ids = array_unique(array_merge($ids, $children));

        return new EntityTreeIndexingMessage(array_values($ids), null, $event->getContext(), \count($ids) > 20);
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $context = $message->getContext();

        RetryableTransaction::retryable($this->connection, function () use ($message, $ids, $context): void {
            if ($this->childCountUpdater && $message->allow($this->entityName . '.child-count')) {
                $this->childCountUpdater->update($this->entityName, $ids, $context);
            }

            if ($this->treeUpdater && $message->allow($this->entityName . '.tree')) {
                $this->treeUpdater->batchUpdate($ids, $this->entityName, $context);
            }

            if ($this->breadcrumbUpdater && $message->allow($this->entityName . '.breadcrumb')) {
                $this->breadcrumbUpdater->update($ids, $this->entityName, $context);
            }
        });
    }

    public function getOptions(): array
    {
        return [
            $this->entityName . '.child-count',
            $this->entityName . '.tree',
            $this->entityName . '.breadcrumb',
        ];
    }

    private function fetchChildren(array $elementIds, string $versionId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(sprintf('DISTINCT LOWER(HEX(%s.id))', $this->entityName));
        $query->from($this->entityName);

        $wheres = [];
        foreach ($elementIds as $id) {
            $key = 'path' . $id;
            $wheres[] = $this->entityName . '.path LIKE :' . $key;
            $query->setParameter($key, '%|' . $id . '|%');
        }

        $query->andWhere('(' . implode(' OR ', $wheres) . ')');
        
        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getIterator(?array $offset): IterableQuery
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);
    }

    public function getTotal(): int
    {
        return $this->getIterator(null)->fetchCount();
    }
}
