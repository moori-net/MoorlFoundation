<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use MoorlFoundation\Core\Service\PriceCalculatorService;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PriceCalculatorExtension
{
    protected int $priority = 0;
    protected bool $shouldBreak = true;

    public function __construct(
        protected readonly EntityRepository $repository,
        protected readonly SystemConfigService $systemConfigService,
        protected readonly PriceCalculatorService $priceCalculatorService
    )
    {
    }

    public function getName(): string
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function shouldBreak(): bool
    {
        return $this->shouldBreak;
    }

    public function init(SalesChannelContext $salesChannelContext): void
    {
        $this->shouldBreak = $this->systemConfigService->getBool(
            sprintf("%s.config.%s", $this->getName(), PriceCalculatorService::CONFIG_KEY_SHOULD_BREAK),
            $salesChannelContext->getSalesChannelId()
        );

        $this->priority = $this->systemConfigService->getInt(
            sprintf("%s.config.%s", $this->getName(), PriceCalculatorService::CONFIG_KEY_PRIORITY),
            $salesChannelContext->getSalesChannelId()
        );
    }

    public function match(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): bool
    {
        throw new \RuntimeException('Not implemented');
    }

    public function calculate(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        throw new \RuntimeException('Not implemented');
    }
}
