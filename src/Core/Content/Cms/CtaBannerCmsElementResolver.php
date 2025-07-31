<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class CtaBannerCmsElementResolver extends AbstractCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-cta-banner';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $criteriaCollection = new CriteriaCollection();
        $config = $slot->getFieldConfig();

        $mediaConfig = $config->get('media');
        if ($mediaConfig && $mediaConfig->getValue()) {
            $criteria = new Criteria([$mediaConfig->getValue()]);
            $criteriaCollection->add('media_' . $slot->getUniqueIdentifier(), MediaDefinition::class, $criteria);
        }

        $iconMediaConfig = $config->get('iconMedia');
        if ($iconMediaConfig && $iconMediaConfig->getValue()) {
            $criteria = new Criteria([$iconMediaConfig->getValue()]);
            $criteriaCollection->add('icon_media_' . $slot->getUniqueIdentifier(), MediaDefinition::class, $criteria);
        }

        $categoryConfig = $config->get('category');
        if ($categoryConfig && $categoryConfig->getValue()) {
            $criteria = new Criteria([$categoryConfig->getValue()]);
            $criteria->addAssociation('media');
            $criteriaCollection->add('category_' . $slot->getUniqueIdentifier(), CategoryDefinition::class, $criteria);
        }

        $productConfig = $config->get('product');
        if ($productConfig && $productConfig->getValue()) {
            $criteria = new Criteria([$productConfig->getValue()]);
            $criteria->addAssociation('cover.media');
            $criteriaCollection->add('product_' . $slot->getUniqueIdentifier(), ProductDefinition::class, $criteria);
        }

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $ctaBanner = new CtaBannerStruct();
        $slot->setData($ctaBanner);

        $mediaConfig = $config->get('media');
        if ($mediaConfig && $mediaConfig->getValue()) {
            $searchResult = $result->get('media_' . $slot->getUniqueIdentifier());
            if ($searchResult) {
                $media = $searchResult->get($mediaConfig->getValue());
                if ($media) {
                    $ctaBanner->setMedia($media);
                }
            }
        }

        $iconMediaConfig = $config->get('iconMedia');
        if ($iconMediaConfig && $iconMediaConfig->getValue()) {
            $searchResult = $result->get('icon_media_' . $slot->getUniqueIdentifier());
            $ctaBanner->setIconMedia($searchResult?->get($iconMediaConfig->getValue()));
        }

        $categoryConfig = $config->get('category');
        if ($categoryConfig && $categoryConfig->getValue()) {
            $searchResult = $result->get('category_' . $slot->getUniqueIdentifier());
            $ctaBanner->setCategory($searchResult?->get($categoryConfig->getValue()));
        }

        $productConfig = $config->get('product');
        if ($productConfig && $productConfig->getValue()) {
            $searchResult = $result->get('product_' . $slot->getUniqueIdentifier());
            $ctaBanner->setProduct($searchResult?->get($productConfig->getValue()));
        }

        $scssConfig = $config->get('scss');
        $enableScssConfig = $config->get('enableScss');
        if ($enableScssConfig && $enableScssConfig->getValue()) {
            $ctaBanner->setScss($scssConfig->getValue(), $slot->getUniqueIdentifier());
        }
    }
}
