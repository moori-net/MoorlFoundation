<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

/** @deprecated */
class LabelProperty extends Flag
{
    public function __construct(private readonly ?string $labelProperty)
    {
    }

    public function parse(): \Generator
    {
        yield 'moorl_label_property' => $this->labelProperty;
    }
}
