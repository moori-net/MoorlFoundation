<?php

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\System\SalesChannelEntitySearchInterface;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SalesChannelEntitySearchService
{
    private Connection $connection;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    private SalesChannelContext $salesChannelContext;
    private Context $context;
    /**
     * @var SalesChannelEntitySearchInterface[]
     */
    private iterable $searchEntities;

    public function __construct(
        Connection $connection,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        iterable $searchEntities
    )
    {
        $this->connection = $connection;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->searchEntities = $searchEntities;

        $this->context = Context::createDefaultContext();
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
            $searchEntity->setSalesChannelContext($salesChannelContext);

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

            $criteria->setTitle($searchEntity->getTitle());

            $searchEntity->processCriteria($criteria);

            if ($searchEntity->getSalesChannelRepository()) {
                $moorlSearchResult = $searchEntity->getSalesChannelRepository()->search($criteria, $salesChannelContext);
            } else {
                $repo = $this->definitionInstanceRegistry->getRepository($searchEntity->getEntityName());
                $moorlSearchResult = $repo->search($criteria, $context);
            }

            if ($moorlSearchResult->getTotal() === 0 && $advancedSearchHideEmptyResults) {
                continue;
            }

            $searchEntity->processSearchResult($moorlSearchResult);

            $moorlSearchResult->assign([
                'snippet' => $searchEntity->getSnippet(),
                'templatePath' => $searchEntity->getTemplatePath(),
            ]);

            $moorlSearchResults[] = $moorlSearchResult;
        }

        $result->assign(['moorlSearchResults' => $moorlSearchResults]);
    }
}
