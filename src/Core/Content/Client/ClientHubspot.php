<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use MoorlFoundation\Core\Service\ClientService;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;
use HubSpot\Factory;

class ClientHubspot extends ClientExtension implements ClientInterface
{
    protected string $clientName = "hubspot";
    protected string $clientType = ClientService::TYPE_API;
    protected string $baseUrl = "https://api.hubapi.com";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'token', 'type' => 'password', 'required' => true, 'placeholder' => 'pat-eu1-********-****-****-****-************']
        ];
    }

    public function testConnection(): array
    {
        if (!class_exists(Factory::class)) {
            throw new MissingRequirementException('hubspot/api-client', '*');
        }

        $config = $this->clientEntity->getConfig();
        $hubspot = Factory::createWithAccessToken($config['token']);

        $response = $hubspot->apiRequest([
            'method' => 'GET',
            'path' => "/marketing/v3/forms/"
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
