<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class ClientNextcloud extends ClientExtension implements ClientInterface
{
    protected string $clientName = "nextcloud";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'baseUri', 'type' => 'text', 'required' => true, 'placeholder' => 'http://your-nextcloud-server.org'],
            ['name' => 'userName', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        $config = $this->clientEntity->getConfig();
        $prefix = sprintf('remote.php/dav/files/%s/', $config['userName']);

        $client = new Client($config);
        return new WebDAVAdapter($client, $prefix);
    }
}
