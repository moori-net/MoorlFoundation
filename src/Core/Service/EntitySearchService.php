<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\System\EntityListingInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class EntitySearchService
{
    protected DefinitionInstanceRegistry $definitionInstanceRegistry;
    protected SystemConfigService $systemConfigService;
    protected EventDispatcherInterface $eventDispatcher;
    /**
     * @var EntityListingInterface[]
     */
    protected iterable $searchEntities;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher,
        iterable $searchEntities
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->searchEntities = $searchEntities;
    }

    public function getEntityListing(Request $request, Context $context): ?EntityListingInterface
    {
        if ($request->get('_route') === "frontend.search.page") {
            $request->query->set('order', 'score');

            return null;
        }

        $slotIds = $request->query->get('slots');
        /* Unset immediately, because its not compatible with product listing */
        $request->query->remove('slots');
        $tab = $request->query->get('tab');
        if (!$slotIds && !$tab) {
            return null;
        }

        if ($tab) {
            foreach ($this->searchEntities as $searchEntity) {
                if ($searchEntity->getTitle() === $tab) {
                    $searchEntity->setSystemConfigService($this->systemConfigService);
                    return $searchEntity;
                }
            }
        }

        if (!$slotIds) {
            return null;
        }

        $slotRepository = $this->definitionInstanceRegistry->getRepository('cms_slot');
        $slotIds = explode('|', $slotIds);
        $criteria = new Criteria($slotIds);
        $criteria->setLimit(count($slotIds));
        /** @var CmsSlotEntity $slot */
        $slots = $slotRepository->search($criteria, $context)->getEntities();

        foreach ($this->searchEntities as $searchEntity) {
            foreach ($slots as $slot) {
                if ($searchEntity->getTitle() === $slot->getType()) {
                    return $searchEntity;
                }
            }

        }
        return null;
    }

    public function enrich(ProductListingResultEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();
        if (!$this->systemConfigService->get('MoorlFoundation.config.advancedSearchActive', $salesChannelContext->getSalesChannelId())) {
            return;
        }

        $advancedSearchHideEmptyResults = $this->systemConfigService->get('MoorlFoundation.config.advancedSearchHideEmptyResults', $salesChannelContext->getSalesChannelId());

        $request = $event->getRequest();
        $search = $request->get('search');
        $result = $event->getResult();
        $context = $salesChannelContext->getContext();
        $moorlSearchResults = [];

        foreach ($this->searchEntities as $searchEntity) {
            $searchEntity->setSystemConfigService($this->systemConfigService);
            $searchEntity->setEvent($event);

            if (!$searchEntity->isActive()) {
                continue;
            }

            if ($searchEntity->inheritCriteria()) {
                if ($result->getTotal() === 0) {
                    continue;
                }

                $criteria = $result->getCriteria();
            } else {
                $criteria = new Criteria();
                $criteria->setLimit($searchEntity->getLimit());
                $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
                $criteria->setTerm($searchEntity->getTerm($search));
            }

            $searchEntity->setEventDispatcher($this->eventDispatcher);
            $searchEntity->processCriteria($criteria);

            $criteria->setTitle($searchEntity->getTitle());

            if ($searchEntity->getSalesChannelRepository()) {
                $moorlSearchResult = $searchEntity->getSalesChannelRepository()->search($criteria, $salesChannelContext);
            } else {
                $repo = $this->definitionInstanceRegistry->getRepository($searchEntity->getEntityName());
                $moorlSearchResult = $repo->search($criteria, $context);
            }

            if ($moorlSearchResult->getTotal() === 0 && $advancedSearchHideEmptyResults) {
                continue;
            }

            /** @var ProductListingResult $moorlSearchResult */
            $moorlSearchResult = ProductListingResult::createFrom($moorlSearchResult);
            if ($searchEntity->inheritCriteria() && $event instanceof ProductSearchResultEvent) {
                $moorlSearchResult->setAvailableSortings($result->getAvailableSortings());
                $moorlSearchResult->setSorting($result->getSorting());
            }

            $searchEntity->processSearchResult($moorlSearchResult);

            $moorlSearchResult->assign([
                'snippet' => $searchEntity->getSnippet(),
                'templatePath' => $searchEntity->getTemplatePath(),
                'elementConfig' => $searchEntity->getElementConfig()
            ]);

            $moorlSearchResults[] = $moorlSearchResult;
        }

        $result->assign(['moorlSearchResults' => $moorlSearchResults]);
    }
}
