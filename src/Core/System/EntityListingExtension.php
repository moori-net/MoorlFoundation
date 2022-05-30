<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityListingExtension implements EntityListingInterface
{
    protected EntityDefinition $entityDefinition;
    protected SystemConfigService $systemConfigService;
    protected SalesChannelContext $salesChannelContext;
    protected ?ProductListingResultEvent $event = null;
    protected EventDispatcherInterface $eventDispatcher;
    protected Request $request;
    protected ?SalesChannelRepositoryInterface $salesChannelRepository = null;
    protected ?Filter $filter = null;
    protected ?string $route;

    public function __construct(
        ?SalesChannelRepositoryInterface $salesChannelRepository = null
    )
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function isWidget(): bool
    {
        return in_array($this->route, [
            "widgets.search.filter",
            "widgets.search.pagelet.v2",
        ]);
    }

    public function isSearch(): bool
    {
        return in_array($this->route, [
            "frontend.search.page",
            "widgets.search.pagelet.v2"
        ]);
    }

    public function isSuggest(): bool
    {
        return in_array($this->route, [
            "frontend.search.suggest"
        ]);
    }

    public function getElementConfig(): array
    {
        if ($this->isSuggest()) {
            return [
                'listingLayout' => ['value' => 'search-suggest']
            ];
        }

        return [
            'listingSource' => ['value' => 'auto'],
            'listingLayout' => ['value' => 'grid'],
            'itemLayout' => ['value' => 'overlay'],
            'displayMode' => ['value' => 'cover'],
            'textAlign' => ['value' => 'left'],
            'gapSize' => ['value' => '20px'],
            'itemWidth' => ['value' => '300px'],
            'itemHeight' => ['value' => '400px'],
            'itemHasBorder' => ['value' => false],
            'contentPadding' => ['value' => '20px'],
            'hasButton' => ['value' => true],
            'buttonClass' => ['value' => 'btn btn-dark'],
            'buttonLabel' => ['value' => null],
        ];
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
        $this->route = $request->get('_route');
    }

    /**
     * @param ProductListingResultEvent $event
     */
    public function setEvent(ProductListingResultEvent $event): void
    {
        $this->event = $event;
        $this->salesChannelContext = $event->getSalesChannelContext();
        $this->setRequest($event->getRequest());
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return SalesChannelRepositoryInterface|null
     */
    public function getSalesChannelRepository(): ?SalesChannelRepositoryInterface
    {
        return $this->salesChannelRepository;
    }

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function setSystemConfigService(SystemConfigService $systemConfigService): void
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getEntityName(): string
    {
        return "";
    }

    public function getTitle(): string
    {
        return "";
    }

    public function processSearchResult(ProductListingResult $searchResult): void
    {
    }

    public function processCriteria(Criteria $criteria): void
    {
    }

    public function isActive(): bool
    {
        return true;
    }

    public function inheritCriteria(): bool
    {
        return false;
    }

    public function getLimit(): int
    {
        return 6;
    }

    public function getTerm(string $search): string
    {
        return $search;
    }

    public function getSnippet(): ?string
    {
        return null;
    }

    public function getTemplatePath(): ?string
    {
        return null;
    }

    public function listingLoader(Criteria $origin): EntitySearchResult
    {
        $criteria = clone $origin;

        $this->salesChannelContext->getContext()->addState(Context::STATE_ELASTICSEARCH_AWARE);
        $ids = $this->salesChannelRepository->searchIds($criteria, $this->salesChannelContext);

        $aggregations = $this->salesChannelRepository->aggregate($criteria, $this->salesChannelContext);
        if (empty($ids->getIds())) {
            return new EntitySearchResult(
                $this->getEntityName(),
                0,
                new EntityCollection(),
                $aggregations,
                $origin,
                $this->salesChannelContext->getContext()
            );
        }

        $entities = $this->salesChannelRepository->search($criteria, $this->salesChannelContext);
        $result = new EntitySearchResult($this->getEntityName(), $ids->getTotal(), $entities->getEntities(), $aggregations, $origin, $this->salesChannelContext->getContext());
        $result->addState(...$ids->getStates());

        return $result;
    }

    public function listingRoute(Criteria $criteria, ?string $categoryId = null): ProductListingRouteResponse
    {
        $this->processCriteria($criteria);
        $criteria->setTitle($this->getTitle());

        $entities = $this->listingLoader($criteria);

        /** @var ProductListingResult $result */
        $result = ProductListingResult::createFrom($entities);
        $result->addState(...$entities->getStates());

        $result->assign([
            'elementConfig' => $this->getElementConfig()
        ]);

        $result->addCurrentFilter('navigationId', $categoryId);

        $this->processSearchResult($result);

        return new ProductListingRouteResponse($result);
    }
}
