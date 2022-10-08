<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VersionCompareExtension extends AbstractExtension
{
    protected string $shopwareVersion;

    public function __construct(string $shopwareVersion)
    {
        $this->shopwareVersion = $shopwareVersion;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('moorl_sw_version_compare', [$this, 'swVersionCompare']),
            new TwigFunction('moorl_php_version_compare', [$this, 'phpVersionCompare'])
        ];
    }

    public function swVersionCompare(string $version2, string $operator = '>='): bool
    {
        return version_compare($this->shopwareVersion, $version2, $operator);
    }

    public function phpVersionCompare(string $version2, string $operator = '>='): bool
    {
        return version_compare(phpversion(), $version2, $operator);
    }
}
