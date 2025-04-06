<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Expanded;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ImageStruct;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CtaBannerStruct extends ImageStruct
{
    protected ?CategoryEntity $category = null;
    protected ?MediaEntity $iconMedia = null;
    protected ?ProductEntity $product = null;
    protected ?Entity $customEntity = null;
    protected ?string $scss = null;

    public function getCustomEntity(): ?Entity
    {
        return $this->customEntity;
    }

    public function setCustomEntity(?Entity $customEntity): void
    {
        $this->customEntity = $customEntity;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getScss(): ?string
    {
        return $this->scss;
    }

    public function setScss(?string $scss, string $elementId): void
    {
        if (!$scss) {
            return;
        }

        $compiler = new Compiler();
        $compiler->setFormatter(Expanded::class);

        $this->scss = $compiler->compile(sprintf("#moorl-cta-banner-%s { %s }", $elementId, $scss));
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getIconMedia(): ?MediaEntity
    {
        return $this->iconMedia;
    }

    public function setIconMedia(?MediaEntity $iconMedia): void
    {
        $this->iconMedia = $iconMedia;
    }
}
