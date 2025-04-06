<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use HubSpot\Factory;
use MoorlFoundation\Core\Service\ClientService;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class ClientHubspot extends ClientExtension implements ClientInterface
{
    protected string $clientName = "api-hubspot";
    protected string $clientType = ClientService::TYPE_API;

    public function getClientConfigTemplate(): ?array
    {
        return [
            [
                'name' => 'token',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'pat-eu1-********-****-****-****-************',
                'helpText' => 'Please enable this Hubspot protocols: crm.objects.contacts, crm.schemas.contacts, forms, forms-uploaded-files'
            ],
            [
                'name' => 'portalId',
                'type' => 'text',
                'required' => true,
                'placeholder' => '*********',
                'helpText' => 'The HubSpot account number'
            ]
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
            'path' => "/crm/v3/objects/contacts"
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
