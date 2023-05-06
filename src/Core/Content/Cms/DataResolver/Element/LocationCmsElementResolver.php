<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\LocationStruct;
use MoorlFoundation\Core\Content\Marker\MarkerCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;

class LocationCmsElementResolver extends FoundationCmsElementResolver
{
    public function __construct(
        private readonly EntityRepository $markerRepository
    )
    {
    }

    public function getType(): string
    {
        return 'moorl-location';
    }

    public function getStruct(): Struct
    {
        return new LocationStruct();
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);

        if (!$slot->getFieldConfig()->get('legend')->getValue()) {
            return;
        }

        if (!$slot->getFieldConfig()->get('legendItems')->getValue()) {
            return;
        }

        $criteria = new Criteria($slot->getFieldConfig()->get('legendItems')->getValue());

        $criteria->addAssociation('marker');

        /** @var MarkerCollection $markers */
        $markers = $this->markerRepository->search($criteria, $resolverContext->getSalesChannelContext()->getContext())->getEntities();

        /** @var LocationStruct $data */
        $data = $slot->getData();

        $data->setMarkers($markers->sortByName());
    }
}
