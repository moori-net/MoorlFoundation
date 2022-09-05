<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityContactTrait;
use Shopware\Core\Framework\Struct\Struct;

class ContactStruct extends Struct
{
    use EntityContactTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_contact';
    }
}
