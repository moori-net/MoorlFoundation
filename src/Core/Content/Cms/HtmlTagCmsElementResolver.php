<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use Shopware\Core\Content\Cms\DataResolver\Element\TextCmsElementResolver;

class HtmlTagCmsElementResolver extends LegacyTextCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-html-tag';
    }
}
