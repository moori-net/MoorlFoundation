<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Administration\Controller;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
require_once dirname(__DIR__, 3) . '/src/Core/Service/AiChatService.php';
require_once dirname(__DIR__, 3) . '/src/Administration/Controller/AiChatController.php';

use MoorlFoundation\Administration\Controller\AiChatController;
use MoorlFoundation\Core\Service\AiChatService;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AiChatControllerTest extends TestCase
{
    public function testReturnsTheAiClientTestResultWithoutAcceptingAClientId(): void
    {
        $service = $this->createMock(AiChatService::class);
        $service->expects(self::once())
            ->method('test')
            ->with(self::isInstanceOf(Context::class))
            ->willReturn(['success' => true]);

        $controller = new AiChatController($service);
        $response = $controller->test(Context::createDefaultContext());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(['success' => true], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        self::assertSame(1, (new \ReflectionMethod(AiChatController::class, 'test'))->getNumberOfParameters());
    }

    public function testReturnsChatResponseForDecodedContextMessagesAndItem(): void
    {
        $messages = [
            ['role' => 'context', 'content' => [['type' => 'entity_context', 'context' => ['entity' => 'product']]]],
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Hello']]],
        ];
        $item = ['id' => 'item-id', 'name' => 'Draft'];
        $images = [['url' => 'https://shop.example.test/media/article-cover.jpg']];
        $service = $this->createMock(AiChatService::class);
        $service->expects(self::once())
            ->method('chat')
            ->with($messages, $item, self::isInstanceOf(Context::class), $images)
            ->willReturn(['message' => 'Answer']);

        $request = Request::create(
            '/api/moorl-foundation/ai/chat',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['messages' => $messages, 'item' => $item, 'images' => $images, 'entity' => 'product'], JSON_THROW_ON_ERROR)
        );

        $response = (new AiChatController($service))->chat($request, Context::createDefaultContext());

        self::assertSame(['message' => 'Answer'], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        self::assertSame(2, (new \ReflectionMethod(AiChatController::class, 'chat'))->getNumberOfParameters());
    }

    public function testPassesAnEmptyMessageListForMalformedJson(): void
    {
        $service = $this->createMock(AiChatService::class);
        $service->expects(self::once())
            ->method('chat')
            ->with([], [], self::isInstanceOf(Context::class), [])
            ->willReturn(['errors' => [['message' => 'Chat messages are invalid.']]]);

        $response = (new AiChatController($service))->chat(
            Request::create('/api/moorl-foundation/ai/chat', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{'),
            Context::createDefaultContext()
        );

        self::assertSame(['errors' => [['message' => 'Chat messages are invalid.']]], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
