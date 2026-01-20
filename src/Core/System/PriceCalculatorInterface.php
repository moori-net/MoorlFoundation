<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface PriceCalculatorInterface
{
    public function getName(): string;
    public function getPriority(): int;
    public function shouldBreak(): bool;
    public function init(SalesChannelContext $salesChannelContext): void;
    public function match(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): bool;
    public function calculate(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void;
}
