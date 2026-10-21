<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\DataResolver\Element;

use MoorlFoundation\Core\Content\Cms\DataResolver\FoundationCmsElementResolver;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;

class SocialLinksCmsElementResolver extends FoundationCmsElementResolver
{
    public function getType(): string
    {
        return 'moorl-social-links';
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        parent::enrich($slot, $resolverContext, $result);
        $data = $slot->getData();
        $links = $data->get('socialLinks');
        $validLinks = [];
        foreach (is_array($links) ? $links : [] as $link) {
            if (!is_array($link) || !is_string($link['url'] ?? null) || !is_string($link['label'] ?? null) || !is_string($link['username'] ?? '')) {
                continue;
            }
            $url = trim($link['url']);
            if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
                continue;
            }
            $validLinks[] = ['label' => $link['label'], 'username' => $link['username'] ?? '', 'url' => $url];
        }
        $data->set('socialLinks', $validLinks);
    }
}
