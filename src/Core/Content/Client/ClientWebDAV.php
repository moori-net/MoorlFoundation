<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class ClientWebDAV extends ClientExtension implements ClientInterface
{
    protected string $clientName = "webdav";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'baseUri', 'type' => 'text', 'required' => true, 'placeholder' => 'http://your-webdav-server.org/'],
            ['name' => 'userName', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        $client = new Client($this->clientEntity->getConfig());
        return new WebDAVAdapter($client);
    }
}
