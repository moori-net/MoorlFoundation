<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia\Cms;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class EmbeddedMediaCmsElementResolver extends AbstractCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-embedded-media';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $criteriaCollection = new CriteriaCollection();
        $config = $slot->getFieldConfig();

        $embeddedMediaConfig = $config->get('embeddedMedia');
        if ($embeddedMediaConfig && $embeddedMediaConfig->getValue()) {
            $criteria = new Criteria([$embeddedMediaConfig->getValue()]);
            $criteria->addAssociation('media');
            $criteria->addAssociation('videos.media');
            $criteriaCollection->add('moorl_media_' . $slot->getUniqueIdentifier(), EmbeddedMediaDefinition::class, $criteria);
        }

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $embeddedMedia = new EmbeddedMediaCmsStruct();
        $slot->setData($embeddedMedia);

        $embeddedMediaConfig = $config->get('embeddedMedia');
        if ($embeddedMediaConfig && $embeddedMediaConfig->getValue()) {
            $searchResult = $result->get('moorl_media_' . $slot->getUniqueIdentifier());
            $embeddedMedia->setEmbeddedMedia($searchResult?->get($embeddedMediaConfig->getValue()));
        }
    }
}
