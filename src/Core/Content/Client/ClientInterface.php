<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;

interface ClientInterface
{
    public function getClientType(): string;
    public function getClientName(): string;
    public function getClientConfigTemplate(): ?array;
    public function getClientAdapter(): ?FilesystemAdapter;
    public function getClient(): ?\GuzzleHttp\ClientInterface;
    public function setClientEntity(ClientEntity $clientEntity): void;
    public function getClientEntity(): ClientEntity;
}
