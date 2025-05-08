<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\InvalidParameterException;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ModalController extends StorefrontController
{
    #[Route(path: '/moorl-modal/embedded-media/{embeddedMediaId}', name: 'frontend.moorl-modal.embedded-media', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function mediaModal(string $embeddedMediaId, SalesChannelContext $salesChannelContext): Response
    {
        if (!Uuid::isValid($embeddedMediaId)) {
            throw new InvalidParameterException('Invalid parameter "embeddedMediaId"');
        }

        $repository = $this->container->get('moorl_media.repository');
        $criteria = new Criteria([$embeddedMediaId]);

        $embeddedMedia = $repository->search($criteria, $salesChannelContext->getContext())->get($embeddedMediaId);
        if (!$embeddedMedia) {
            throw new InvalidParameterException('Invalid parameter "embeddedMediaId"');
        }

        $body = $this->renderView(
            '@MoorlFoundation/plugin/moorl-foundation/component/embedded-media/index.html.twig',
            [
                'embeddedMedia' => $embeddedMedia
            ]
        );

        return $this->renderStorefront(
            '@MoorlFoundation/plugin/moorl-foundation/modal.html.twig',
            [
                'modal' => [
                    'title' => $embeddedMedia->getTranslation('name'),
                    'size' => 'sm',
                    'body' => $body
                ]
            ]
        );
    }
}
