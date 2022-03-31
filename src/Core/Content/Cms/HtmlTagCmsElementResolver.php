<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use Shopware\Core\Content\Cms\DataResolver\Element\TextCmsElementResolver;
use Shopware\Core\Framework\Util\HtmlSanitizer;

class HtmlTagCmsElementResolver extends TextCmsElementResolver
{
    private HtmlSanitizer $sanitizer;

    public function __construct(HtmlSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function getType(): string
    {
        return 'moorl-html-tag';
    }
}
