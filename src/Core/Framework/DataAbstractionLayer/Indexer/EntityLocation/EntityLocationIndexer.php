<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityLocation;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Service\LocationServiceV2;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityLocationIndexer extends EntityIndexer
{
    protected IteratorFactory $iteratorFactory;
    protected Connection $connection;
    protected EntityRepositoryInterface $repository;
    protected EventDispatcherInterface $eventDispatcher;
    protected LocationServiceV2 $locationServiceV2;

    protected string $entityName;

    public function __construct(
        Connection $connection,
        IteratorFactory $iteratorFactory,
        EntityRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        LocationServiceV2 $locationServiceV2
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->locationServiceV2 = $locationServiceV2;

        $this->entityName = $repository->getDefinition()->getEntityName();
    }

    public function getName(): string
    {
        return $this->entityName . '.indexer';
    }

    public function iterate(/*?array */$offset): ?EntityLocationIndexingMessage
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

        $sql = 'SELECT 
LOWER(HEX(#entity#.id)) AS id,
LOWER(HEX(#entity#.country_id)) AS countryId,
#entity#.auto_location AS autoLocation,
#entity#.street AS street,
#entity#.street_number AS streetNumber,
#entity#.zipcode AS zipcode,
#entity#.city AS city,
#entity#.country_code AS iso,
#entity#.location_lat AS lat,
#entity#.location_lon AS lon
FROM #entity#
WHERE #entity#.id IN (:ids);';

        $sql = str_replace(
            ['#entity#'],
            [$this->entityName],
            $sql
        );

        $data = $this->connection->fetchAll(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($data as $item) {
            if (!$item['countryId'] && $item['iso']) {
                try {
                    $country = $this->locationServiceV2->getCountryByIso($item['iso']);
                    if ($country) {
                        $sql = 'UPDATE #entity# SET country_id = :country_id WHERE id = :id;';
                        $sql = str_replace(
                            ['#entity#'],
                            [$this->entityName],
                            $sql
                        );
                        $this->connection->executeUpdate(
                            $sql,
                            [
                                'id' => Uuid::fromHexToBytes($item['id']),
                                'country_id' => Uuid::fromHexToBytes($country->getId())
                            ]
                        );
                    }
                } catch (\Exception $exception) {}
            }

            if ($item['autoLocation'] === "1") {
                $location = $this->locationServiceV2->getLocationByAddress($item);
                if (!$location) {
                    continue;
                }

                $sql = 'UPDATE #entity# SET location_lat = :lat, location_lon = :lon WHERE id = :id;';
                $sql = str_replace(
                    ['#entity#'],
                    [$this->entityName],
                    $sql
                );
                $this->connection->executeUpdate(
                    $sql,
                    [
                        'id' => Uuid::fromHexToBytes($item['id']),
                        'lat' => $location->getLocationLat(),
                        'lon' => $location->getLocationLon()
                    ]
                );
            }
        }

        $context = Context::createDefaultContext();

        $this->eventDispatcher->dispatch(new EntityLocationIndexerEvent($ids, $this->entityName, $context));
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
