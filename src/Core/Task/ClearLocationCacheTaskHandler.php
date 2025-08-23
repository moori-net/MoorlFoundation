<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Task;

use MoorlFoundation\Core\Service\LocationServiceV2;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ClearLocationCacheTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly LocationServiceV2 $locationServiceV2
    )
    {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public static function getHandledMessages(): iterable
    {
        return [ClearLocationCacheTask::class];
    }

    public function run(): void
    {
        $this->locationServiceV2->clearLocationCache();
    }
}
