<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityCompanyTrait;
use Shopware\Core\Framework\Struct\Struct;

class CompanyStruct extends Struct
{
    use EntityCompanyTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_company';
    }
}
