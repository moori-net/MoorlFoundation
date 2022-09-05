<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\ContactStruct;
use Shopware\Core\Framework\Struct\Struct;

class ContactCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-contact';
    }

    public function getStruct(): Struct
    {
        return new ContactStruct();
    }
}
