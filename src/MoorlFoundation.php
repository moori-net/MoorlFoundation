<?php declare(strict_types=1);

namespace MoorlFoundation;

use MoorlFoundation\MoorlPlugin as Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class MoorlFoundation extends Plugin
{
    public const FEED_URL = [
        'https://demo-shop.moorleiche.com',
        'http://dev.rh-webdesign.com'
    ];

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        $this->dropTables([
            'moorl_foundation_article'
        ]);
    }
}