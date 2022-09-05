<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityAddressTrait;
use Shopware\Core\Framework\Struct\Struct;

class AddressStruct extends Struct
{
    use EntityAddressTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_address';
    }
}
