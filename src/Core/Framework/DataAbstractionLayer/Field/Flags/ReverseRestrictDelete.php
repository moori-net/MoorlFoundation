<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

/** @deprecated */
class ReverseRestrictDelete extends Flag
{
    public function parse(): \Generator
    {
        yield 'reverse_restrict_delete' => true;
    }
}
