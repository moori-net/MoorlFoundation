<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

class ClientLocal extends ClientExtension implements ClientInterface
{
    protected string $clientName = "filesystem-local";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'root', 'type' => 'text', 'required' => true, 'default' => '/home'],
            ['name' => 'public_url', 'type' => 'text', 'placeholder' => 'https://example.org/assets/'],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        return new LocalFilesystemAdapter($this->clientEntity->getConfig()['root']);
    }
}
