<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class ClientNextcloud extends ClientExtension implements ClientInterface
{
    protected string $clientName = "nextcloud";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'baseUri', 'type' => 'text', 'required' => true, 'placeholder' => 'https://your-nextcloud-server.org/'],
            ['name' => 'userName', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        if (!class_exists(WebDAVAdapter::class)) {
            throw new MissingRequirementException('league/flysystem-webdav', '*');
        }

        $config = $this->clientEntity->getConfig();
        $client = new Client($config);
        return new WebDAVAdapter($client, sprintf('remote.php/dav/files/%s/', $config['userName']));
    }

    public function executePublicUrl(): bool
    {
        return false;
    }
}
