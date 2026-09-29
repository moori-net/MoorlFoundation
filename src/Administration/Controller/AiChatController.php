<?php declare(strict_types=1);

namespace MoorlFoundation\Administration\Controller;

use MoorlFoundation\Core\Service\AiChatService;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AiChatController
{
    public function __construct(private readonly AiChatService $aiChatService)
    {
    }

    #[Route(path: '/api/moorl-foundation/ai/test', name: 'api.moorl-foundation.ai.test', methods: ['POST'])]
    public function test(Context $context): JsonResponse
    {
        return new JsonResponse($this->aiChatService->test($context));
    }

    #[Route(path: '/api/moorl-foundation/ai/chat', name: 'api.moorl-foundation.ai.chat', methods: ['POST'])]
    public function chat(Request $request, Context $context): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $messages = is_array($payload) && is_array($payload['messages'] ?? null) ? $payload['messages'] : [];
        $item = is_array($payload) && is_array($payload['item'] ?? null) ? $payload['item'] : [];
        $images = is_array($payload) && is_array($payload['images'] ?? null) ? $payload['images'] : [];

        return new JsonResponse($this->aiChatService->chat($messages, $item, $context, $images));
    }
}
