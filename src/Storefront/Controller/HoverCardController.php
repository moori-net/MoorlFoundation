<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class HoverCardController extends StorefrontController
{
    public const WHITELIST = [
        'product',
        'category'
    ];

    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry
    )
    {
    }

    #[Route(path: '/moorl-hover-card/{entityName}/{entityId}', name: 'frontend.moorl-hover-card', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function hoverCard(string $entityName, string $entityId, SalesChannelContext $salesChannelContext, Request $request): Response
    {
        if (!in_array($entityName, self::WHITELIST)) {
            throw new \HttpInvalidParamException();
        }

        if (!Uuid::isValid($entityId)) {
            throw new \HttpInvalidParamException();
        }

        $criteria = new Criteria([$entityId]);

        switch ($entityName) {
            case "product":
                $entityItem = $this->productRepository->search($criteria, $salesChannelContext)->get($entityId);
                break;
            default:
                $repository = $this->definitionInstanceRegistry->getRepository($entityName);
                $entityItem = $repository->search($criteria, $salesChannelContext->getContext())->get($entityId);

        }
    }
}
