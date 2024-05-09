<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

interface EntityAutoCacheInterface
{
    public function getEntityAutoCacheOptions(): array;
    public function getEntityName(): string;
}
