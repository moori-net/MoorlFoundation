<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class EditField extends Flag
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
        yield 'moorl_edit_field' => $this->type ?: true;
        yield 'moorl_edit_field_options' => $this->options;
    }
}
