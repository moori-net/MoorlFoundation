<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use MoorlFoundation\Core\Service\ClientService;

class ClientChatGpt extends ClientExtension implements ClientInterface, ClientAiInterface
{
    public const DEFAULT_MODEL = 'gpt-6-luna';
    public const DEFAULT_REASONING_EFFORT = 'medium';

    protected string $clientName = 'ai-chat-gpt';
    protected string $clientType = ClientService::TYPE_AI;

    public function getClientLabel(): string
    {
        return 'Chat-GPT';
    }

    public function getClientConfigTemplate(): ?array
    {
        return [
            ['name' => 'baseUri', 'type' => 'url', 'required' => true, 'default' => 'https://api.openai.com/v1/'],
            ['name' => 'apiKey', 'type' => 'password', 'required' => true, 'default' => ''],
            ['name' => 'organizationId', 'type' => 'text', 'required' => false, 'default' => ''],
            ['name' => 'projectId', 'type' => 'text', 'required' => false, 'default' => ''],
            [
                'name' => 'model',
                'type' => 'custom',
                'componentName' => 'sw-single-select',
                'options' => [
                    ['value' => 'gpt-6-astra', 'label' => 'GPT-6 Astra'],
                    ['value' => 'gpt-6-sol', 'label' => 'GPT-6 Sol'],
                    ['value' => 'gpt-6-luna', 'label' => 'GPT-6 Luna'],
                ],
                'required' => true,
                'default' => self::DEFAULT_MODEL,
            ],
            [
                'name' => 'reasoningEffort',
                'type' => 'custom',
                'componentName' => 'sw-single-select',
                'options' => [
                    ['value' => 'low', 'label' => 'Low'],
                    ['value' => 'medium', 'label' => 'Medium'],
                    ['value' => 'high', 'label' => 'High'],
                    ['value' => 'xhigh', 'label' => 'Extra high'],
                    ['value' => 'max', 'label' => 'Maximum'],
                ],
                'required' => true,
                'default' => self::DEFAULT_REASONING_EFFORT,
            ],
            [
                'name' => 'store',
                'type' => 'custom',
                'componentName' => 'sw-single-select',
                'options' => [
                    ['value' => false, 'label' => 'Nein'],
                    ['value' => true, 'label' => 'Ja'],
                ],
                'required' => false,
                'default' => false,
            ],
        ];
    }

    public function getClient(): ?GuzzleClientInterface
    {
        $config = $this->clientEntity->getConfig();
        $headers = [
            'Authorization' => 'Bearer ' . $config['apiKey'],
            'Content-Type' => 'application/json',
        ];

        if (!empty($config['organizationId'])) {
            $headers['OpenAI-Organization'] = $config['organizationId'];
        }

        if (!empty($config['projectId'])) {
            $headers['OpenAI-Project'] = $config['projectId'];
        }

        return new Client([
            'base_uri' => $config['baseUri'] ?? 'https://api.openai.com/v1/',
            'headers' => $headers,
        ]);
    }

    public function getResponseOptions(): array
    {
        $config = $this->clientEntity->getConfig();

        return [
            'model' => $config['model'] ?? self::DEFAULT_MODEL,
            'reasoning' => [
                'effort' => $config['reasoningEffort'] ?? self::DEFAULT_REASONING_EFFORT,
            ],
            'store' => filter_var($config['store'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function createResponse(array $messages, array $requestOptions = []): array
    {
        $response = $this->getClient()->post('responses', [
            'json' => array_merge($this->getResponseOptions(), ['input' => $messages], $requestOptions),
        ]);

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }

    public function testConnection(): array
    {
        $response = $this->getClient()->get('models/' . rawurlencode($this->getResponseOptions()['model']));

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }
}
