<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\CmsCtaBanner;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ImageStruct;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Crunched;
use ScssPhp\ScssPhp\Formatter\Expanded;

class CmsCtaBannerStruct extends ImageStruct
{
    protected ?CategoryEntity $category = null;
    protected ?MediaEntity $iconMedia = null;
    protected ?ProductEntity $product = null;
    protected ?string $scss = null;

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string|null
     */
    public function getScss(): ?string
    {
        return $this->scss;
    }

    /**
     * @param string|null $scss
     */
    public function setScss(?string $scss, string $elementId): void
    {
        if (!$scss) {
            return;
        }

        $compiler = new Compiler();
        $compiler->setFormatter(Expanded::class);

        $this->scss = $compiler->compile(sprintf("#appflix-cta-banner-%s { %s }", $elementId, $scss));
    }

    /**
     * @return CategoryEntity|null
     */
    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    /**
     * @param CategoryEntity|null $category
     */
    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    /**
     * @return MediaEntity|null
     */
    public function getIconMedia(): ?MediaEntity
    {
        return $this->iconMedia;
    }

    /**
     * @param MediaEntity|null $iconMedia
     */
    public function setIconMedia(?MediaEntity $iconMedia): void
    {
        $this->iconMedia = $iconMedia;
    }
}
