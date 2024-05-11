<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Task;

use MoorlFoundation\Core\Service\EntityAutoCacheService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntityAutoCacheTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        private readonly EntityAutoCacheService $entityAutoCacheService,
        protected EntityRepository $scheduledTaskRepository,
        protected readonly ?LoggerInterface $exceptionLogger = null
    )
    {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public static function getHandledMessages(): iterable
    {
        return [EntityAutoCacheTask::class];
    }

    public function run(?SymfonyStyle $console = null): void
    {
        $this->entityAutoCacheService->scanForTimeControlledEntities(EntityAutoCacheService::TRIGGER_SCHEDULED , $console);
    }
}
