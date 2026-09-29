<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\Client\ClientAiInterface;
use MoorlFoundation\Core\Content\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class AiChatService
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly ClientService $clientService,
        private readonly AiChatContextService $aiChatContextService,
        private readonly LoggerInterface $clientLogger
    )
    {
    }

    public function test(Context $context): array
    {
        $client = $this->getConfiguredAiClient($context);

        if (is_array($client)) {
            return $client;
        }

        try {
            $result = $this->clientService->test((string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), $context);
        } catch (\Throwable $exception) {
            $this->logClientError('AI client connection test failed.', (string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), $exception, $client);
            return ['errors' => [['message' => 'AI client connection test failed.']]];
        }

        if (!empty($result['errors'])) {
            $this->logClientError('AI client connection test failed.', (string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), null, $client, $result['errors']);
            return ['errors' => [['message' => 'AI client connection test failed.']]];
        }

        return ['success' => true];
    }

    public function chat(array $messages, array $item, Context $context, array $images = []): array
    {
        $chatContext = $this->extractChatContext($messages, $item);

        if ($chatContext === null) {
            return ['errors' => [['message' => 'Chat context is invalid.']]];
        }

        $messages = $this->normalizeMessages(array_slice($messages, 1));

        if ($messages === null) {
            return ['errors' => [['message' => 'Chat messages are invalid.']]];
        }

        $images = $this->normalizeImages($images);

        if ($images === null || ($images !== [] && ($messages[0]['role'] ?? null) !== 'user')) {
            return ['errors' => [['message' => 'Chat images are invalid.']]];
        }

        if ($images !== []) {
            $messages[0]['content'] = array_merge($messages[0]['content'], $images);
        }

        array_unshift($messages, [
            'role' => 'developer',
            'content' => [[
                'type' => 'input_text',
                'text' => $chatContext['instructions'],
            ]],
        ]);

        $client = $this->getConfiguredAiClient($context);

        if (is_array($client)) {
            return $client;
        }

        if (!$client instanceof ClientAiInterface) {
            return ['errors' => [['message' => 'Configured AI client does not support chat.']]];
        }

        try {
            $response = $client->createResponse($messages, $chatContext['requestOptions']);
            if (!empty($response['error'])) {
                $this->logClientError('AI chat provider returned an error.', (string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), null, $client, $response['error']);
                return ['errors' => [['message' => 'AI chat request failed.']]];
            }

            $chatResponse = $this->extractResponse($response, $chatContext['allowedFields']);
        } catch (\Throwable $exception) {
            $this->logClientError('AI chat request failed.', (string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), $exception, $client);
            return ['errors' => [['message' => 'AI chat request failed.']]];
        }

        if ($chatResponse === null) {
            $this->logClientError('AI chat response did not contain text.', (string) $this->systemConfigService->get('MoorlFoundation.config.aiClient'), null, $client);
            return ['errors' => [['message' => 'AI chat request failed.']]];
        }

        return $chatResponse;
    }

    private function getConfiguredAiClient(Context $context): ClientInterface|array
    {
        $clientId = $this->systemConfigService->get('MoorlFoundation.config.aiClient');

        if (!$clientId) {
            return ['errors' => [['message' => 'No AI client is configured.']]];
        }

        try {
            $client = $this->clientService->getClient($clientId, $context);
        } catch (\Throwable $exception) {
            $this->logClientError('Configured AI client could not be loaded.', (string) $clientId, $exception);
            return ['errors' => [['message' => 'Configured AI client could not be loaded.']]];
        }

        if ($client->getClientType() !== ClientService::TYPE_AI) {
            return ['errors' => [['message' => 'Configured client is not an AI client.']]];
        }

        if (!$client->getClientEntity()->getActive()) {
            return ['errors' => [['message' => 'Configured AI client is inactive.']]];
        }

        return $client;
    }

    private function extractChatContext(array $messages, array $item): ?array
    {
        if ($messages === []) {
            return null;
        }

        $message = $messages[0] ?? null;
        $content = $message['content'][0] ?? null;

        if (!is_array($message) || ($message['role'] ?? null) !== 'context' || !is_array($message['content'] ?? null) || count($message['content']) !== 1 || !is_array($content) || ($content['type'] ?? null) !== 'entity_context' || !is_array($content['context'] ?? null)) {
            return null;
        }

        return $this->aiChatContextService->build($content['context'], $item);
    }

    private function normalizeMessages(array $messages): ?array
    {
        if ($messages === [] || count($messages) > 29) {
            return null;
        }

        $normalizedMessages = [];

        foreach ($messages as $message) {
            if (!is_array($message) || !in_array($message['role'] ?? null, ['user', 'assistant'], true) || empty($message['content']) || !is_array($message['content'])) {
                return null;
            }

            $content = [];
            $messageLength = 0;
            foreach ($message['content'] as $part) {
                if (!is_array($part) || ($part['type'] ?? null) !== 'text' || !is_string($part['text'] ?? null)) {
                    return null;
                }

                $text = trim($part['text']);
                $messageLength += mb_strlen($text);
                if ($text === '' || $messageLength > 10000) {
                    return null;
                }

                $content[] = [
                    'type' => $message['role'] === 'assistant' ? 'output_text' : 'input_text',
                    'text' => $text,
                ];
            }

            $normalizedMessages[] = [
                'role' => $message['role'],
                'content' => $content,
            ];
        }

        return $normalizedMessages;
    }

    private function normalizeImages(array $images): ?array
    {
        if (count($images) > 1) {
            return null;
        }

        $normalizedImages = [];

        foreach ($images as $image) {
            if (!is_array($image) || array_diff(array_keys($image), ['url']) !== [] || !is_string($image['url'] ?? null) || !filter_var($image['url'], FILTER_VALIDATE_URL) || strtolower((string) parse_url($image['url'], PHP_URL_SCHEME)) !== 'https') {
                return null;
            }

            $normalizedImages[] = [
                'type' => 'input_image',
                'image_url' => $image['url'],
                'detail' => 'auto',
            ];
        }

        return $normalizedImages;
    }

    private function extractResponse(array $response, array $allowedFields): ?array
    {
        if (!is_array($response['output'] ?? null)) {
            return null;
        }

        $parts = [];

        foreach ($response['output'] as $output) {
            if (!is_array($output)) {
                return null;
            }

            if (($output['type'] ?? null) !== 'message') {
                continue;
            }

            if (!is_array($output['content'] ?? null)) {
                return null;
            }

            foreach ($output['content'] as $content) {
                if (!is_array($content)) {
                    return null;
                }

                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    $parts[] = $content['text'];
                }
            }
        }

        $text = trim(implode('', $parts));

        if ($text === '') {
            return null;
        }

        try {
            $payload = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($payload) || array_diff(array_keys($payload), ['message', 'changes']) !== [] || !is_string($payload['message'] ?? null) || !is_array($payload['changes'] ?? null)) {
            return null;
        }

        $message = trim($payload['message']);
        $changes = $this->validateChanges($payload['changes'], $allowedFields);

        if ($message === '' || $changes === null) {
            return null;
        }

        return $changes === [] ? ['message' => $message] : ['message' => $message, 'changes' => $changes];
    }

    private function validateChanges(array $changes, array $allowedFields): ?array
    {
        $patch = [];

        foreach ($changes as $change) {
            if (!is_array($change) || array_diff(array_keys($change), ['field', 'value']) !== [] || !is_string($change['field'] ?? null) || !array_key_exists('value', $change)) {
                return null;
            }

            $field = $change['field'];
            $value = $change['value'];

            if (!isset($allowedFields[$field]) || isset($patch[$field])) {
                return null;
            }

            $valid = match ($allowedFields[$field]) {
                'string' => is_string($value),
                'integer' => is_int($value),
                'number' => is_int($value) || is_float($value),
                'boolean' => is_bool($value),
                default => false,
            };

            if (!$valid) {
                return null;
            }

            $patch[$field] = $value;
        }

        return $patch;
    }

    private function logClientError(
        string $message,
        ?string $clientId,
        ?\Throwable $exception = null,
        ?ClientInterface $client = null,
        mixed $providerError = null
    ): void {
        $context = array_filter([
            'clientId' => $clientId,
            'exceptionClass' => $exception ? $exception::class : null,
            'exceptionCode' => $exception?->getCode(),
            'exceptionMessage' => $exception ? $this->redact($exception->getMessage(), $client) : null,
            'providerError' => $providerError ? $this->redact(json_encode($providerError, JSON_THROW_ON_ERROR), $client) : null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $this->clientLogger->error($message, $context);
    }

    private function redact(string $message, ?ClientInterface $client = null): string
    {
        $apiKey = $client?->getClientEntity()->getConfig()['apiKey'] ?? null;

        if (is_string($apiKey) && $apiKey !== '') {
            $message = str_replace($apiKey, '[redacted]', $message);
        }

        return preg_replace('/\bsk-[A-Za-z0-9_-]+\b/', '[redacted]', $message) ?? $message;
    }
}
