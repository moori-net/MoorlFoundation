<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityTreeTrait
{
    protected ?string $parentId = null;
    protected ?string $afterId = null;
    protected ?string $path = null;
    protected int $childCount = 0;
    protected int $level = 0;

    /**
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @param string|null $parentId
     */
    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string|null
     */
    public function getAfterId(): ?string
    {
        return $this->afterId;
    }

    /**
     * @param string|null $afterId
     */
    public function setAfterId(?string $afterId): void
    {
        $this->afterId = $afterId;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getChildCount(): int
    {
        return $this->childCount;
    }

    /**
     * @param int $childCount
     */
    public function setChildCount(int $childCount): void
    {
        $this->childCount = $childCount;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
