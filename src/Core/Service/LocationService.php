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
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
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

    public function getLocationByTerm(?string $term = null): ?GeoPoint
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
            }

            preg_match('/([\d]{5})/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $zipcode = $matches[1];
            }

            preg_match('/(\w[\s\w]+?)\s*(\d+\s*[a-z]?)/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[0])) {
                $street = $matches[0];
            }

            preg_match('/^(^\D+)$/', $term, $matches, PREG_UNMATCHED_AS_NULL);
            if (!empty($matches[1])) {
                $city = $matches[1];
            }
        }

        return $this->getLocationByAddress([
            'street' => $street,
            'zipcode' => $zipcode,
            'city' => $city,
            'iso' => $iso
        ]);
    }

    public function getLocationByAddress(array $payload, $tries = 0, ?string $locationId = null): ?GeoPoint
    {
        $payload = array_merge([
            'street' => null,
            'streetNumber' => null,
            'zipcode' => null,
            'city' => null,
            'iso' => null
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
            $apiKey = $this->systemConfigService->get('AppflixDewaShop.config.googleMapsApiKey');

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

            $params = [
                "format" => "json",
                "zipcode" => $payload['zipcode'],
                "city" => $payload['city'],
                "street" => trim(sprintf(
                    '%s %s',
                    $payload['street'],
                    $payload['streetNumber']
                )),
                "country" => $payload['iso']
            ];

            $response = $this->apiRequest('GET', self::SEARCH_ENGINE, null, $params);

            if ($response && isset($response[0])) {
                $repo->upsert([[
                    'id' => $locationId,
                    'payload' => $payload,
                    'locationLat' => $response[0]['lat'],
                    'locationLon' => $response[0]['lon'],
                    'updatedAt' => $this->now->format(DATE_ATOM)
                ]], $this->context);

                return new GeoPoint($response[0]['lat'], $response[0]['lon']);
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
