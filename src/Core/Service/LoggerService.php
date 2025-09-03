<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Monolog\Level;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class LoggerService implements LoggerInterface
{
    private int $level;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?SystemConfigService $systemConfigService = null,
        ?string $configKey = null
    )
    {
        $this->level = 100;

        if ($systemConfigService && $configKey) {
            $this->level = (int)$this->systemConfigService->get($configKey) ?: Level::Error->value;
        }
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Debug->value) {
            $this->logger->debug($message, $context);
        }
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Info->value) {
            $this->logger->info($message, $context);
        }
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Notice->value) {
            $this->logger->notice($message, $context);
        }
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Warning->value) {
            $this->logger->warning($message, $context);
        }
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Error->value) {
            $this->logger->error($message, $context);
        }
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Critical->value) {
            $this->logger->critical($message, $context);
        }
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Alert->value) {
            $this->logger->alert($message, $context);
        }
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        if ($this->level <= Level::Emergency->value) {
            $this->logger->emergency($message, $context);
        }
    }
}
