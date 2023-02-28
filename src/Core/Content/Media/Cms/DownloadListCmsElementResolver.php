<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Media\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\Cms\Type\ImageSliderTypeDataResolver;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class DownloadListCmsElementResolver extends ImageSliderTypeDataResolver
{
    public function getType(): string
    {
        return 'moorl-download-list';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $downloadsConfig = $slot->getFieldConfig()->get('downloads');
        if ($downloadsConfig === null || $downloadsConfig->isMapped()) {
            return null;
        }

        $criteria = new Criteria($downloadsConfig->getArrayValue());

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add('media_' . $slot->getUniqueIdentifier(), MediaDefinition::class, $criteria);

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $fieldConfig = $slot->getFieldConfig();
        $data = new DownloadListStruct();
        $slot->setData($data);

        $downloadsConfig = $fieldConfig->get('downloads');

        if ($downloadsConfig->isStatic()) {
            $searchResult = $result->get('media_' . $slot->getUniqueIdentifier());
            if (!$searchResult) {
                return;
            }

            $downloads = $searchResult->getEntities();
        }

        if ($downloadsConfig->isMapped() && $resolverContext instanceof EntityResolverContext) {
            $downloads = $this->resolveEntityValue($resolverContext->getEntity(), $downloadsConfig->getStringValue());
            if ($downloads instanceof MediaEntity) {
                $downloads = new MediaCollection([$downloads]);
            }
            if ($downloads === null || \count($downloads) < 1) {
                return;
            }
        }

        $this->sortItemsByFileName($downloads);
        $data->setDownloads($downloads);
    }

    protected function sortItemsByFileName(MediaCollection $downloads): void
    {
        if (!$downloads->first()) {
            return;
        }

        $downloads->sort(fn(MediaEntity $a, MediaEntity $b) => strnatcasecmp($a->getFileName(), $b->getFileName()));
    }
}
