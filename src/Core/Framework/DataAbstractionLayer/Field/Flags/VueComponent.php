<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class VueComponent extends Flag
{
    private ?string $type;
    private ?array $options;

    public function __construct(?string $type = null, ?array $options = null)
    {
        $this->type = $type;
        $this->options = $options;
    }

    public function parse(): \Generator
    {
        yield 'moorl_vue_component' => $this->type ?: true;
        yield 'moorl_vue_component_properties' => $this->options;
    }
}
