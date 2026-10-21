<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Content\Cms;

require_once dirname(__DIR__, 7) . '/vendor/autoload.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Cms/DataResolver/FoundationCmsElementResolver.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Cms/DataResolver/Element/SocialLinksCmsElementResolver.php';

use DOMDocument;
use DOMXPath;
use MoorlFoundation\Core\Content\Cms\DataResolver\Element\SocialLinksCmsElementResolver;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SocialLinksCmsElementResolverTest extends TestCase
{
    public function testResolutionKeepsArrayOrderAndFiltersInvalidLinks(): void
    {
        $links = [
            ['label' => 'YouTube', 'username' => '@artist', 'url' => 'https://youtube.com/@artist'],
            ['label' => 'Unsafe', 'username' => '', 'url' => 'javascript:alert(1)'],
            ['label' => 'Invalid username', 'username' => [], 'url' => 'https://example.com'],
            ['label' => 'Instagram', 'username' => '@artist', 'url' => 'https://instagram.com/artist'],
            ['label' => 'Website', 'url' => 'https://example.com'],
        ];
        $slot = $this->resolve($links);
        self::assertSame([$links[0], $links[3], ['label' => 'Website', 'username' => '', 'url' => 'https://example.com']], $slot->getData()->get('socialLinks'));
    }

    public function testRenderedRowsHaveEscapedLabelsAndConsecutiveNumbers(): void
    {
        $links = $this->resolve([
            ['label' => '<b>Instagram</b>', 'username' => '<script>bad</script>', 'url' => 'https://instagram.com/artist'],
            ['label' => 'Unsafe', 'username' => '', 'url' => 'data:text/html,bad'],
            ['label' => 'Website', 'username' => '', 'url' => 'https://example.com'],
        ])->getData()->get('socialLinks');
        $loader = new FilesystemLoader(dirname(__DIR__, 4) . '/src/Resources/views');
        $twig = new Environment($loader, ['autoescape' => 'html']);
        $html = $twig->render('plugin/moorl-foundation/component/social-links.html.twig', ['socialLinks' => $links]);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;b&gt;Instagram&lt;/b&gt;', $html);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($document);
        self::assertCount(2, $xpath->query('//a[@target="_blank" and @rel="noopener noreferrer"]'));
        self::assertSame(['01', '02'], array_map(static fn ($node) => $node->textContent, iterator_to_array($xpath->query('//span[contains(@class,"__index")]'))));
    }

    public function testEmptyOrInvalidDataRendersNoList(): void
    {
        self::assertSame([], $this->resolve(null)->getData()->get('socialLinks'));
        self::assertSame([], $this->resolve('not a list')->getData()->get('socialLinks'));
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 4) . '/src/Resources/views'));
        self::assertSame('', trim($twig->render('plugin/moorl-foundation/component/social-links.html.twig', ['socialLinks' => []])));
    }

    private function resolve(mixed $links): CmsSlotEntity
    {
        $slot = new CmsSlotEntity();
        $slot->setFieldConfig(new FieldConfigCollection([new FieldConfig('socialLinks', FieldConfig::SOURCE_STATIC, $links)]));
        (new SocialLinksCmsElementResolver())->enrich($slot, $this->createMock(ResolverContext::class), new ElementDataCollection());
        return $slot;
    }
}
