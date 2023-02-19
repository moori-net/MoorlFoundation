<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AdapterInterface;

class ClientLocal implements ClientInterface
{
    public function getClientType(): string
    {
        return "";
    }
    public function getClientName(): string
    {
        return "local";
    }
    public function getClientConfigTemplate(): ?array
    {
        return null;
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
