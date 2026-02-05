<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class ProductBuyListCmsElementResolver extends ProductSliderCmsElementResolver
{
    private const TYPE = 'moorl-product-buy-list';

    private readonly ProductConfiguratorLoader $configuratorLoader;

    public function __construct() {
        $args = func_get_args();
        $this->configuratorLoader = array_pop($args);

        parent::__construct(...$args);
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $slider = new ProductSliderStruct();
        $slot->setData($slider);

        $productConfig = $config->get('products');
        if ($productConfig === null || $productConfig->getValue() === null) {
            return;
        }

        if (!$productConfig->isMapped() || !$resolverContext instanceof EntityResolverContext) {
            parent::enrich($slot, $resolverContext, $result);
            return;
        }

        /** @var ProductSliderStruct $slider */
        $slider = $slot->getData();
        if ($slider->getProducts()) {
            return;
        }

        /** @var SalesChannelProductCollection|null $products */
        $products = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());

        $sliderProducts = new SalesChannelProductCollection();

        if ($products) {
            /** @var SalesChannelProductEntity $product */
            foreach ($products as $product) {
                if ($product->getChildren()?->count() > 0) {
                    $sliderProducts->add($product->getChildren()->first());
                } else {
                    $sliderProducts->add($product);
                }
            }
        }

        /** @var SalesChannelProductEntity $product */
        foreach ($sliderProducts as $product) {
            if ($product->getSortedProperties() || !$product->getOptionIds()) {
                continue;
            }

            $product->setSortedProperties($this->configuratorLoader->load(
                $product,
                $resolverContext->getSalesChannelContext()
            ));
        }

        $slider->setProducts($sliderProducts);
    }
}
