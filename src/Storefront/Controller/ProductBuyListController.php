<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\FindVariant\AbstractFindProductVariantRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductBuyListController extends StorefrontController
{
    public function __construct(
        private readonly SalesChannelRepository          $productRepository,
        private readonly ProductConfiguratorLoader       $configuratorLoader,
        private readonly AbstractFindProductVariantRoute $findProductVariantRoute
    )
    {
    }

    #[Route(path: '/moorl-product-buy-list/{productId}/switch', name: 'moorl.product.buy.list.switch', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function switch(string $productId, SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $switchedGroup = $request->query->has('switched') ? (string)$request->query->get('switched') : null;
        /** @var array<mixed>|null $options */
        $options = json_decode($request->query->get('options', ''), true);

        try {
            $redirect = $this->findProductVariantRoute->load(
                $productId,
                new Request([
                    'switchedGroup' => $switchedGroup,
                    'options' => $options ?? [],
                ]),
                $salesChannelContext);

            $productId = $redirect->getFoundCombination()->getVariantId();
        } catch (ProductNotFoundException) {
        }

        $criteria = new Criteria([$productId]);

        /** @var SalesChannelProductEntity $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext)->get($productId);
        $product->setSortedProperties($this->configuratorLoader->load($product, $salesChannelContext));

        return $this->renderStorefront('@MoorlFoundation/plugin/moorl-foundation/component/product-buy-list/product-item.html.twig', [
            'product' => $product,
            'enablePrices' => $request->query->getBoolean('enablePrices'),
            'enableAddToCartSingle' => $request->query->getBoolean('enableAddToCartSingle'),
            'enableAddToCartAll' => $request->query->getBoolean('enableAddToCartAll'),
        ]);
    }
}
