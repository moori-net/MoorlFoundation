<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\SalesChannel\Price;

use MoorlFoundation\Core\Service\PriceCalculatorService;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\Service\ResetInterface;

class FoundationProductPriceCalculator extends AbstractProductPriceCalculator implements ResetInterface
{
    public function __construct(
        private readonly AbstractProductPriceCalculator $decorated,
        private readonly PriceCalculatorService $priceCalculatorService
    )
    {
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        return $this->decorated;
    }

    public function calculate(iterable $products, SalesChannelContext $context): void
    {
        $this->priceCalculatorService->calculate($products, $context, $this->decorated);
    }
}
