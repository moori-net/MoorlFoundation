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

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $collection = parent::collect($slot, $resolverContext);
        if ($collection !== null) {
            return $collection;
        }

        $collection = new CriteriaCollection();
        $config = $slot->getFieldConfig();
        $productConfig = $config->get('products');
        if ($productConfig === null) {
            return null;
        }

        if ($productConfig->isMapped() && $productConfig->getStringValue() && $resolverContext instanceof EntityResolverContext) {
            $criteria = $this->collectByEntity($resolverContext, $productConfig);
            if ($criteria !== null) {
                $collection->add(
                    self::TYPE . $slot->getUniqueIdentifier(),
                    ProductDefinition::class,
                    $criteria
                );
            }
        }

        return $collection->all() ? $collection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);
        /** @var ProductSliderStruct $slider */
        $slider = $slot->getData();

        if (!$slider->getProducts()) {
            $config = $slot->getFieldConfig();
            $productConfig = $config->get('products');
            if ($productConfig === null) {
                return;
            }

            if ($productConfig->isMapped() && $productConfig->getStringValue() && $resolverContext instanceof EntityResolverContext) {
                $products = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getStringValue());
                if ($products === null) {
                    $this->enrichFromSearch(
                        $slider,
                        $result,
                        self::TYPE . $slot->getUniqueIdentifier()
                    );
                } else {
                    $slider->setProducts($products);
                }
            }
        }

        $products = $slider->getProducts() ?: [];

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

        return $this->resolveCriteriaForLazyLoadedRelations($resolverContext, $config);
    }

    private function enrichFromSearch(ProductSliderStruct $slider, ElementDataCollection $result, string $searchKey): void
    {
        $products = $result->get($searchKey)?->getEntities();
        if (!$products instanceof ProductCollection) {
            return;
        }

        $slider->setProducts($products);
    }
}
