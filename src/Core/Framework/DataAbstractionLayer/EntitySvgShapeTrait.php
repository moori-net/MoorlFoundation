<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntitySvgShapeTrait
{
    protected ?array $svgShape = null;

    public function getSvgShape(): ?array
    {
        return $this->svgShape;
    }

    public function setSvgShape(?array $svgShape): void
    {
        $this->svgShape = $svgShape;
    }

    public function getSvgShapeHTML(string $title = ""): string
    {
        $unit = "";
        if (isset($this->svgShape['unit'])) {
            $unit = $this->svgShape['unit'] === 'px' ? "" : "%";
        }

        if (isset($this->svgShape['shape'])) {
            switch ($this->svgShape['shape']) {
                case 'rect':
                    return sprintf(
                        '<rect width="%s" height="%s" x="%s" y="%s" rx="%s" ry="%s" style="%s"><title>%s</title></rect>',
                        $this->svgShape['width'] . $unit,
                        $this->svgShape['height'] . $unit,
                        $this->svgShape['x'] . $unit,
                        $this->svgShape['y'] . $unit,
                        $this->svgShape['rx'] . $unit,
                        $this->svgShape['ry'] . $unit,
                        $this->svgShape['style'],
                        $title
                    );
                case 'circle':
                    return sprintf(
                        '<circle r="%s" cx="%s" cy="%s" style="%s"><title>%s</title></circle>',
                        $this->svgShape['r'] . $unit,
                        $this->svgShape['cx'] . $unit,
                        $this->svgShape['cy'] . $unit,
                        $this->svgShape['style'],
                        $title
                    );
                case 'ellipse':
                    return sprintf(
                        '<ellipse rx="%s" ry="%s" cx="%s" cy="%s" style="%s"><title>%s</title></ellipse>',
                        $this->svgShape['rx'] . $unit,
                        $this->svgShape['ry'] . $unit,
                        $this->svgShape['cx'] . $unit,
                        $this->svgShape['cy'] . $unit,
                        $this->svgShape['style'],
                        $title
                    );
            }
        }

        return '<!-- error loading svg shape -->';
    }
}
