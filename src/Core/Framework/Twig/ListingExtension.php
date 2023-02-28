<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Twig;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class ListingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('moorl_replace_from_entity', $this->replaceFromEntity(...))
        ];
    }

    public function replaceFromEntity(string $text, ?Entity $entity = null): string
    {
        if (!$entity) {
            return $text;
        }

        $variables = $entity->getTranslated();

        foreach($variables as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $text = str_replace('%'.$key.'%', $value, $text);
        }

        return $text;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('moorl_format_bytes', $this->formatBytes(...)),
        );
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
