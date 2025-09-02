<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityAutoLocationService implements EventSubscriberInterface
{
    public const TEST_AGAINST = [
        'street',
        'streetNumber',
        'zipcode',
        'city',
        'countryCode',
        'countryId',
        'autoLocation'
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly LocationServiceV2 $locationServiceV2,
        private readonly iterable $entityDefinitions
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'onEntityWrittenContainerEvent'
        ];
    }

    public function onEntityWrittenContainerEvent(EntityWrittenContainerEvent $event): void
    {
        foreach ($this->entityDefinitions as $entityDefinition) {
            $entityEvent = $event->getEventByEntityName($entityDefinition->getEntityName());
            if (!$entityEvent) {
                continue;
            }

            $ids = [];
            foreach ($entityEvent->getWriteResults() as $writeResult) {
                if (!$this->testPayload($writeResult->getPayload())) {
                    continue;
                }

                $ids[] = $writeResult->getPrimaryKey();
            }

            $this->handle($entityDefinition->getEntityName(), $ids);
        }
    }

    private function testPayload(array $payload): bool
    {
        foreach (self::TEST_AGAINST as $test) {
            if (isset($payload[$test])) {
                return true;
            }
        }

        return false;
    }

    private function handle(string $entityName, array $ids): void
    {
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
#entity#.country_code AS countryCode,
#entity#.location_lat AS lat,
#entity#.location_lon AS lon
FROM #entity#
WHERE #entity#.id IN (:ids);';

        $sql = str_replace(['#entity#'], [$entityName], $sql);

        $data = $this->connection->fetchAllAssociative(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );

        foreach ($data as $item) {
            if (!$item['countryId'] && $item['countryCode']) {
                try {
                    $country = $this->locationServiceV2->getCountryByIso($item['countryCode']);
                    if ($country) {
                        $sql = 'UPDATE #entity# SET country_id = :country_id WHERE id = :id;';
                        $sql = str_replace(['#entity#'], [$entityName], $sql);
                        $this->connection->executeStatement(
                            $sql,
                            [
                                'id' => Uuid::fromHexToBytes($item['id']),
                                'country_id' => Uuid::fromHexToBytes($country->getId())
                            ]
                        );

                        $item['countryId'] = $country->getId();
                    }
                } catch (\Exception) {}
            }

            if ($item['autoLocation'] === "1") {
                $location = $this->locationServiceV2->getLocationByAddress($item);
                if (!$location) {
                    continue;
                }

                $sql = 'UPDATE #entity# SET location_lat = :lat, location_lon = :lon WHERE id = :id;';
                $sql = str_replace(['#entity#'], [$entityName], $sql);
                $this->connection->executeStatement(
                    $sql,
                    [
                        'id' => Uuid::fromHexToBytes($item['id']),
                        'lat' => $location->getLocationLat(),
                        'lon' => $location->getLocationLon()
                    ]
                );
            }
        }
    }
}
