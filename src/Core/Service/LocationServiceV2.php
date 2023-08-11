<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use MoorlFoundation\Core\Content\Location\LocationEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\Country\CountryCollection;
use Shopware\Core\System\Country\CountryDefinition;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class LocationServiceV2
{
    public const SEARCH_ENGINE = 'https://nominatim.openstreetmap.org/search';

    protected ?Context $context;
    protected DefinitionInstanceRegistry $definitionInstanceRegistry;
    protected SystemConfigService $systemConfigService;
    protected Connection $connection;
    protected ClientInterface $client;
    protected \DateTimeImmutable $now;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        Connection $connection
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->connection = $connection;

        $this->client = new Client([
            'timeout' => 200,
            'allow_redirects' => false,
        ]);

        $this->now = new \DateTimeImmutable();
        $this->context = Context::createDefaultContext();
    }

    public function writeLocationCache(
        LocationEntity $location,
        string $entityName,
        float $distance = 0.00,
        string $unit = "km"
    ): void
    {
        if ($this->now->modify("-1 hour") < $location->getUpdatedAt()) {
            //return;
        }

        $sql = <<<SQL
INSERT INTO `moorl_location_cache` (`location_id`, `entity_id`, `distance`, `created_at`, `updated_at`) 
SELECT
    UNHEX('%s'),
    `id`, 
    (6371 * acos(
        cos(radians(`location_lat`)) *
        cos(radians(%f)) *
        cos(radians(%f) - radians(`location_lon`)) +
        sin(radians(`location_lat`)) *
        sin(radians(%f))
    )) AS distance,
    `created_at`, 
    `updated_at`
FROM `%s`
ON DUPLICATE KEY UPDATE `moorl_location_cache`.`distance` = `distance`;
SQL;
        $this->connection->executeStatement(sprintf(
            $sql,
            $location->getId(),
            $location->getLocationLat(),
            $location->getLocationLon(),
            $location->getLocationLat(),
            $entityName
        ));
    }

    public function getUnitOfMeasurement(): string
    {
        return $this->systemConfigService->get('MoorlFoundation.config.osmUnitOfMeasurement') ?: 'mi';
    }

    public function getCountryByIso(?string $iso): ?CountryEntity
    {
        if (!$iso) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_OR,
            [
                new EqualsFilter('iso', $iso),
                new EqualsFilter('iso3', $iso),
                new EqualsFilter('name', $iso)
            ]
        ));
        $criteria->setLimit(1);

        $countryRepository = $this->definitionInstanceRegistry->getRepository('country');

        return $countryRepository->search($criteria, $this->context)->first();
    }

    public function getCustomerLocation(?CustomerEntity $customer = null): ?LocationEntity
    {
        if (!$customer) {
            return null;
        }

        $address = $customer->getActiveShippingAddress();

        return $this->getLocationByAddress([
            'street' => $address->getStreet(),
            'zipcode' => $address->getZipcode(),
            'city' => $address->getCity(),
            'iso' => $address->getCountry()->getIso()
        ]);
    }

    public function getLocationByAddress(
        array $payload,
        $tries = 0,
        ?string $locationId = null,
        array $countryIds = []
    ): ?LocationEntity
    {
        $payload = array_merge([
            'street' => null,
            'streetNumber' => null,
            'zipcode' => null,
            'city' => null,
            'iso' => null,
            'countryIds' => $countryIds
        ], $payload);

        if (!$locationId) {
            $locationId = md5(serialize($payload));
        }

        /* Check if location already exists */
        $repo = $this->definitionInstanceRegistry->getRepository('moorl_location');
        $criteria = new Criteria([$locationId]);
        $criteria->addFilter(new RangeFilter('updatedAt', [
            RangeFilter::GTE => ($this->now->modify("-1 hour"))->format(DATE_ATOM)
        ]));

        /** @var $location LocationEntity */
        $location = $repo->search($criteria, $this->context)->first();
        if ($location) {
            return $location;
        }

        if (!empty($payload['coords'])) {
            $coords = explode("|", $payload['coords']);

            $repo->upsert([[
                'id' => $locationId,
                'payload' => $payload,
                'locationLat' => (float) $coords[0],
                'locationLon' => (float) $coords[1],
                'updatedAt' => $this->now->format(DATE_ATOM)
            ]], $this->context);

            return $repo->search(new Criteria([$locationId]), $this->context)->get($locationId);
        }

        try {
            $countryIso = $this->getCountryIso($countryIds);

            $params = [
                "format" => "json",
                "postalcode" => $payload['zipcode'],
                "city" => $payload['city'],
                "street" => trim(sprintf(
                    '%s %s',
                    $payload['street'],
                    $payload['streetNumber']
                )),
                "countrycodes" => implode(",", $countryIso),
                "addressdetails" => 1
            ];

            $response = $this->apiRequest('GET', self::SEARCH_ENGINE, null, $params);

            if ($response && isset($response[0])) {
                $locationLat = $response[0]['lat'];
                $locationLon = $response[0]['lon'];

                /* Find best result by country filter */
                if (count($response) > 1) {
                    foreach ($response as $item) {
                        if (in_array(strtoupper($item['address']['country_code']), $countryIso)) {
                            $locationLat = $item['lat'];
                            $locationLon = $item['lon'];
                            break;
                        }
                    }
                }

                $repo->upsert([[
                    'id' => $locationId,
                    'payload' => $payload,
                    'locationLat' => $locationLat,
                    'locationLon' => $locationLon,
                    'updatedAt' => $this->now->format(DATE_ATOM)
                ]], $this->context);

                return $repo->search(new Criteria([$locationId]), $this->context)->get($locationId);
            } else {
                $tries++;

                switch ($tries) {
                    case 1:
                        $payload['iso'] = 'DE';
                        return $this->getLocationByAddress($payload, $tries, $locationId);
                    case 2:
                        $payload['iso'] = null;
                        return $this->getLocationByAddress($payload, $tries, $locationId);
                    case 3:
                        $payload['street'] = null;
                        $payload['streetNumber'] = null;
                        return $this->getLocationByAddress($payload, $tries, $locationId);
                    case 4:
                        $payload['zipcode'] = null;
                        return $this->getLocationByAddress($payload, $tries, $locationId);
                }

                return null;
            }
        } catch (\Exception $exception) {}

        return null;
    }

    private function getCountryPostalCodePatterns(array $countryIds): array
    {
        if (count($countryIds) === 0) {
            if ($this->systemConfigService->get('MoorlFoundation.config.osmCountryIds')) {
                $countryIds = $this->systemConfigService->get('MoorlFoundation.config.osmCountryIds');
            } else {
                return ['\d{5}','\d{4}'];
            }
        }

        $criteria = new Criteria($countryIds);
        $criteria->setLimit(count($countryIds));
        $countryRepository = $this->definitionInstanceRegistry->getRepository(CountryDefinition::ENTITY_NAME);

        /** @var CountryCollection $countries */
        $countries = $countryRepository->search($criteria, $this->context)->getEntities();

        return array_values($countries->fmap(fn(CountryEntity $entity) => $entity->getDefaultPostalCodePattern()));
    }

    private function getCountryIso(array $countryIds): array
    {
        if (count($countryIds) === 0) {
            if ($this->systemConfigService->get('MoorlFoundation.config.osmCountryIds')) {
                $countryIds = $this->systemConfigService->get('MoorlFoundation.config.osmCountryIds');
            } else {
                return ['DE','AT','CH'];
            }
        }

        $criteria = new Criteria($countryIds);
        $criteria->setLimit(count($countryIds));
        $countryRepository = $this->definitionInstanceRegistry->getRepository(CountryDefinition::ENTITY_NAME);

        /** @var CountryCollection $countries */
        $countries = $countryRepository->search($criteria, $this->context)->getEntities();

        return array_values($countries->fmap(function (CountryEntity $entity) {
            return $entity->getIso();
        }));
    }

    protected function apiRequest(string $method, ?string $endpoint = null, ?array $data = null, array $query = [])
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        $httpBody = json_encode($data);

        $query = \guzzlehttp\psr7\build_query($query);

        $request = new Request(
            $method,
            $endpoint . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );

        $response = $this->client->send($request);

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode > 299) {
            throw new \Exception(
                sprintf('[%d] Error connecting to the API (%s)', $statusCode, $request->getUri()),
                $statusCode
            );
        }

        $contents = $response->getBody()->getContents();

        try {
            return json_decode($contents, true);
        } catch (\Exception $exception) {
            throw new \Exception(
                sprintf('[%d] Error decoding JSON: %s', $statusCode, $contents),
                $statusCode
            );
        }
    }

    public function getLocationByTerm(?string $term = null, array $countryIds = []): ?LocationEntity
    {
        if (!$term) {
            return null;
        }

        $countryPostalCodePatterns = $this->getCountryPostalCodePatterns($countryIds);
        $terms = explode(',', $term);
        $iso = null;
        $zipcode = null;
        $street = null;
        $city = null;

        foreach ($terms as $term) {
            $term = trim($term);

            preg_match('/^(-?\d+(\.\d+)?)\|(-?\d+(\.\d+)?)$/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[0])) {
                return $this->getLocationByAddress([
                    'coords' => $matches[0]
                ]);
            }

            foreach ($countryPostalCodePatterns as $countryPostalCodePattern) {
                preg_match("/(^" . $countryPostalCodePattern . "$)/", $term, $matches, PREG_UNMATCHED_AS_NULL);
                if (!empty($matches[1])) {
                    $zipcode = $matches[1];
                    continue 2;
                }
            }

            preg_match('/([A-Z]{2})/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $iso = $matches[1];
                continue;
            }

            //preg_match('/(\w[\s\w]+?)\s*(\d+\s*[a-z]?)/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            preg_match('/(\w[\D\w]+?)\s*(\d+\s*[a-z]?)/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[0])) {
                $street = $matches[0];
                continue;
            }

            preg_match('/^(^\D+)$/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $city = $matches[1];
                continue;
            }
        }

        return $this->getLocationByAddress([
            'street' => $street,
            'zipcode' => $zipcode,
            'city' => $city,
            'iso' => $iso,
        ], 0, null, $countryIds);
    }

    /**
     * @return Context|null
     */
    public function getContext(): ?Context
    {
        return $this->context;
    }

    /**
     * @param Context|null $context
     */
    public function setContext(?Context $context): void
    {
        $this->context = $context;
    }
}
