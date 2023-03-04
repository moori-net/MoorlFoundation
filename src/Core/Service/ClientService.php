<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
use MoorlFoundation\Core\Content\Client\ClientEntity;
use MoorlFoundation\Core\Content\Client\ClientInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ClientService
{
    private array $_clients = [];

    /**
     * @param \MoorlFoundation\Core\Content\Client\ClientInterface[] $clients
     */
    public function __construct(
        private readonly EntityRepository $clientRepository,
        private readonly iterable $clients
    )
    {
    }

    public function publicUrl(string $clientId, string $path, Context $context): ?string
    {
        $filesystem = $this->getFilesystem($clientId, $context);
        if (method_exists($filesystem, 'publicUrl')) {
            return $filesystem->publicUrl($path);
        }
        return null;
    }

    public function temporaryUrl(string $clientId, string $path, $dateTimeOfExpiry, Context $context): ?string
    {
        $filesystem = $this->getFilesystem($clientId, $context);
        if (method_exists($filesystem, 'temporaryUrl')) {
            return $filesystem->temporaryUrl($path, $dateTimeOfExpiry);
        }
        return null;
    }

    public function getSize(string $clientId, string $path, Context $context): int
    {
        return $this->getFilesystem($clientId, $context)->fileSize($path);
    }

    public function readStream(string $clientId, string $path, Context $context)
    {
        return $this->getFilesystem($clientId, $context)->readStream($path);
    }

    public function getMimetype(string $clientId, string $path, Context $context)
    {
        return $this->getFilesystem($clientId, $context)->mimeType($path);
    }

    public function read(string $clientId, string $path, Context $context)
    {
        return $this->getFilesystem($clientId, $context)->read($path);
    }

    public function listContents(string $clientId, ?string $directory, Context $context): array
    {
        $listing = $this->getFilesystem($clientId, $context)->listContents($directory)->toArray();

        usort($listing, function (StorageAttributes $a, StorageAttributes $b) {
            return $a->type() <=> $b->type();
        });

        return $listing;
    }

    public function createDir(string $clientId, ?string $dirname, Context $context): void
    {
        $this->getFilesystem($clientId, $context)->createDirectory($dirname);
    }

    public function test(string $clientId, Context $context): array
    {
        return $this->getFilesystem($clientId, $context)->listContents("")->toArray();
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

    private function getFilesystem(string $clientId, Context $context): Filesystem
    {
        $client = $this->getClient($clientId, $context);
        return new Filesystem($client->getClientAdapter());
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
