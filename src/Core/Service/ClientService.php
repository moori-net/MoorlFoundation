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

    public function __construct(
        EntityRepositoryInterface $clientRepository,
        iterable $clients
    )
    {
        $this->clientRepository = $clientRepository;
        $this->clients = $clients;
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
        $criteria = new Criteria([$clientId]);

        /** @var ClientEntity $clientEntity */
        $clientEntity = $this->clientRepository->search($criteria, $context)->first();

        if (!$clientEntity) {
            throw new \Exception('Client entity not found');
        }

        foreach ($this->clients as $client) {
            if ($clientEntity->getType() === $client->getClientName()) {
                $client->setClientEntity($clientEntity);

                return $client;
            }
        }

        throw new \Exception('Client not found');
    }
}
