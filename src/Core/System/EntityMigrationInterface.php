<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

interface EntityMigrationInterface
{
    public function getMigrationFields(): FieldCollection;
}
