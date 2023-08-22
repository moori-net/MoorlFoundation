<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use AsyncAws\SimpleS3\SimpleS3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\FilesystemAdapter;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

class ClientAwsS3 extends ClientExtension implements ClientInterface
{
    protected string $clientName = "aws-s3";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'region', 'type' => 'text', 'required' => true, 'default' => 'eu-central-1'],
            ['name' => 'accessKeyId', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'accessKeySecret', 'type' => 'password', 'required' => true, 'default' => ''],
            ['name' => 'bucket', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'prefix', 'type' => 'text', 'required' => true, 'default' => ''],
        ];
    }

    public function getClientAdapter(): ?FilesystemAdapter
    {
        if (!class_exists(AsyncAwsS3Adapter::class)) {
            throw new MissingRequirementException('league/flysystem-async-aws-s3', '*');
        }

        $config = $this->clientEntity->getConfig();

        $clientConfig = $config;
        unset($clientConfig['bucket']);
        unset($clientConfig['prefix']);

        $client = new SimpleS3Client($clientConfig);

        return new AsyncAwsS3Adapter($client, $config['bucket'], $config['prefix']);
    }
}
