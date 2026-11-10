<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Service;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
require_once dirname(__DIR__, 3) . '/src/Core/Service/LocationCookieProvider.php';

use MoorlFoundation\Core\Service\LocationCookieProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationCookieProviderTest extends TestCase
{
    private const GROUPS = [
        ['snippet_name' => 'cookie.groupComfortFeatures', 'entries' => []],
    ];

    public function testUsesTheCurrentSalesChannelForEachRequest(): void
    {
        $enabledChannelId = str_repeat('a', 32);
        $disabledChannelId = str_repeat('b', 32);
        $requestStack = new RequestStack();
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::exactly(2))->method('get')
            ->willReturnCallback(static function (string $key, ?string $salesChannelId = null) use ($enabledChannelId, $disabledChannelId): bool {
                self::assertSame('MoorlFoundation.config.osmCookieConsent', $key);
                self::assertContains($salesChannelId, [$enabledChannelId, $disabledChannelId]);

                return $salesChannelId === $enabledChannelId;
            });
        $provider = $this->createProvider($systemConfig, $requestStack);

        $requestStack->push(new Request(attributes: [PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID => $enabledChannelId]));
        $groups = $provider->getCookieGroups();
        self::assertSame('moorl-location-map', $groups[0]['entries'][0]['cookie']);
        $requestStack->pop();

        $requestStack->push(new Request(attributes: [PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID => $disabledChannelId]));
        self::assertSame(self::GROUPS, $provider->getCookieGroups());
    }

    public function testFallsBackToGlobalConfigurationWithoutARequest(): void
    {
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::once())->method('get')
            ->with('MoorlFoundation.config.osmCookieConsent', null)->willReturn(true);

        $groups = $this->createProvider($systemConfig, new RequestStack())->getCookieGroups();

        self::assertSame('moorl-location-map', $groups[0]['entries'][0]['cookie']);
    }

    public function testFallsBackToGlobalConfigurationWithoutASalesChannel(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $systemConfig = $this->createMock(SystemConfigService::class);
        $systemConfig->expects(self::once())->method('get')
            ->with('MoorlFoundation.config.osmCookieConsent', null)->willReturn(false);

        self::assertSame(self::GROUPS, $this->createProvider($systemConfig, $requestStack)->getCookieGroups());
    }

    private function createProvider(SystemConfigService $systemConfig, RequestStack $requestStack): LocationCookieProvider
    {
        $original = $this->createMock(CookieProviderInterface::class);
        $original->method('getCookieGroups')->willReturn(self::GROUPS);

        return new LocationCookieProvider($original, $systemConfig, $requestStack);
    }
}
