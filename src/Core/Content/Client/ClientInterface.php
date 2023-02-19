<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\AdapterInterface;

interface ClientInterface
{
    public function getClientType(): string;
    public function getClientName(): string;
    public function getClientConfigTemplate(): ?array;
    public function getClientAdapter(ClientEntity $client): ?AdapterInterface;
    public function getClient(ClientEntity $client): ?\GuzzleHttp\ClientInterface;
}
