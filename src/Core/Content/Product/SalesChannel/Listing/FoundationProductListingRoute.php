<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\SalesChannel\Listing;

use MoorlFoundation\Core\Service\EntitySearchService;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @RouteScope(scopes={"store-api"})
 */
class FoundationProductListingRoute extends AbstractProductListingRoute
{
    private AbstractProductListingRoute $decorated;
    private EventDispatcherInterface $dispatcher;
    private EntitySearchService $searchService;

    public function __construct(
        AbstractProductListingRoute $decorated,
        EntitySearchService $searchService,
        EventDispatcherInterface $dispatcher
    ) {
        $this->decorated = $decorated;
        $this->dispatcher = $dispatcher;
        $this->searchService = $searchService;
    }

    public function getDecorated(): AbstractProductListingRoute
    {
        return $this->decorated;
    }

    public function load(string $categoryId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $entityListing = $this->searchService->getEntityListing($request, $context->getContext());

        if ($entityListing) {
            $entityListing->setEventDispatcher($this->dispatcher);
            $entityListing->setRequest($request);
            $entityListing->setSalesChannelContext($context);

            return $entityListing->listingRoute($criteria, $categoryId);;
        }

        return $this->decorated->load($categoryId, $request, $context, $criteria);
    }
}
