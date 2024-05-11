<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Task;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class EntityAutoCacheTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'moorl_foundation.entity_auto_cache';
    }

    public static function getDefaultInterval(): int
    {
        return 60 * 5; // 5m
    }
}
