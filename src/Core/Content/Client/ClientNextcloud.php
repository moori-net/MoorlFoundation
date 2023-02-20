<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

class ClientNextcloud extends ClientExtension implements ClientInterface
{
    protected string $clientName = "nextcloud";

    public function getClientConfigTemplate(): ?array
    {
        return [
            [
                'name' => 'host',
                'type' => 'text',
                'required' => true,
                'default' => 'localhost',
            ],
            [
                'name' => 'port',
                'type' => 'number',
                'required' => true,
                'default' => '21',
            ],
            [
                'name' => 'username',
                'type' => 'text',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'ssl',
                'type' => 'bool',
                'default' => false,
            ]
        ];
    }
}
