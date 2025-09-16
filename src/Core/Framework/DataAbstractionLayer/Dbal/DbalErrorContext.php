<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\Connection;

final class DbalErrorContext
{
    public function __construct(
        public Connection $connection,
        public ?string $sql = null,
        public ?string $table = null,
        public ?string $column = null,
        public array $codes = [],
        public array $ids = [],
    ) {}
}
