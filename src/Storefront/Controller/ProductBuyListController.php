<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Cache\Annotation\HttpCache;
use Shopware\Storefront\Page\Product\Configurator\ProductCombinationFinder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class ProductBuyListController extends StorefrontController
{
    private SalesChannelRepositoryInterface $productRepository;
    private ProductConfiguratorLoader $configuratorLoader;
    private ProductCombinationFinder $combinationFinder;

    public function __construct(
        SalesChannelRepositoryInterface $productRepository,
        ProductConfiguratorLoader $configuratorLoader,
        ProductCombinationFinder $combinationFinder
    ) {
        $this->productRepository = $productRepository;
        $this->configuratorLoader = $configuratorLoader;
        $this->combinationFinder = $combinationFinder;
    }

    /**
     * @HttpCache()
     * @Route("/moorl-product-buy-list/{productId}/switch", name="moorl.product.buy.list.switch", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function switch(string $productId, SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $switchedOption = $request->query->has('switched') ? (string) $request->query->get('switched') : null;

        $options = (string) $request->query->get('options');

        try {
            $newOptions = json_decode($options, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            $newOptions = [];
        }

        try {
            $redirect = $this->combinationFinder->find($productId, $switchedOption, $newOptions, $salesChannelContext);

            $productId = $redirect->getVariantId();
        } catch (ProductNotFoundException $productNotFoundException) {
            //nth
        }

        $criteria = new Criteria([$productId]);

        /** @var SalesChannelProductEntity $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext)->get($productId);
        $product->setSortedProperties($this->configuratorLoader->load($product, $salesChannelContext));

        return $this->renderStorefront('@Storefront/plugin/moorl-foundation/component/product-buy-list/product-item.html.twig', [
            'product' => $product,
            'enablePrices' => $request->query->getBoolean('enablePrices'),
            'enableAddToCart' => $request->query->getBoolean('enableAddToCart')
        ]);
    }
}
