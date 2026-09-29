<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Service;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
require_once dirname(__DIR__, 3) . '/src/Core/Content/Client/ClientEntity.php';
require_once dirname(__DIR__, 3) . '/src/Core/Content/Client/ClientInterface.php';
require_once dirname(__DIR__, 3) . '/src/Core/Content/Client/ClientAiInterface.php';
require_once dirname(__DIR__, 3) . '/src/Core/Content/Client/ClientExtension.php';
require_once dirname(__DIR__, 3) . '/src/Core/Content/Client/ClientChatGpt.php';
require_once dirname(__DIR__, 3) . '/src/Core/Service/ClientService.php';
require_once dirname(__DIR__, 3) . '/src/Core/Service/AiChatContextService.php';
require_once dirname(__DIR__, 3) . '/src/Core/Service/AiChatService.php';

use MoorlFoundation\Core\Content\Client\ClientChatGpt;
use MoorlFoundation\Core\Content\Client\ClientEntity;
use MoorlFoundation\Core\Content\Client\ClientInterface;
use MoorlFoundation\Core\Service\AiChatContextService;
use MoorlFoundation\Core\Service\AiChatService;
use MoorlFoundation\Core\Service\ClientService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class AiChatServiceTest extends TestCase
{
    public function testReturnsErrorWhenNoAiClientIsConfigured(): void
    {
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::once())
            ->method('get')
            ->with('MoorlFoundation.config.aiClient')
            ->willReturn(null);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::never())->method('getClient');

        self::assertSame(
            ['errors' => [['message' => 'No AI client is configured.']]],
            (new AiChatService($systemConfig, $clientService, $this->createContextService(), new NullLogger()))->test(Context::createDefaultContext())
        );
    }

    public function testDoesNotTestAnInactiveAiClient(): void
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setActive(false);

        $client = $this->createMock(ClientInterface::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_AI);
        $client->method('getClientEntity')->willReturn($clientEntity);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->with('client-id', self::isInstanceOf(Context::class))->willReturn($client);
        $clientService->expects(self::never())->method('test');

        self::assertSame(
            ['errors' => [['message' => 'Configured AI client is inactive.']]],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testReturnsErrorWhenTheConfiguredAiClientDoesNotExist(): void
    {
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->willThrowException(new \Exception('Client entity not found'));
        $clientService->expects(self::never())->method('test');

        self::assertSame(
            ['errors' => [['message' => 'Configured AI client could not be loaded.']]],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testDoesNotTestAClientOfAnotherCategory(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_API);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->willReturn($client);
        $clientService->expects(self::never())->method('test');

        self::assertSame(
            ['errors' => [['message' => 'Configured client is not an AI client.']]],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testDelegatesToTheConfiguredActiveAiClient(): void
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setActive(true);

        $client = $this->createMock(ClientInterface::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_AI);
        $client->method('getClientEntity')->willReturn($clientEntity);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->with('client-id', self::isInstanceOf(Context::class))->willReturn($client);
        $clientService->expects(self::once())->method('test')->with('client-id', self::isInstanceOf(Context::class))->willReturn(['id' => 'gpt-6-luna']);

        self::assertSame(
            ['success' => true],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testDoesNotExposeProviderErrorsOrApiKeys(): void
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setActive(true);

        $client = $this->createMock(ClientInterface::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_AI);
        $client->method('getClientEntity')->willReturn($clientEntity);

        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);
        $clientService->expects(self::once())->method('test')->willReturn([
            'errors' => [['message' => 'Provider rejected API key sk-test-secret']],
        ]);

        self::assertSame(
            ['errors' => [['message' => 'AI client connection test failed.']]],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testDoesNotExposeThrownProviderErrorsOrApiKeys(): void
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setActive(true);

        $client = $this->createMock(ClientInterface::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_AI);
        $client->method('getClientEntity')->willReturn($clientEntity);

        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);
        $clientService->expects(self::once())->method('test')
            ->willThrowException(new \RuntimeException('Provider rejected API key sk-test-secret'));

        self::assertSame(
            ['errors' => [['message' => 'AI client connection test failed.']]],
            $this->createService('client-id', $clientService)->test(Context::createDefaultContext())
        );
    }

    public function testRejectsInvalidChatMessages(): void
    {
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::never())->method('getClient');
        $service = $this->createService('client-id', $clientService);

        foreach ([
            [],
            array_fill(0, 31, ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]]),
            [['role' => 'system', 'content' => [['type' => 'text', 'text' => 'Hello']]]],
            [['role' => 'user', 'content' => [['type' => 'image', 'url' => 'https://example.test/image.png']]]],
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => '']]]],
            [['role' => 'user', 'content' => [['type' => 'text', 'text' => str_repeat('a', 10001)]]]],
            [['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => str_repeat('a', 6000)],
                ['type' => 'text', 'text' => str_repeat('b', 6000)],
            ]]],
        ] as $messages) {
            self::assertSame(
                ['errors' => [['message' => 'Chat messages are invalid.']]],
                $service->chat(array_merge([$this->chatContextMessage()], $messages), $this->chatItem(), Context::createDefaultContext())
            );
        }
    }

    public function testRejectsMissingChatContextBeforeResolvingAClient(): void
    {
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::never())->method('getClient');

        self::assertSame(
            ['errors' => [['message' => 'Chat context is invalid.']]],
            $this->createService('client-id', $clientService)->chat([
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    public function testCreatesAChatResponseFromNormalizedTextMessages(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->expects(self::once())
            ->method('createResponse')
            ->with(
                self::callback(static fn (array $messages): bool =>
                    count($messages) === 3
                    && $messages[0]['role'] === 'developer'
                    && $messages[0]['content'][0]['type'] === 'input_text'
                    && str_contains($messages[0]['content'][0]['text'], 'moorl_example')
                    && $messages[1] === ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Hello']]]
                    && $messages[2] === ['role' => 'assistant', 'content' => [['type' => 'output_text', 'text' => 'Hi']]]
                ),
                self::callback(static fn (array $options): bool =>
                    $options['text']['format']['type'] === 'json_schema'
                    && isset($options['text']['format']['schema']['properties']['changes']['items'])
                )
            )
            ->willReturn([
                'output' => [[
                    'type' => 'message',
                    'content' => [
                        ['type' => 'output_text', 'text' => '{"message":"Answer continued","changes":[{"field":"title","value":"Corrected"}]}'],
                    ],
                ]],
            ]);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->with('client-id', self::isInstanceOf(Context::class))->willReturn($client);

        self::assertSame(
            ['message' => 'Answer continued', 'changes' => ['title' => 'Corrected']],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
                ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Hi']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    public function testAddsAValidatedImageToTheFirstUserMessage(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->expects(self::once())
            ->method('createResponse')
            ->with(
                self::callback(static fn (array $messages): bool =>
                    $messages[1] === [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => 'Describe this article image.'],
                            [
                                'type' => 'input_image',
                                'image_url' => 'https://shop.example.test/media/article-cover.jpg',
                                'detail' => 'auto',
                            ],
                        ],
                    ]
                ),
                self::isType('array')
            )
            ->willReturn([
                'output' => [[
                    'type' => 'message',
                    'content' => [
                        ['type' => 'output_text', 'text' => '{"message":"I can see the image.","changes":[]}'],
                    ],
                ]],
            ]);

        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::once())->method('getClient')->willReturn($client);

        self::assertSame(
            ['message' => 'I can see the image.'],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Describe this article image.']]],
            ], $this->chatItem(), Context::createDefaultContext(), [
                ['url' => 'https://shop.example.test/media/article-cover.jpg'],
            ])
        );
    }

    public function testRejectsNonHttpsImageInput(): void
    {
        $clientService = $this->createMock(ClientService::class);
        $clientService->expects(self::never())->method('getClient');

        self::assertSame(
            ['errors' => [['message' => 'Chat images are invalid.']]],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Describe this article image.']]],
            ], $this->chatItem(), Context::createDefaultContext(), [
                ['url' => 'http://shop.example.test/media/article-cover.jpg'],
            ])
        );
    }

    public function testRejectsPatchesWithUnknownOrNestedFields(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->method('createResponse')->willReturn([
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => '{"message":"Done","changes":[{"field":"customFields","value":"invalid"}]}',
                ]],
            ]],
        ]);
        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);

        self::assertSame(
            ['errors' => [['message' => 'AI chat request failed.']]],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Change custom fields']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    public function testDoesNotExposeProviderChatErrorsOrApiKeys(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->method('createResponse')->willThrowException(new \RuntimeException('Provider rejected sk-test-secret'));

        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);

        self::assertSame(
            ['errors' => [['message' => 'AI chat request failed.']]],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    public function testLogsRedactedProviderChatErrors(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->method('createResponse')->willThrowException(new \RuntimeException('Provider rejected API key sk-test-secret'));

        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                'AI chat request failed.',
                self::callback(static fn (array $context): bool =>
                    $context['clientId'] === 'client-id'
                    && $context['exceptionClass'] === \RuntimeException::class
                    && !str_contains(json_encode($context, JSON_THROW_ON_ERROR), 'sk-test-secret')
                )
            );

        self::assertSame(
            ['errors' => [['message' => 'AI chat request failed.']]],
            $this->createService('client-id', $clientService, $logger)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    public function testReturnsControlledErrorForMalformedProviderChatResponse(): void
    {
        $client = $this->createActiveChatGptClient();
        $client->method('createResponse')->willReturn(['output' => 'invalid']);

        $clientService = $this->createMock(ClientService::class);
        $clientService->method('getClient')->willReturn($client);

        self::assertSame(
            ['errors' => [['message' => 'AI chat request failed.']]],
            $this->createService('client-id', $clientService)->chat([
                $this->chatContextMessage(),
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
            ], $this->chatItem(), Context::createDefaultContext())
        );
    }

    private function createActiveChatGptClient(): ClientChatGpt
    {
        $clientEntity = new ClientEntity();
        $clientEntity->setActive(true);

        $client = $this->createMock(ClientChatGpt::class);
        $client->method('getClientType')->willReturn(ClientService::TYPE_AI);
        $client->method('getClientEntity')->willReturn($clientEntity);

        return $client;
    }

    private function chatContextMessage(): array
    {
        return [
            'role' => 'context',
            'content' => [[
                'type' => 'entity_context',
                'context' => [
                    'entity' => 'moorl_example',
                    'labelProperty' => 'title',
                    'fields' => [
                        'title' => ['type' => 'string', 'required' => true],
                    ],
                ],
            ]],
        ];
    }

    private function chatItem(): array
    {
        return ['id' => 'item-id', 'title' => 'Draft'];
    }

    private function createService(?string $clientId, ClientService $clientService, ?LoggerInterface $logger = null): AiChatService
    {
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->method('get')->with('MoorlFoundation.config.aiClient')->willReturn($clientId);

        return new AiChatService($systemConfig, $clientService, $this->createContextService(), $logger ?? new NullLogger());
    }

    private function createContextService(): AiChatContextService
    {
        return new class extends AiChatContextService {
            public function __construct()
            {
            }

            public function build(array $context, array $item): ?array
            {
                if ($context === [] || $item === []) {
                    return null;
                }

                return [
                    'instructions' => 'Entity context: moorl_example',
                    'allowedFields' => ['title' => 'string'],
                    'requestOptions' => [
                        'text' => [
                            'format' => [
                                'type' => 'json_schema',
                                'schema' => [
                                    'properties' => [
                                        'changes' => ['items' => []],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }
        };
    }
}
