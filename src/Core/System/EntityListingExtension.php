<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityListingExtension implements EntityListingInterface
{
    public const CONFIG_TYPE = [
        'searchActive' => 'bool',
        'suggestActive' => 'bool',
        'searchConfigActive' => 'bool',
        'suggestConfigActive' => 'bool',
        'searchLimit' => 'int',
        'suggestLimit' => 'int',
        'searchConfig' => 'array',
        'suggestConfig' => 'array',
    ];
    public const CONFIG_DEFAULT_SEARCH = [
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
    public const CONFIG_DEFAULT_SUGGEST = [
        'listingLayout' => ['value' => 'search-suggest']
    ];

    protected EntityDefinition $entityDefinition;
    protected ?SystemConfigService $systemConfigService = null;
    protected SalesChannelContext $salesChannelContext;
    protected ?ProductListingResultEvent $event = null;
    protected EventDispatcherInterface $eventDispatcher;
    protected Request $request;
    protected ?Filter $filter = null;
    protected ?string $route = null;
    /* @noRector $salesChannelRepository must not be accessed before initialization */
    protected ?SalesChannelRepository $salesChannelRepository = null;

    public function __construct(
        ?SalesChannelRepository $salesChannelRepository = null,
        ?SystemConfigService $systemConfigService = null
    )
    {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->systemConfigService = $systemConfigService;
    }

    public function getPluginName(): ?string
    {
        return null;
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
        if ($this->getPluginName()) {
            if ($this->isSearch() && $this->getConfig('searchConfigActive')) {
                return $this->getConfig('searchConfig');
            } elseif ($this->isSuggest() && $this->getConfig('suggestConfigActive')) {
                return $this->getConfig('suggestConfig');
            }
        }

        if ($this->isSuggest()) {
            return self::CONFIG_DEFAULT_SUGGEST;
        }

        return self::CONFIG_DEFAULT_SEARCH;
    }

    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
        $this->route = $request->attributes->get('_route');
    }

    public function setEvent(ProductListingResultEvent $event): void
    {
        $this->event = $event;
        $this->salesChannelContext = $event->getSalesChannelContext();
        $this->setRequest($event->getRequest());
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getSalesChannelRepository(): ?SalesChannelRepository
    {
        return $this->salesChannelRepository;
    }

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
        if ($this->getPluginName()) {
            if ($this->isSearch()) {
                return $this->getConfig('searchActive');
            } elseif ($this->isSuggest()) {
                return $this->getConfig('suggestActive');
            }
        }

        return true;
    }

    public function inheritCriteria(): bool
    {
        return false;
    }

    public function getLimit(): int
    {
        if ($this->getPluginName()) {
            if ($this->isSearch()) {
                return $this->getConfig('searchLimit');
            } elseif ($this->isSuggest()) {
                return $this->getConfig('suggestLimit');
            }
        }

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

        $this->salesChannelContext->getContext()->addState(Criteria::STATE_ELASTICSEARCH_AWARE);
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

        $result = ProductListingResult::createFrom($entities);
        $result->addState(...$entities->getStates());

        $result->assign([
            'elementConfig' => $this->getElementConfig()
        ]);

        $result->addCurrentFilter('navigationId', $categoryId);

        $this->processSearchResult($result);

        return new ProductListingRouteResponse($result);
    }

    private function getConfig(string $key): mixed
    {
        $configKey = sprintf("%s.config.%s", $this->getPluginName(), $key);

        if (self::CONFIG_TYPE[$key] === 'bool') {
            return $this->systemConfigService->getBool($configKey, $this->salesChannelContext->getSalesChannelId());
        } elseif (self::CONFIG_TYPE[$key] === 'int') {
            return $this->systemConfigService->getInt($configKey, $this->salesChannelContext->getSalesChannelId());
        } else {
            $configValue = $this->systemConfigService->get($configKey, $this->salesChannelContext->getSalesChannelId());

            if ($key === 'searchConfig') {
                if (is_array($configValue)) {
                    return array_merge(self::CONFIG_DEFAULT_SEARCH, $configValue);
                } else {
                    return self::CONFIG_DEFAULT_SEARCH;
                }
            }

            if ($key === 'suggestConfig') {
                if (is_array($configValue)) {
                    return array_merge(self::CONFIG_DEFAULT_SUGGEST, $configValue);
                } else {
                    return self::CONFIG_DEFAULT_SUGGEST;
                }
            }

            return $configValue;
        }
    }
}
