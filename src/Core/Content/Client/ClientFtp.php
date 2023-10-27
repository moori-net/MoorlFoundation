<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class ClientFtp extends ClientExtension implements ClientInterface
{
    protected string $clientName = "filesystem-ftp";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'host', 'type' => 'text', 'required' => true, 'default' => 'localhost'],
            ['name' => 'root', 'type' => 'text', 'required' => true, 'default' => '/'],
            ['name' => 'port', 'type' => 'number', 'required' => true, 'default' => 21],
            ['name' => 'username', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
            ['name' => 'ssl', 'type' => 'switch', 'default' => false],
            ['name' => 'timeout', 'type' => 'number', 'default' => 90],
            ['name' => 'utf8', 'type' => 'switch', 'default' => false],
            ['name' => 'passive', 'type' => 'switch', 'default' => true],
            ['name' => 'public_url', 'type' => 'text', 'placeholder' => 'https://example.org/assets/'],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        if (!class_exists(FtpAdapter::class)) {
            throw new MissingRequirementException('league/flysystem-ftp', '*');
        }

        return new FtpAdapter(FtpConnectionOptions::fromArray($this->clientEntity->getConfig()));
    }
}
