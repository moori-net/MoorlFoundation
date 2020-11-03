<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class EditField extends Flag
{
    /*
     * @var string|null
     */
    private $type;

    public function __construct(?string $type = null)
    {
        $this->type = $type;
    }

    public function parse(): \Generator
    {
        yield 'moorl_edit_field' => $this->type ?: true;
    }
}
