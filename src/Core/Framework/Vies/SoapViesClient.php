<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Vies;

class SoapViesClient implements ViesClientInterface
{
    private \SoapClient $client;

    public function __construct(?int $timeoutSec = 4)
    {
        $ctx = stream_context_create(['http' => ['timeout' => $timeoutSec]]);
        $this->client = new \SoapClient(
            'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl',
            ['stream_context' => $ctx, 'exceptions' => true]
        );
    }

    public function checkVat(string $cc, string $num): bool
    {
        $res = $this->client->checkVat(['countryCode' => $cc, 'vatNumber' => $num]);
        return (bool)($res->valid ?? false);
    }
}
