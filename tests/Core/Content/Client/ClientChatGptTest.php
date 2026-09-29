<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Content\Client;

require_once dirname(__DIR__, 7) . '/vendor/autoload.php';
require_once dirname(__DIR__, 4) . '/src/Core/Service/ClientService.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Client/ClientEntity.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Client/ClientInterface.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Client/ClientExtension.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Client/ClientAiInterface.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Client/ClientChatGpt.php';

use MoorlFoundation\Core\Content\Client\ClientChatGpt;
use MoorlFoundation\Core\Content\Client\ClientEntity;
use MoorlFoundation\Core\Service\ClientService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientChatGptTest extends TestCase
{
    public function testProvidesOpenAiModelAndReasoningConfiguration(): void
    {
        $client = new ClientChatGpt();
        $configuration = $client->getClientConfigTemplate();

        self::assertSame('ai-chat-gpt', $client->getClientName());
        self::assertSame('Chat-GPT', $client->getClientLabel());
        self::assertSame(ClientService::TYPE_AI, $client->getClientType());
        self::assertSame(
            ['baseUri', 'apiKey', 'organizationId', 'projectId', 'model', 'reasoningEffort', 'store'],
            array_column($configuration, 'name')
        );
        self::assertSame('gpt-6-luna', $configuration[4]['default']);
        self::assertSame(['low', 'medium', 'high', 'xhigh', 'max'], array_column($configuration[5]['options'], 'value'));
        self::assertSame('custom', $configuration[6]['type']);
        self::assertSame('sw-single-select', $configuration[6]['componentName']);
        self::assertSame([
            ['value' => false, 'label' => 'Nein'],
            ['value' => true, 'label' => 'Ja'],
        ], $configuration[6]['options']);
        self::assertFalse($configuration[6]['default']);
    }

    public function testCreatesAResponsesClientWithConfiguredCredentialsAndReasoning(): void
    {
        $entity = new ClientEntity();
        $entity->setConfig([
            'baseUri' => 'https://api.openai.com/v1/',
            'apiKey' => 'sk-test',
            'organizationId' => 'org-test',
            'projectId' => 'proj-test',
            'model' => 'gpt-6-sol',
            'reasoningEffort' => 'high',
        ]);

        $client = new ClientChatGpt();
        $client->setClientEntity($entity);
        $httpClient = $client->getClient();

        self::assertSame('https://api.openai.com/v1/', (string) $httpClient->getConfig('base_uri'));
        self::assertSame('Bearer sk-test', $httpClient->getConfig('headers')['Authorization']);
        self::assertSame('org-test', $httpClient->getConfig('headers')['OpenAI-Organization']);
        self::assertSame('proj-test', $httpClient->getConfig('headers')['OpenAI-Project']);
        self::assertSame([
            'model' => 'gpt-6-sol',
            'reasoning' => ['effort' => 'high'],
            'store' => false,
        ], $client->getResponseOptions());
    }

    public function testCreatesAResponsesRequestWithDefaultStoreDisabled(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['output' => []], JSON_THROW_ON_ERROR)),
        ]));
        $handler->push(Middleware::history($history));

        $client = $this->createTestClient(new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'handler' => $handler,
        ]));
        $client->setClientEntity($this->createEntity());

        self::assertSame(['output' => []], $client->createResponse([
            ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hallo']]],
        ]));

        self::assertSame('POST', $history[0]['request']->getMethod());
        self::assertSame('/v1/responses', $history[0]['request']->getUri()->getPath());
        self::assertSame([
            'model' => 'gpt-6-sol',
            'reasoning' => ['effort' => 'high'],
            'store' => false,
            'input' => [['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hallo']]]],
        ], json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testCreatesAResponsesRequestWithConfiguredStoreEnabled(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['output' => []], JSON_THROW_ON_ERROR)),
        ]));
        $handler->push(Middleware::history($history));

        $client = $this->createTestClient(new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'handler' => $handler,
        ]));
        $entity = $this->createEntity();
        $entity->setConfig(array_merge($entity->getConfig(), ['store' => true]));
        $client->setClientEntity($entity);

        $client->createResponse([]);

        self::assertTrue(json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR)['store']);
    }

    public function testForwardsServiceGeneratedResponseOptions(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['output' => []], JSON_THROW_ON_ERROR)),
        ]));
        $handler->push(Middleware::history($history));

        $client = $this->createTestClient(new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'handler' => $handler,
        ]));
        $client->setClientEntity($this->createEntity());

        $client->createResponse([
            ['role' => 'developer', 'content' => [['type' => 'input_text', 'text' => 'Context']]],
            ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Correct this']]],
        ], [
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'moorl_ai_chat_response',
                    'strict' => true,
                    'schema' => ['type' => 'object'],
                ],
            ],
        ]);

        self::assertSame([
            'model' => 'gpt-6-sol',
            'reasoning' => ['effort' => 'high'],
            'store' => false,
            'input' => [
                ['role' => 'developer', 'content' => [['type' => 'input_text', 'text' => 'Context']]],
                ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Correct this']]],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'moorl_ai_chat_response',
                    'strict' => true,
                    'schema' => ['type' => 'object'],
                ],
            ],
        ], json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testTreatsStringFalseStoreConfigurationAsDisabled(): void
    {
        $entity = $this->createEntity();
        $entity->setConfig(array_merge($entity->getConfig(), ['store' => 'false']));

        $client = new ClientChatGpt();
        $client->setClientEntity($entity);

        self::assertFalse($client->getResponseOptions()['store']);
    }

    private function createEntity(): ClientEntity
    {
        $entity = new ClientEntity();
        $entity->setConfig([
            'baseUri' => 'https://api.openai.com/v1/',
            'apiKey' => 'sk-test',
            'organizationId' => 'org-test',
            'projectId' => 'proj-test',
            'model' => 'gpt-6-sol',
            'reasoningEffort' => 'high',
        ]);

        return $entity;
    }

    private function createTestClient(GuzzleClientInterface $httpClient): ClientChatGpt
    {
        return new class($httpClient) extends ClientChatGpt {
            public function __construct(private readonly GuzzleClientInterface $httpClient)
            {
            }

            public function getClient(): ?GuzzleClientInterface
            {
                return $this->httpClient;
            }
        };
    }
}
