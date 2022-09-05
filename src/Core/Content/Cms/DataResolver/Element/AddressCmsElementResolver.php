<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\AddressStruct;
use Shopware\Core\Framework\Struct\Struct;

class AddressCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-address';
    }

    public function getStruct(): Struct
    {
        return new AddressStruct();
    }
}
