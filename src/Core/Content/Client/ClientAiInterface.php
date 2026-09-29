<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

interface ClientAiInterface
{
    public function getResponseOptions(): array;

    public function createResponse(array $messages, array $requestOptions = []): array;
}
