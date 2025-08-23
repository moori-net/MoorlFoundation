<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use Mautic\Auth\ApiAuth;
use MoorlFormsMautic\Api\Contacts;
use MoorlFoundation\Core\Service\ClientService;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class ClientMautic extends ClientExtension implements ClientInterface
{
    protected string $clientName = "api-mautic";
    protected string $clientType = ClientService::TYPE_API;

    public function getClientConfigTemplate(): ?array
    {
        return [
            [
                'name' => 'baseUrl',
                'type' => 'url',
                'required' => true,
                'placeholder' => 'https://your-mautic-server.org',
                'helpText' => 'Sanitize this URL. Make sure it starts with http/https and does not end with "/"'
            ],
            [
                'name' => 'userName',
                'type' => 'text',
                'required' => true,
                'default' => ''
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'required' => true,
                'default' => ''
            ]
        ];
    }

    public function testConnection(): array
    {
        if (!class_exists(ApiAuth::class)) {
            throw new MissingRequirementException('mautic/api-library', '*');
        }

        $config = $this->clientEntity->getConfig();

        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($config, 'BasicAuth');
        $contactApi = new Contacts($auth, sprintf('%s/api/', $config['baseUrl']));

        return $contactApi->getList();
    }
}
