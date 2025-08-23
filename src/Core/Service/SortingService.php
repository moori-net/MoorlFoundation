<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\Sorting\SortingCollection;
use MoorlFoundation\Core\Content\Sorting\SortingEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class SortingService
{
    public function __construct(protected EntityRepository $sortingRepository)
    {
    }

    public function getFallbackSorting(string $entityName): SortingEntity
    {
        $sorting = new SortingEntity();
        $sorting->setId(Uuid::randomHex());
        $sorting->setPriority(0);
        $sorting->setActive(true);
        $sorting->setKey('created-at-desc');
        $sorting->setTranslated([
            'label' => 'New entries first (Fallback)'
        ]);
        $sorting->setFields([
            [
                'field' => $entityName . '.createdAt',
                'order' => 'desc',
                'priority' => 1,
                'naturalSorting' => 0
            ],
            [
                'field' => $entityName . '.id',
                'order' => 'desc',
                'priority' => 0,
                'naturalSorting' => 0
            ]
        ]);

        return $sorting;
    }

    public function getAvailableSortings(string $entityName, Context $context): EntityCollection
    {
        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('entity', $entityName))
            ->addSorting(new FieldSorting('priority', 'DESC'));

        /** @var SortingCollection $sortings */
        $sortings = $this->sortingRepository->search($criteria, $context)->getEntities();

        /* Fallback sorting */
        if ($sortings->count() < 1) {
            $sortings->add($this->getFallbackSorting($entityName));
        }

        return $sortings;
    }

    public function getSorting(string $id, Context $context): ?SortingEntity
    {
        $criteria = new Criteria([$id]);

        return $this->sortingRepository->search($criteria, $context)->get($id);
    }

    public function addSortingCriteria(string $id, Criteria $criteria, Context $context): ?SortingEntity
    {
        $sorting = $this->getSorting($id, $context);
        if (!$sorting) {
            return null;
        }

        $criteria->addSorting(...$sorting->createDalSorting());

        return $sorting;
    }

    /**
     * @deprecated extend FoundationListingCmsElementResolver instead
     */
    public function enrichCmsElementResolverCriteria(
        CmsSlotEntity $slot,
        Criteria $criteria,
        Context $context,
        ?Request $request = null
    ): void
    {
        if ($request) {
            $request->query->set('slots', $slot->getId());
        }

        $criteria->setTitle("cms::" . $slot->getType());

        $config = $slot->getFieldConfig();
        $limitConfig = $config->get('limit');
        if ($limitConfig && $limitConfig->getValue()) {
            $criteria->setLimit($limitConfig->getValue());
            if ($request) {
                $request->query->set('moorl_limit', $limitConfig->getValue());
            }
        }

        $listingSourceConfig = $config->get('listingSource');
        if ($listingSourceConfig && $listingSourceConfig->getValue() === 'select') {
            $listingItemIdsConfig = $config->get('listingItemIds');
            if ($listingItemIdsConfig && $listingItemIdsConfig->getArrayValue()) {
                $criteria->setIds($listingItemIdsConfig->getArrayValue());
            }
        }

        $listingSortingConfig = $config->get('listingSorting');
        if ($listingSortingConfig && $listingSortingConfig->getValue()) {
            $sorting = $this->addSortingCriteria($listingSortingConfig->getValue(), $criteria, $context);
            if ($listingSourceConfig && $listingSourceConfig->getValue() === 'auto') {
                $criteria->resetSorting();
                if ($request && !$request->query->get('order') && $sorting) {
                    $request->query->set('order', $sorting->getKey());
                }
            }
        }
    }

    /**
     * @deprecated extend FoundationListingCmsElementResolver instead
     */
    public function enrichCmsElementResolverCriteriaV2(
        CmsSlotEntity $slot,
        Criteria $criteria,
        ResolverContext $resolverContext
    ): void
    {
        $request = $resolverContext->getRequest();
        $salesChannelContext = $resolverContext->getSalesChannelContext();

        $this->enrichCmsElementResolverCriteria($slot, $criteria, $salesChannelContext->getContext(), $request);

        if ($resolverContext instanceof EntityResolverContext) {
            $config = $slot->getFieldConfig();
            $foreignKeyConfig = $config->get('foreignKey');
            if ($foreignKeyConfig && $foreignKeyConfig->getValue() && !in_array($foreignKeyConfig->getValue(), ['Keine', 'None'])) {
                $criteria->addFilter(new EqualsFilter(
                    $foreignKeyConfig->getValue(),
                    $resolverContext->getEntity()->getUniqueIdentifier()
                ));
            } else {
                $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
                    new EqualsFilter('id', $resolverContext->getEntity()->getUniqueIdentifier())
                ]));
            }
        }
    }
}
