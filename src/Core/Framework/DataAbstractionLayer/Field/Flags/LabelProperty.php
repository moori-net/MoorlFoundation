<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;

class LabelProperty extends Flag
{
    /*
     * @var string|null
     */
    private $labelProperty;

    public function __construct(?string $labelProperty)
    {
        $this->labelProperty = $labelProperty;
    }

    public function parse(): \Generator
    {
        yield 'moorl_label_property' => $this->labelProperty;
    }
}
