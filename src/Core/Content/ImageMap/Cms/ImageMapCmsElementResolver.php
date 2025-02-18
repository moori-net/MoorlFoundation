<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap\Cms;

use MoorlFoundation\Core\Content\ImageMap\ImageMapEntity;
use MoorlFoundation\Core\Content\ImageMap\SalesChannel\ImageMapAvailableFilter;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ImageMapCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        private readonly SalesChannelRepository $salesChannelRepository,
        private readonly ProductConfiguratorLoader $configuratorLoader
    )
    {
    }

    public function getType(): string
    {
        return 'moorl-image-map';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig()->get('combinationDiscount');
        if (!$config || !$config->getValue()) {
            return;
        }

        $criteria = new Criteria([$config->getValue()]);
        $criteria->addAssociation('items.product.cover.media');
        $criteria->addAssociation('items.product.prices');
        $criteria->addAssociation('items.category');
        $criteria->addFilter(new ImageMapAvailableFilter());
        $itemsCriteria = $criteria->getAssociation('items');
        $itemsCriteria->addFilter(new NotFilter(NotFilter::CONNECTION_OR, [
            new EqualsFilter('active', false),
        ]));
        $itemsCriteria->addFilter(new EqualsFilter('product.children.id', null));

        /** @var ImageMapEntity $combinationDiscount */
        $combinationDiscount = $this->salesChannelRepository->search($criteria, $resolverContext->getSalesChannelContext())->first();
        if (!$combinationDiscount) {
            return;
        }

        $config = $slot->getFieldConfig()->get('options');
        if ($config) {
            $combinationDiscount->addOptions($config->getValue());
        }

        $this->enrichImageMapItems($combinationDiscount, $resolverContext->getSalesChannelContext());

        $cmsData = new ImageMapCmsStruct();
        $cmsData->setImageMap($combinationDiscount);

        $slot->setData($cmsData);
    }

    private function enrichImageMapItems(ImageMapEntity $combinationDiscount, SalesChannelContext $salesChannelContext): void
    {
        if (!$combinationDiscount->getItems()) {
            return;
        }

        $combinationDiscount->getItems()->sortByPriority();

        foreach ($combinationDiscount->getItems() as $item) {
            $product = $item->getProduct();
            if ($product instanceof SalesChannelProductEntity) {
                $product->setSortedProperties($this->configuratorLoader->load($product, $salesChannelContext));
            }
        }
    }
}
