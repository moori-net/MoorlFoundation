<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap\Cms;

use MoorlFoundation\Core\Content\ImageMap\ImageMapEntity;
use Shopware\Core\Framework\Struct\Struct;

class ImageMapCmsStruct extends Struct
{
    protected array $options = [];
    protected ImageMapEntity $combinationDiscount;

    public function getImageMap(): ImageMapEntity
    {
        return $this->combinationDiscount;
    }

    public function setImageMap(ImageMapEntity $combinationDiscount): void
    {
        $this->combinationDiscount = $combinationDiscount;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
