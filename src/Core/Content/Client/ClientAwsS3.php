<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class ClientAwsS3 extends ClientExtension implements ClientInterface
{
    protected string $clientName = "aws-s3";

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'username', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'password', 'type' => 'password', 'required' => true, 'default' => ''],
            ['name' => 'bucket', 'type' => 'text', 'required' => true, 'default' => ''],
            ['name' => 'prefix', 'type' => 'text', 'required' => true, 'default' => ''],
        ];
    }

    public function getClientAdapter(): ?AdapterInterface
    {
        $config = $this->clientEntity->getConfig();

        $client = new S3Client($config);

        return new AwsS3Adapter($client, $config['bucket'], $config['prefix']);
    }
}
