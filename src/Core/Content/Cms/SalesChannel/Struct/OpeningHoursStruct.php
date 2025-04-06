<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityOpeningHoursTrait;
use Shopware\Core\Framework\Struct\Struct;

class OpeningHoursStruct extends Struct
{
    use EntityOpeningHoursTrait;

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_opening_hours';
    }
}
