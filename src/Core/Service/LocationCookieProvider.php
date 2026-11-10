<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationCookieProvider implements CookieProviderInterface
{
    public function __construct(
        private readonly CookieProviderInterface $originalService,
        private readonly SystemConfigService $systemConfigService,
        private readonly RequestStack $requestStack
    )
    {
    }

    public function getCookieGroups(): array
    {
        $cookieGroups = $this->originalService->getCookieGroups();

        $salesChannelId = $this->requestStack->getCurrentRequest()?->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        $osmCookieConsent = $this->systemConfigService->get('MoorlFoundation.config.osmCookieConsent', $salesChannelId);
        if (!$osmCookieConsent) {
            return $cookieGroups;
        }

        foreach ($cookieGroups as $groupIndex => $cookieGroup) {
            if ($cookieGroup['snippet_name'] !== 'cookie.groupComfortFeatures') {
                continue;
            }

            $cookieGroups[$groupIndex]['entries'][] = [
                'snippet_name' => 'moorl-location-map.cookieName',
                'snippet_description' => 'moorl-location-map.cookieDescription',
                'cookie' => 'moorl-location-map',
                'value' => '1',
            ];
        }

        return $cookieGroups;
    }
}
