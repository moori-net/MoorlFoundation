<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AdapterInterface;

class ClientFtp implements ClientInterface
{
    public function getClientType(): string
    {
        return "";
    }
    public function getClientName(): string
    {
        return "ftp";
    }
    public function getClientConfigTemplate(): ?array
    {
        return [
            [
                'name' => 'host',
                'type' => 'text',
                'required' => true,
                'default' => 'localhost',
            ],
            [
                'name' => 'port',
                'type' => 'number',
                'required' => true,
                'default' => '21',
            ],
            [
                'name' => 'username',
                'type' => 'text',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'ssl',
                'type' => 'bool',
                'default' => false,
            ]
        ];
    }

    public function getClientAdapter(ClientEntity $client): ?AdapterInterface
    {
        return new Ftp($client->getConfig());
    }

    public function getClient(ClientEntity $client): ?\GuzzleHttp\ClientInterface
    {
        return null;
    }
}
