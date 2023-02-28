<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class VueComponent extends Flag
{
    public function __construct(private readonly ?string $type = null, private readonly ?array $options = null)
    {
    }

    public function parse(): \Generator
    {
        yield 'moorl_vue_component' => $this->type ?: true;
        yield 'moorl_vue_component_properties' => $this->options;
    }
}
