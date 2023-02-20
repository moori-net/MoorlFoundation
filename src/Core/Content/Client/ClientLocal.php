<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

class ClientLocal extends ClientExtension implements ClientInterface
{
    protected string $clientName = "local";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'root', 'type' => 'text', 'required' => true, 'default' => '/home'],
        ];
    }

    public function getClientAdapter(): ?AdapterInterface
    {
        return new Local($this->clientEntity->getConfig()['root']);
    }
}
