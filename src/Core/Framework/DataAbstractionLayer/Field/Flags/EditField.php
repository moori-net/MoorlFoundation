<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class EditField extends Flag
{
    public const TEXT = 'text';
    public const TEXTAREA = 'textarea';
    public const NUMBER = 'number';
    public const MULTI_ID = 'multi-id';

    public function __construct(private readonly ?string $type = null, private readonly ?array $options = null)
    {
    }

    public function parse(): \Generator
    {
        yield 'moorl_edit_field' => $this->type ?: true;
        yield 'moorl_edit_field_options' => $this->options;
    }
}
