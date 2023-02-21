<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use League\Flysystem\Filesystem;
use MoorlFoundation\Core\Content\Client\ClientEntity;
use MoorlFoundation\Core\Content\Client\ClientInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ClientService
{
    private EntityRepositoryInterface $clientRepository;
    /**
     * @var ClientInterface[]
     */
    private iterable $clients;
    private array $_clients = [];

    public function __construct(
        EntityRepositoryInterface $clientRepository,
        iterable $clients
    )
    {
        $this->clientRepository = $clientRepository;
        $this->clients = $clients;
    }

    public function getSize(string $clientId, string $path, Context $context): int
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->getSize($path);
    }

    public function readStream(string $clientId, string $path, Context $context)
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->readStream($path);
    }

    public function getMimetype(string $clientId, string $path, Context $context)
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->getMimetype($path);
    }

    public function read(string $clientId, string $path, Context $context)
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->read($path);
    }

    public function listContents(string $clientId, ?string $directory, Context $context): array
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->listContents($directory);
    }

    public function createDir(string $clientId, ?string $dirname, Context $context): void
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        $filesystem->createDir($dirname);
    }

    public function test(string $clientId, Context $context): array
    {
        $client = $this->getClient($clientId, $context);
        $filesystem = New Filesystem($client->getClientAdapter());
        return $filesystem->listContents();
    }
    public function getOptions(): array
    {
        $options = [];

        foreach ($this->clients as $client) {
            $options[] = [
                'name' => $client->getClientName(),
                'type' => $client->getClientType(),
                'configTemplate' => $client->getClientConfigTemplate(),
            ];
        }

        return $options;
    }

    private function getClient(string $clientId, Context $context): ClientInterface
    {
        if (isset($this->_clients[$clientId])) {
            return $this->_clients[$clientId];
        }

        $criteria = new Criteria([$clientId]);

        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->clientRepository->search($criteria, $context)->first();

        if (!$clientEntity) {
            throw new \Exception('Client entity not found');
        }

        foreach ($this->clients as $client) {
            if ($clientEntity->getType() === $client->getClientName()) {
                $client->setClientEntity($clientEntity);

                $this->_clients[$clientId] = $client;

                return $client;
            }
        }

        throw new \Exception('Client not found');
    }
}
