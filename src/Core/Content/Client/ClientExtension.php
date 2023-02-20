<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\AdapterInterface;

class ClientExtension
{
    protected ?ClientEntity $clientEntity = null;
    protected string $clientName = "";
    protected string $clientType = "";

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

    public function getClientAdapter(): ?AdapterInterface
    {
        return null;
    }

    public function getClient(): ?\GuzzleHttp\ClientInterface
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
}
