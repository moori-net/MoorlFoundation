<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\CmsBrandslider;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class AppflixBrandSliderCmsElementResolver extends AbstractCmsElementResolver
{
    private const PRODUCT_SLIDER_ENTITY_FALLBACK = 'appflix-brand-slider-entity-fallback';
    private const STATIC_SEARCH_KEY = 'appflix-brand-slider';

    public function getType(): string
    {
        return 'appflix-brand-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        $collection = new CriteriaCollection();

        if (!$brands = $config->get('brands')) {
            return null;
        }

        if ($brands->isStatic() && $brands->getValue()) {
            $criteria = new Criteria($brands->getValue());
            $criteria->addAssociation('media');
            $collection->add(self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier(), ProductManufacturerDefinition::class, $criteria);
        }

        if ($brands->isMapped() && $brands->getValue() && $resolverContext instanceof EntityResolverContext) {
            if ($criteria = $this->collectByEntity($resolverContext, $brands)) {
                $collection->add(self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier(), ProductManufacturerDefinition::class, $criteria);
            }
        }

        return $collection->all() ? $collection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $slider = new AppflixBrandSliderStruct();
        $slot->setData($slider);

        if (!$productConfig = $config->get('brands')) {
            return;
        }

        if ($productConfig->isStatic()) {
            $this->enrichFromSearch($slider, $result, self::STATIC_SEARCH_KEY . '_' . $slot->getUniqueIdentifier());
        }

        if ($productConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $brands = $this->resolveEntityValue($resolverContext->getEntity(), $productConfig->getValue());
            if (!$brands) {
                $this->enrichFromSearch($slider, $result, self::PRODUCT_SLIDER_ENTITY_FALLBACK . '_' . $slot->getUniqueIdentifier());
            } else {
                $slider->setBrands($brands);
            }
        }
    }

    private function enrichFromSearch(AppflixBrandSliderStruct $slider, ElementDataCollection $result, string $searchKey): void
    {
        $searchResult = $result->get($searchKey);
        if (!$searchResult) {
            return;
        }

        /** @var ProductManufacturerCollection|null $brands */
        $brands = $searchResult->getEntities();
        if (!$brands) {
            return;
        }

        $slider->setBrands($brands);
    }

    private function collectByEntity(EntityResolverContext $resolverContext, FieldConfig $config): ?Criteria
    {
        $entityBrands = $this->resolveEntityValue($resolverContext->getEntity(), $config->getValue());
        if ($entityBrands) {
            return null;
        }

        $criteria = $this->resolveCriteriaForLazyLoadedRelations($resolverContext, $config);
        $criteria->addAssociation('cover');

        return $criteria;
    }
}
