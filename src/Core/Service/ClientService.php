<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\Client\ClientInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

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
}
