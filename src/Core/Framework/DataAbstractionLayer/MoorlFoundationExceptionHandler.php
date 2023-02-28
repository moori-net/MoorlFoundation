<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;

class MoorlFoundationExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Exception $e, WriteCommand $command = null): ?\Exception
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.moorl.*/', $e->getMessage())) {
            $payload = $command->getPayload() ?: null;

            return new MoorlFoundationDuplicateEntryException($payload, $e);
        }

        return null;
    }
}
