<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class UniqueKey extends Flag
{
    final public const DEFAULT = 'id';

    public function __construct(protected string $key)
    {
    }

    public function parse(): \Generator
    {
        yield 'unique_key' => $this->key;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
