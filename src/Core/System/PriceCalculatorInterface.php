<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

interface PriceCalculatorInterface
{
    public function getPriority(): int;
    public function shouldBreak(): bool;
}
