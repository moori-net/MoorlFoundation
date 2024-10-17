<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ProductBuyListCmsElementResolver extends ProductSliderCmsElementResolver
{
    public function __construct(
        ProductStreamBuilderInterface $productStreamBuilder,
        SystemConfigService $systemConfigService,
        SalesChannelRepository $productRepository,
        private readonly ProductConfiguratorLoader $configuratorLoader
    ) {
        parent::__construct($productStreamBuilder, $systemConfigService, $productRepository);
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
