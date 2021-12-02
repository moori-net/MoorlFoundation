<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\Sorting\SortingCollection;
use MoorlFoundation\Core\Content\Sorting\SortingEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class SortingService
{
    protected EntityRepositoryInterface $sortingRepository;

    public function __construct(
        EntityRepositoryInterface $sortingRepository
    )
    {
        $this->sortingRepository = $sortingRepository;
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

    public function addSortingCriteria(string $id, Criteria $criteria, Context $context): void
    {
        $sorting = $this->getSorting($id, $context);
        if (!$sorting) {
            return;
        }

        $criteria->addSorting(...$sorting->createDalSorting());
    }

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
                $request->query->set('limit', $limitConfig->getValue());
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
            $this->addSortingCriteria($listingSortingConfig->getValue(), $criteria, $context);
        }
    }
}
