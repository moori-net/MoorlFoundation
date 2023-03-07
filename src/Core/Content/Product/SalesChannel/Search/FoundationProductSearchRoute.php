<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\SalesChannel\Search;

use MoorlFoundation\Core\Service\EntitySearchService;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FoundationProductSearchRoute extends AbstractProductSearchRoute
{
    private AbstractProductSearchRoute $decorated;
    private EventDispatcherInterface $dispatcher;
    private EntitySearchService $searchService;

    public function __construct(
        AbstractProductSearchRoute $decorated,
        EntitySearchService $searchService,
        EventDispatcherInterface $dispatcher
    ) {
        $this->decorated = $decorated;
        $this->dispatcher = $dispatcher;
        $this->searchService = $searchService;
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $entityListing = $this->searchService->getEntityListing($request, $context->getContext());
        if ($entityListing && $entityListing->getEntityName() !== SalesChannelProductDefinition::ENTITY_NAME) {
            $entityListing->setEventDispatcher($this->dispatcher);
            $entityListing->setRequest($request);
            $entityListing->setSalesChannelContext($context);

            $result = $entityListing->listingRoute($criteria)->getResult();
            $result = ProductListingResult::createFrom($result);
            $result->addCurrentFilter('search', $request->query->get('search'));

            return new ProductSearchRouteResponse($result);
        }

        return $this->decorated->load($request, $context, $criteria);
    }
}
