<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product\Cms;

use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;

class ProductBuyListCmsElementResolver extends ProductSliderCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-product-buy-list';
    }
}
