<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use AsyncAws\S3\S3Client;
use AsyncAws\SimpleS3\SimpleS3Client;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\FilesystemAdapter;

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
        $config = $this->clientEntity->getConfig();

        $clientConfig = $config;
        unset($clientConfig['bucket']);
        unset($clientConfig['prefix']);

        $client = new SimpleS3Client($clientConfig);

        return new AsyncAwsS3Adapter($client, $config['bucket'], $config['prefix']);
    }
}
