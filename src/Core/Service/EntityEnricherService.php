<?php

namespace MoorlFoundation\Core\Service;

use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class EntityEnricherService
{
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    private AbstractProductPriceCalculator $productPriceCalculator;
    private UrlGeneratorInterface $urlGenerator;

    private ?Context $context = null;
    private ?SalesChannelContext $salesChannelContext = null;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        AbstractProductPriceCalculator $productPriceCalculator,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Context|null $context
     */
    public function setContext(?Context $context): void
    {
        $this->context = $context;
    }

    public function enrichSalesChannelProduct(?SalesChannelProductEntity $product = null): void
    {
        if (!$product) {
            return;
        }

        if (!$this->salesChannelContext) {
            return;
        }

        $this->productPriceCalculator->calculate([$product], $this->salesChannelContext);

        $cover = $product->getCover();
        if (!$cover || !$cover->getMedia()) {
            return;
        }

        $this->enrichMedia($cover->getMedia());
    }

    public function enrichMedia(?MediaEntity $media = null): void
    {
        if (!$media) {
            return;
        }

        if ($media->getMediaTypeRaw()) {
            $media->setMediaType(unserialize($media->getMediaTypeRaw()));
        }

        if ($media->getThumbnails() === null) {
            if ($media->getThumbnailsRo()) {
                $media->setThumbnails(unserialize($media->getThumbnailsRo()));
            } else {
                $media->setThumbnails(new MediaThumbnailCollection());
            }
        }

        if (!$media->hasFile() || $media->isPrivate()) {
            return;
        }

        $media->setUrl($this->urlGenerator->getAbsoluteMediaUrl($media));

        foreach ($media->getThumbnails() as $thumbnail) {
            $this->addThumbnailUrl($thumbnail, $media);
        }
    }

    private function addThumbnailUrl(MediaThumbnailEntity $thumbnail, MediaEntity $media): void
    {
        $thumbnail->setUrl(
            $this->urlGenerator->getAbsoluteThumbnailUrl(
                $media,
                $thumbnail
            )
        );
    }

    /**
     * @param SalesChannelContext|null $salesChannelContext
     */
    public function setSalesChannelContext(?SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->context = $salesChannelContext->getContext();
    }
}
