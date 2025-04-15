<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Task;

use MoorlFoundation\Core\Service\EntityAutoCacheService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class EntityAutoCacheTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly EntityAutoCacheService $entityAutoCacheService
    )
    {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public static function getHandledMessages(): iterable
    {
        return [EntityAutoCacheTask::class];
    }

    public function run(): void
    {
        $this->entityAutoCacheService->scanForTimeControlledEntities(EntityAutoCacheService::TRIGGER_SCHEDULED);
    }
}
