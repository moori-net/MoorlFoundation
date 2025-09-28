<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Vies;

interface ViesClientInterface {
    public function checkVat(string $cc, string $num): bool;
}
