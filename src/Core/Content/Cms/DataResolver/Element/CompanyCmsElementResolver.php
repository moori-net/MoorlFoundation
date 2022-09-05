<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\CompanyStruct;
use Shopware\Core\Framework\Struct\Struct;

class CompanyCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-company';
    }

    public function getStruct(): Struct
    {
        return new CompanyStruct();
    }
}
