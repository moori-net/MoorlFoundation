<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\LocationStruct;
use MoorlFoundation\Core\Content\Location\LocationDefinition;
use MoorlFoundation\Core\Content\Location\LocationEntity;
use MoorlFoundation\Core\Content\Sorting\SortingCollection;
use MoorlFoundation\Core\Service\LocationServiceV2;
use MoorlFoundation\Core\Service\SortingService;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\Filter;
use Shopware\Core\Content\Product\SalesChannel\Listing\FilterCollection;
use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\StatsAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\PrefixFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class EntityListingFeaturesSubscriberExtension
{
    final public const DEFAULT_SEARCH_SORT = 'standard';

    protected string $entityName = "";

    public function __construct(
        protected SortingService $sortingService,
        protected ?LocationServiceV2 $locationServiceV2 = null
    )
    {
    }

    public function handleFlags(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();
        $criteria = $event->getCriteria();

        if ($request->query->get('no-aggregations')) {
            $criteria->resetAggregations();
        }

        if ($request->query->get('only-aggregations')) {
            $criteria->setLimit(0);
            $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);
            $criteria->resetSorting();
            $criteria->resetAssociations();
        }
    }

    public function handleListingRequest(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();
        $criteria = $event->getCriteria();
        $context = $event->getSalesChannelContext();

        if (!$request->query->get('order')) {
            $request->request->set('order', self::DEFAULT_SEARCH_SORT);
        }

        $this->handlePagination($request, $criteria, $event->getSalesChannelContext());
        $this->handleFilters($request, $criteria, $context);
        $this->handleSorting($request, $criteria, $context);
    }

    public function handleSearchRequest(ProductListingCriteriaEvent $event): void
    {
        $request = $event->getRequest();
        $criteria = $event->getCriteria();
        $context = $event->getSalesChannelContext();

        if (!$request->query->get('order')) {
            $request->request->set('order', self::DEFAULT_SEARCH_SORT);
        }

        $this->handlePagination($request, $criteria, $event->getSalesChannelContext());
        $this->handleFilters($request, $criteria, $context);
        $this->handleSorting($request, $criteria, $context);
    }

    public function handleResult(ProductListingResultEvent $event): void
    {
        $this->addCurrentFilters($event);

        $result = $event->getResult();

        $sortings = $this->sortingService->getAvailableSortings(
            $this->entityName,
            $event->getSalesChannelContext()->getContext()
        );

        $currentSortingKey = $this->getCurrentSorting($sortings, $event->getRequest())->getKey();

        $result->setSorting($currentSortingKey);
        $result->setAvailableSortings($sortings);
        $result->setPage($this->getPage($event->getRequest()));
        $result->setLimit($this->getLimit($event->getRequest()));
    }

    private function handleFilters(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        $filters = $this->getFilters($request, $context);

        $aggregations = $this->getAggregations($request, $filters);

        foreach ($aggregations as $aggregation) {
            $criteria->addAggregation($aggregation);
        }

        if ($request->query->get('search')) {
            $criteria->setTerm($request->query->get('search'));
        }

        foreach ($filters as $filter) {
            if ($filter->isFiltered()) {
                $criteria->addPostFilter($filter->getFilter());
            }
        }

        /* Handle "locationCache" if radius filter active */
        if ($criteria->hasAssociation('locationCache') && $context->hasExtension(LocationDefinition::ENTITY_NAME)) {
            /** @var LocationEntity $location */
            $location = $context->getExtension(LocationDefinition::ENTITY_NAME);
            $locationCacheCriteria = $criteria->getAssociation('locationCache');
            $locationCacheCriteria->addFilter(new EqualsFilter('locationId', $location->getId()));
            $context->removeExtension(LocationDefinition::ENTITY_NAME);
            $request->attributes->set('order', 'distance');
        } else if ($criteria->hasAssociation('locationCache')) {
            $criteria->removeAssociation('locationCache');
            $request->attributes->set('order', 'standard');
        }

        $criteria->addExtension('filters', $filters);
    }

    private function getAggregations(Request $request, FilterCollection $filters): array
    {
        $aggregations = [];

        if ($request->query->get('reduce-aggregations') === null) {
            foreach ($filters as $filter) {
                $aggregations = array_merge($aggregations, $filter->getAggregations());
            }

            return $aggregations;
        }

        foreach ($filters as $filter) {
            $excluded = $filters->filtered();

            if ($filter->exclude()) {
                $excluded = $excluded->blacklist($filter->getName());
            }

            foreach ($filter->getAggregations() as $aggregation) {
                if ($aggregation instanceof FilterAggregation) {
                    $aggregation->addFilters($excluded->getFilters());

                    $aggregations[] = $aggregation;

                    continue;
                }

                $aggregation = new FilterAggregation(
                    $aggregation->getName(),
                    $aggregation,
                    $excluded->getFilters()
                );

                $aggregations[] = $aggregation;
            }
        }

        return $aggregations;
    }

    private function handlePagination(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        $limit = $this->getLimit($request);
        $page = $this->getPage($request);
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setLimit($limit);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
    }

    private function handleSorting(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        if ($criteria->getSorting()) {
            return;
        }

        $sortings = $this->sortingService->getAvailableSortings(
            $this->entityName,
            $context->getContext()
        );
        $currentSorting = $this->getCurrentSorting($sortings, $request);
        $criteria->addSorting(...$currentSorting->createDalSorting());
        $criteria->addExtension('sortings', $sortings);
    }

    private function getCurrentSorting(SortingCollection $sortings, Request $request): ProductSortingEntity
    {
        $key = $request->query->get('order', self::DEFAULT_SEARCH_SORT);
        $sorting = $sortings->getByKey($key);
        if ($sorting !== null) {
            return $sorting;
        }
        return $sortings->first();
    }

    private function addCurrentFilters(ProductListingResultEvent $event): void
    {
        $result = $event->getResult();
        $filters = $result->getCriteria()->getExtension('filters');
        if (!$filters instanceof FilterCollection) {
            return;
        }

        foreach ($filters as $filter) {
            /* Handle "my location" if radius filter active */
            if ($filter->getName() === 'radius') {
                $values = $filter->getValues();

                if (isset($values['locationLat'])) {
                    $me = new LocationStruct();
                    $me->setLocationLat((float) $values['locationLat']);
                    $me->setLocationLon((float) $values['locationLon']);
                    $me->setLocationDistanceUnit((string) $values['unit']);
                    $me->__set('locationDistance', (float) $values['distance']);

                    $result->addExtension('me', $me);
                }
            }

            $result->addCurrentFilter($filter->getName(), $filter->getValues());
        }
    }

    private function getLimit(Request $request): int
    {
        $limit = $request->query->getInt('moorl_limit', 0);
        if ($request->isMethod(Request::METHOD_POST)) {
            $limit = $request->request->getInt('moorl_limit', $limit);
        }
        return $limit <= 0 ? 12 : $limit;
    }

    private function getPage(Request $request): int
    {
        $page = $request->query->getInt('p', 1);
        if ($request->isMethod(Request::METHOD_POST)) {
            $page = $request->request->getInt('p', $page);
        }
        return $page <= 0 ? 1 : $page;
    }

    protected function getFilters(Request $request, SalesChannelContext $context): FilterCollection
    {
        return new FilterCollection();
    }

    protected function getTagFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "tag");

        return new Filter(
            'tag',
            !empty($ids),
            [new EntityAggregation('tag', $this->entityName . '.tags.id', 'tag')],
            new EqualsAnyFilter($this->entityName . '.tags.id', $ids),
            $ids
        );
    }

    protected function getProductStreamFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "product-stream");

        return new Filter(
            'product-stream',
            !empty($ids),
            [new EntityAggregation('product-stream', $this->entityName . '.streams.id', 'product_stream')],
            new EqualsAnyFilter($this->entityName . '.streams.id', $ids),
            $ids
        );
    }

    protected function getManufacturerFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "manufacturer");

        return new Filter(
            'manufacturer',
            !empty($ids),
            [new EntityAggregation('manufacturer', $this->entityName . '.productManufacturers.id', 'product_manufacturer')],
            new EqualsAnyFilter($this->entityName . '.productManufacturers.id', $ids),
            $ids
        );
    }

    protected function getChildCategoryFilter(Request $request, string $navigationId): Filter
    {
        $ids = $this->getPropIds($request, "child-category");

        return new Filter(
            'child-category',
            !empty($ids),
            [new FilterAggregation(
                'child-category',
                new EntityAggregation('child-category', $this->entityName . '.categories.id', 'category'),
                [
                    new ContainsFilter($this->entityName . '.categories.path', $navigationId)
                ],
            )],
            new EqualsAnyFilter($this->entityName . '.categories.id', $ids),
            $ids
        );
    }

    protected function getNavigationFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "navigation");

        return new Filter(
            'navigation',
            !empty($ids),
            [new EntityAggregation('navigation', $this->entityName . '.categories.id', 'category')],
            new EqualsAnyFilter($this->entityName . '.categories.id', $ids),
            $ids
        );
    }

    protected function getCountryFilter(Request $request, ?array $defaultIds = null): Filter
    {
        $ids = $this->getPropIds($request, "country", $defaultIds);

        return new Filter(
            'country',
            !empty($ids),
            [new EntityAggregation('country', $this->entityName . '.countryId', 'country')],
            new EqualsAnyFilter($this->entityName . '.countryId', $ids),
            $ids
        );
    }

    protected function getTypeFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "type");

        return new Filter(
            'type',
            !empty($ids),
            [new EntityAggregation('type', $this->entityName . '.typeId', $this->entityName . '_type')],
            new EqualsAnyFilter($this->entityName . '.typeId', $ids),
            $ids
        );
    }

    protected function getCustomerFilter(Request $request): Filter
    {
        $ids = $this->getPropIds($request, "customer");

        return new Filter(
            'appflix-ad-customer',
            !empty($ids),
            [],
            new EqualsAnyFilter($this->entityName . '.customerId', $ids),
            $ids
        );
    }

    protected function getFirstCharFilter(Request $request): Filter
    {
        $firstChars = $this->getPropIds($request, "first-char");

        $filter = [];
        if ($firstChars) {
            foreach ($firstChars as $firstChar) {
                $filter[] = new PrefixFilter('name', $firstChar);
            }
        }

        return new Filter(
            'first-char',
            !empty($firstChar),
            [new EntityAggregation('first-char', $this->entityName . '.id', $this->entityName)],
            new MultiFilter(MultiFilter::CONNECTION_OR, $filter),
            [
                'firstChars' => $firstChars
            ]
        );
    }

    protected function getPriceFilter(Request $request): Filter
    {
        $min = $request->query->get('min-price', 0);
        $max = $request->query->get('max-price', 0);

        $range = [];
        if ($min > 0) {
            $range[RangeFilter::GTE] = $min;
        }
        if ($max > 0) {
            $range[RangeFilter::LTE] = $max;
        }

        return new Filter(
            'price',
            !empty($range),
            [new StatsAggregation('price', $this->entityName . '.price', true, true, false, false)],
            new RangeFilter($this->entityName .'.price', $range),
            [
                'min' => (float) $request->query->get('min-price'),
                'max' => (float) $request->query->get('max-price'),
            ]
        );
    }

    protected function getRadiusFilter(Request $request, ?SalesChannelContext $context = null): Filter
    {
        /**
         * km = Kilometer
         * mi = Meilen
         * nm = Nautische Meilen
         */
        $location = $request->query->get('location', '');
        $distance = $request->query->get('distance', 0);
        $unit = $this->locationServiceV2->getUnitOfMeasurement();

        $filter = new EqualsFilter($this->entityName . '.active', true);

        $location = $this->locationServiceV2->getLocationByTerm($location, $this->getPropIds($request, "country"));

        /* If a location was found, write locationCache, add filter and add location to salesChannelContext */
        if ($location) {
            $this->locationServiceV2->writeLocationCache($location, $this->entityName, (float) $distance, $unit);

            $filter = new MultiFilter(MultiFilter::CONNECTION_AND, [
                new RangeFilter($this->entityName . '.locationCache.distance', [RangeFilter::LTE => $distance]),
                new EqualsFilter($this->entityName . '.locationCache.locationId', $location->getId())
            ]);

            $context->addExtension(LocationDefinition::ENTITY_NAME, $location);
        }

        return new Filter(
            'radius',
            !empty($location),
            [new CountAggregation('radius', $this->entityName . '.active')],
            $filter,
            [
                'locationId' => !empty($location) ? $location->getId() : null,
                'distance' => (int) $distance,
                'unit' => $unit,
                'locationLat' => !empty($location) ? $location->getLocationLat() : null,
                'locationLon' => !empty($location) ? $location->getLocationLon() : null
            ]
        );
    }

    protected function getPropIds(Request $request, string $prop = "tag", ?array $defaultIds = null): array
    {
        $ids = $request->query->get($prop);
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get($prop);
        }

        if (\is_string($ids)) {
            $ids = explode('|', $ids);
        }

        if (empty($ids) && !empty($defaultIds)) {
            $ids = $defaultIds;
        }

        return array_filter((array) $ids);
    }
}
