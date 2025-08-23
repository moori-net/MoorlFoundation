<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Task;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ClearLocationCacheTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'moorl_foundation.clear_location_cache';
    }

    public static function getDefaultInterval(): int
    {
        return 60 * 60 * 24; // 24h
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}
