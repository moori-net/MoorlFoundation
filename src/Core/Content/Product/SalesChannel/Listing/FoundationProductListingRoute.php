<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\SalesChannel\Listing;

use MoorlFoundation\Core\Service\EntitySearchService;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class FoundationProductListingRoute extends AbstractProductListingRoute
{
    public function __construct(private readonly AbstractProductListingRoute $decorated, private readonly EntitySearchService $searchService, private readonly EventDispatcherInterface $dispatcher)
    {
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

            return $entityListing->listingRoute($criteria, $categoryId);
        }

        return $this->decorated->load($categoryId, $request, $context, $criteria);
    }
}
