<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityBreadcrumbTrait
{
    protected ?string $breadcrumbPlain = null;
    protected ?array $breadcrumb = null;

    public function getBreadcrumbPlain(): ?string
    {
        return $this->breadcrumbPlain;
    }

    public function setBreadcrumbPlain(?string $breadcrumbPlain): void
    {
        $this->breadcrumbPlain = $breadcrumbPlain;
    }

    public function getBreadcrumb(): ?array
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(?array $breadcrumb): void
    {
        $this->breadcrumb = $breadcrumb;
    }
}
