<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Content\Cms;

require_once dirname(__DIR__, 7) . '/vendor/autoload.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Cms/LegacyTextCmsElementResolver.php';
require_once dirname(__DIR__, 4) . '/src/Core/Content/Cms/TocCmsElementResolver.php';

use MoorlFoundation\Core\Content\Cms\TocCmsElementResolver;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Cms\DataResolver\FieldConfigCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\TextStruct;

class TocCmsElementResolverTest extends TestCase
{
    public function testKeepsAllValidHeadingsWhenLevelsAreSkipped(): void
    {
        $toc = $this->resolveToc(
            '<h2 id="top">Top</h2><h4 id="detail">Detail</h4><h3 id="context">Kontext</h3>'
        );

        $doc = new \DOMDocument();
        $doc->loadHTML($toc);
        $xPath = new \DOMXPath($doc);

        self::assertSame(
            ['#top', '#detail', '#context'],
            array_map(
                static fn (\DOMElement $anchor): string => $anchor->getAttribute('href'),
                iterator_to_array($xPath->query('//a'))
            )
        );
        self::assertSame(1, $xPath->query('//a[@href="#top"]/following-sibling::ol//a[@href="#detail"]')->length);
    }

    public function testDoesNotRenderATocWhenHeadingsHaveNoUsableLinkOrTitle(): void
    {
        $toc = $this->resolveToc(
            '<h2 id="empty"></h2><h3 id="blank">&nbsp;</h3><h4 id="">Ohne Sprungziel</h4>'
        );

        self::assertStringNotContainsString('<ol', $toc);
    }

    private function resolveToc(string $content): string
    {
        $slot = new CmsSlotEntity();
        $slot->setFieldConfig(new FieldConfigCollection([
            new FieldConfig('content', FieldConfig::SOURCE_STATIC, $content),
        ]));

        (new TocCmsElementResolver())->enrich(
            $slot,
            $this->createMock(ResolverContext::class),
            new ElementDataCollection()
        );

        $data = $slot->getData();
        self::assertInstanceOf(TextStruct::class, $data);

        return (string) $data->getContent();
    }
}
