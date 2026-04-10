<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use MoorlFoundation\Core\Service\PriceCalculatorService;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PriceCalculatorExtension
{
    protected bool $active = true;
    protected int $priority = 0;
    protected bool $shouldBreak = true;
    protected ?string $calculationPriceSource = null;
    protected ?string $listPriceSource = null;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function shouldBreak(): bool
    {
        return $this->shouldBreak;
    }

    public function getCalculationPriceSource(): string
    {
        return $this->calculationPriceSource ?? PriceCalculatorService::SOURCE_ORIGIN_PRICE;
    }

    public function getListPriceSource(): string
    {
        return $this->listPriceSource ?? PriceCalculatorService::SOURCE_ORIGIN_LIST_PRICE;
    }

    public function init(SalesChannelContext $salesChannelContext): void
    {
        $salesChannelId = $salesChannelContext->getSalesChannelId();

        $this->shouldBreak = $this->priceCalculatorService->getShouldBreak(
            $this->getName(),
            $salesChannelId
        );
        $this->priority = $this->priceCalculatorService->getPriority(
            $this->getName(),
            $salesChannelId
        );
        $this->calculationPriceSource = $this->priceCalculatorService->getCalculationPriceSource(
            $this->getName(),
            $salesChannelId
        );
        $this->listPriceSource = $this->priceCalculatorService->getListPriceSource(
            $this->getName(),
            $salesChannelId
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
