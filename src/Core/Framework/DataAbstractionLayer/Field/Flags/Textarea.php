<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

/** @deprecated */
class Textarea extends Flag
{
    public function parse(): \Generator
    {
        yield 'moorl_textarea' => true;
    }
}
