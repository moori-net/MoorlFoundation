<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;
use Shopware\Core\Framework\Log\Package;

#[Package('framework')]
class ReverseRestrictDelete extends Flag
{
    public function parse(): \Generator
    {
        yield 'reverse_restrict_delete' => true;
    }
}
