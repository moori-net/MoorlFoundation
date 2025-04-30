<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductBuyListCmsElementResolver extends ProductSliderCmsElementResolver
{
    private const PRODUCT_SLIDER_ENTITY_FALLBACK = 'product-slider-entity-fallback';

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

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $collection = new CriteriaCollection();

        $products = $config->get('products');
        if ($products === null) {
            return null;
        }

        if ($products->isMapped() && $products->getValue() && $resolverContext instanceof EntityResolverContext) {
            $criteria = $this->collectByEntity($resolverContext, $products);
            if ($criteria !== null) {
                $collection->add(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);
            }
        }

        return $collection->all() ? $collection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);

        $config = $slot->getFieldConfig();
        $productConfig = $config->get('products');

        /** @var ProductSliderStruct $slider */
        $slider = $slot->getData();

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $products = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
            if ($products === null) {
                $this->enrichFromSearch($slider, $result, self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), $resolverContext->getSalesChannelContext());
            } else {
                $slider->setProducts($products);
            }
        }

        $products = $slider->getProducts();

        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {
            if ($product->getSortedProperties() || !$product->getOptionIds()) {
                continue;
            }

            $product->setSortedProperties($this->configuratorLoader->load($product, $resolverContext->getSalesChannelContext()));
        }
    }

    private function collectByEntity(EntityResolverContext $resolverContext, FieldConfig $config): ?Criteria
    {
        $entityProducts = $this->resolveEntityValue($resolverContext->getEntity(), $config->getStringValue());
        if ($entityProducts !== null) {
            return null;
        }

        $criteria = $this->resolveCriteriaForLazyLoadedRelations($resolverContext, $config);

        return $criteria;
    }

    private function enrichFromSearch(ProductSliderStruct $slider, ElementDataCollection $result, string $searchKey, SalesChannelContext $saleschannelContext): void
    {
        $products = $result->get($searchKey)?->getEntities();
        if (!$products instanceof ProductCollection) {
            return;
        }

        $slider->setProducts($products);
    }
}
