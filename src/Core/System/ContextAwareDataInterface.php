<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Framework\Context;

/** Optional lifecycle context support for data packages. */
interface ContextAwareDataInterface
{
    public function setContext(Context $context): void;
    public function getContext(): Context;
}
