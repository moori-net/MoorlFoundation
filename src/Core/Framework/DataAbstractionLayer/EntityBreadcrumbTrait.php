<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityBreadcrumbTrait
{
    protected ?string $breadcrumbPlain;
    protected ?array $breadcrumb;

    /**
     * @return string|null
     */
    public function getBreadcrumbPlain(): ?string
    {
        return $this->breadcrumbPlain;
    }

    /**
     * @param string|null $breadcrumbPlain
     */
    public function setBreadcrumbPlain(?string $breadcrumbPlain): void
    {
        $this->breadcrumbPlain = $breadcrumbPlain;
    }

    /**
     * @return array|null
     */
    public function getBreadcrumb(): ?array
    {
        return $this->breadcrumb;
    }

    /**
     * @param array|null $breadcrumb
     */
    public function setBreadcrumb(?array $breadcrumb): void
    {
        $this->breadcrumb = $breadcrumb;
    }
}
