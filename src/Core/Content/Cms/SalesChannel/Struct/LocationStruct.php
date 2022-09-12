<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityLocationTrait;
use Shopware\Core\Framework\Struct\Struct;

class LocationStruct extends Struct
{
    use EntityLocationTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_location';
    }
}
