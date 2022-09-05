<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\PersonStruct;
use Shopware\Core\Framework\Struct\Struct;

class PersonCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-person';
    }

    public function getStruct(): Struct
    {
        return new PersonStruct();
    }
}
