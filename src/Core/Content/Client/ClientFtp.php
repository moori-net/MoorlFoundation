<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AdapterInterface;

class ClientFtp extends ClientExtension implements ClientInterface
{
    protected string $clientName = "ftp";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'host', 'type' => 'text', 'required' => true, 'default' => 'localhost'],
            ['name' => 'port', 'type' => 'number', 'required' => true, 'default' => 21],
            ['name' => 'username', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
            ['name' => 'ssl', 'type' => 'switch', 'default' => false],
            ['name' => 'timeout', 'type' => 'number', 'default' => 90],
            ['name' => 'utf8', 'type' => 'switch', 'default' => false],
            ['name' => 'passive', 'type' => 'switch', 'default' => true],
        ];
    }

    public function getClientAdapter(): ?AdapterInterface
    {
        return new Ftp($this->clientEntity->getConfig());
    }
}
