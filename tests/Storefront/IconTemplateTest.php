<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Storefront;

require_once dirname(__DIR__, 5) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

class IconTemplateTest extends TestCase
{
    public function testLegacyPacksRenderTheCorrespondingFontAwesome7Svg(): void
    {
        $resources = dirname(__DIR__, 2) . '/src/Resources';
        $template = file_get_contents($resources . '/views/storefront/utilities/icon.html.twig');
        $loader = new FilesystemLoader();
        $loader->addPath($resources . '/app/storefront/dist', 'MoorlFoundation');
        $twig = new Environment(new ChainLoader([
            new ArrayLoader([
                // Standalone rendering: replace only Shopware's inheritance tag.
                'icon' => str_replace('sw_extends', 'extends', $template),
                '@Storefront/storefront/utilities/icon.html.twig' => '{% block utilities_icon %}parent-icon{% endblock %}',
            ]),
            $loader,
        ]));
        $twig->addFilter(new TwigFilter('sw_icon_cache', static fn (string $svg): string => $svg));

        foreach (['fa6b' => 'brands', 'fa6r' => 'regular', 'fa6s' => 'solid'] as $pack => $style) {
            $name = $style === 'brands' ? 'shopware' : 'user';
            $svg = file_get_contents($resources . '/app/storefront/dist/assets/icon/fa7/' . $style . '/' . $name . '.svg');
            $rendered = $twig->render('icon', ['pack' => $pack, 'name' => $name]);
            self::assertStringContainsString($svg, $rendered, $pack);
            self::assertStringContainsString('icon-' . $pack . '-' . $name, $rendered);
        }

        self::assertStringContainsString('parent-icon', $twig->render('icon', ['pack' => 'solid', 'name' => 'user']));
    }
}
