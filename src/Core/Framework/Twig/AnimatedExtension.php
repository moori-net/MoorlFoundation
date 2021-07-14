<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnimatedExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('moorl_animated', [$this, 'animated'])
        ];
    }

    /**
     * @return string
     */
    public function animated(array $config): string
    {
        $ar = [
            'animateIn' => 'data-animate-in',
            'animateOut' => 'data-animate-out',
            'animateHover' => 'data-animate-hover',
        ];
        $html = [];

        foreach ($ar as $ju => $das) {
            if (!empty($config[$ju]) && !empty($config[$ju]['value']) && $config[$ju]['value'] !== 'none') {
                $html[] = sprintf("%s=%s", $das, $config[$ju]['value']);
            }
        }

        return implode(" ", $html);
    }
}
