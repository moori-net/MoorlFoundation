<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

interface EntityTranslationInterface
{
    public function getConfigKey(): string;
    public function getEntityName(): string;
}
