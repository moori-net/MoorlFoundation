<?php

namespace MoorlFoundation\Core\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use MoorlFoundation\Core\Content\Location\LocationEntity;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class LocationService
{
    public const SEARCH_ENGINE = 'https://nominatim.openstreetmap.org/search';

    private ?Context $context;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    protected ClientInterface $client;
    protected \DateTimeImmutable $now;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;

        $this->client = new Client([
            'timeout' => 200,
            'allow_redirects' => false,
        ]);

        $this->now = new \DateTimeImmutable();
        $this->context = Context::createDefaultContext();
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

    public function getCustomerLocation(?CustomerEntity $customer = null): ?GeoPoint
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

    public function getLocationByTerm(?string $term = null, array $countryIds = []): ?GeoPoint
    {
        if (!$term) {
            return null;
        }

        $terms = explode(',', $term);
        $iso = null;
        $zipcode = null;
        $street = null;
        $city = null;

        foreach ($terms as $term) {
            $term = trim($term);

            preg_match('/([A-Z]{2})/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $iso = $matches[1];
                continue;
            }

            preg_match('/([\d]{5})/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $zipcode = $matches[1];
                continue;
            }

            preg_match('/(\w[\s\w]+?)\s*(\d+\s*[a-z]?)/', $term, $matches, PREG_UNMATCHED_AS_NULL);
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
            'iso' => $iso
        ], 0, null, $countryIds);
    }

    public function getLocationByAddress(
        array $payload,
        $tries = 0,
        ?string $locationId = null,
        array $countryIds = []
    ): ?GeoPoint
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
            return new GeoPoint($location->getLocationLat(), $location->getLocationLon());
        }

        try {
            $apiKey = $this->systemConfigService->get('MoorlFoundation.config.googleMapsApiKey');

            if ($apiKey) {
                $address = sprintf('%s %s, %s %s, %s',
                    $payload['street'],
                    $payload['streetNumber'],
                    $payload['zipcode'],
                    $payload['city'],
                    $payload['iso']
                );

                return GeoPoint::fromAddress($address, $apiKey);
            }

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

                return new GeoPoint($locationLat, $locationLon);
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
}
