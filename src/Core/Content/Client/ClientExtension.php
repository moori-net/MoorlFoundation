<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use GuzzleHttp\ClientInterface;
use League\Flysystem\FilesystemAdapter;
use MoorlFoundation\Core\Service\ClientService;

class ClientExtension
{
    protected ?ClientEntity $clientEntity = null;
    protected string $clientName = "";
    protected string $clientType = ClientService::TYPE_FILESYSTEM;

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'host', 'type' => 'text', 'required' => true, 'default' => 'localhost'],
            ['name' => 'port', 'type' => 'number', 'required' => true, 'default' => 21],
            ['name' => 'username', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => '']
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        return null;
    }

    public function getClient(): ?ClientInterface
    {
        return null;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function setClientEntity(ClientEntity $clientEntity): void
    {
        $this->clientEntity = $clientEntity;
    }

    public function getClientEntity(): ClientEntity
    {
        return $this->clientEntity;
    }

    public function executePublicUrl(): bool
    {
        return true;
    }

    public function testConnection(): array
    {
        return [];
    }

    public function prepareProviderInstance(array $options, string $redirectUri): array
    {
        $options['redirectUri'] = $redirectUri;

        if (empty($options['scopes'])) {
            unset($options['scopes']);
        } elseif (is_string($options['scopes'])) {
            $scope = array_map('trim', explode(",", $options['scopes']));
            $options['scope'] = $scope;
            $options['scopes'] = $scope;
        }

        foreach ($options as $k => $option) {
            if (!is_string($option)) {
                continue;
            }

            if (empty($option)) {unset($options[$k]);}
        }

        return $options;
    }
}
