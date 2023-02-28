<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityTreeTrait
{
    protected ?string $parentId = null;
    protected ?string $afterId = null;
    protected ?string $path = null;
    protected int $childCount = 0;
    protected int $level = 0;

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getAfterId(): ?string
    {
        return $this->afterId;
    }

    public function setAfterId(?string $afterId): void
    {
        $this->afterId = $afterId;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getChildCount(): int
    {
        return $this->childCount;
    }

    public function setChildCount(int $childCount): void
    {
        $this->childCount = $childCount;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
