<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Twig;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ListingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('moorl_replace_from_entity', [$this, 'replaceFromEntity'])
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
}
