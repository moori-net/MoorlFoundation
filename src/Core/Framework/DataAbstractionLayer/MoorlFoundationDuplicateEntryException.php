<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class MoorlFoundationDuplicateEntryException extends ShopwareHttpException
{
    public function __construct($payload, $e)
    {
        parent::__construct(
            'Entry already exists',
            $payload,
            $e
        );
    }

    public function getErrorCode(): string
    {
        return 'MOORL__DUPLICATE_ENTRY';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
