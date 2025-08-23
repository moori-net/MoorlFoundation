<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;

class MoorlFoundationExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*moorl_/', $e->getMessage())) {
            $number = [];
            preg_match('/Duplicate entry \'(.*)\' for key/', $e->getMessage(), $number);
            return new MoorlFoundationDuplicateEntryException([
                'value' => $number[1],
                'message' => $e->getMessage()
            ], $e);
        }

        return null;
    }
}
