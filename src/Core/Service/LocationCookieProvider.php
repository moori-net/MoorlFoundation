<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class LocationCookieProvider implements CookieProviderInterface
{
    public function __construct(
        private readonly CookieProviderInterface $originalService,
        private readonly SystemConfigService $systemConfigService
    )
    {
    }

    public function getCookieGroups(): array
    {
        $cookieGroups = $this->originalService->getCookieGroups();

        $osmCookieConsent = $this->systemConfigService->get('MoorlFoundation.config.osmCookieConsent');
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
