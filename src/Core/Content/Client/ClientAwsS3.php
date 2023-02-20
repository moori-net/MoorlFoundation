<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

class ClientAwsS3 extends ClientExtension implements ClientInterface
{
    protected string $clientName = "aws-s3";
}
