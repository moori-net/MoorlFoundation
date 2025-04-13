<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class ProductBuyListCmsElementResolver extends ProductSliderCmsElementResolver
{
    private readonly ProductConfiguratorLoader $configuratorLoader;

    public function __construct() {
        $args = func_get_args();
        $this->configuratorLoader = array_pop($args);

        parent::__construct(...$args);
    }

    public function getType(): string
    {
        return 'moorl-product-buy-list';
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);

        /** @var ProductSliderStruct $slider */
        $slider = $slot->getData();

        $products = $slider->getProducts();

        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {
            if ($product->getSortedProperties() || !$product->getOptionIds()) {
                continue;
            }

            $product->setSortedProperties($this->configuratorLoader->load($product, $resolverContext->getSalesChannelContext()));
        }
    }
}
