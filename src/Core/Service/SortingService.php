<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\Sorting\SortingCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class SortingService
{
    protected EntityRepositoryInterface $sortingRepository;

    public function __construct(
        EntityRepositoryInterface $sortingRepository
    )
    {
        $this->sortingRepository = $sortingRepository;
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

        return $sortings;
    }
}
