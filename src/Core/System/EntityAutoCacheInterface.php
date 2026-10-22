<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

interface EntityAutoCacheInterface
{
    /** START_TIME and END_TIME accept a column name or a list of column names. */
    public function getEntityAutoCacheOptions(): array;
    public function getEntityName(): string;
}
