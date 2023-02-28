<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;

trait EntityMediaFolderTrait
{
    protected ?string $mediaFolderId = null;
    protected ?MediaFolderEntity $mediaFolder = null;

    public function setMediaFolderId(?string $mediaFolderId): void
    {
        $this->mediaFolderId = $mediaFolderId;
    }

    public function getMediaFolderId(): ?string
    {
        return $this->mediaFolderId;
    }

    public function setMediaFolder(?MediaFolderEntity $mediaFolder): void
    {
        $this->mediaFolder = $mediaFolder;
    }

    public function getMediaFolder(): ?MediaFolderEntity
    {
        return $this->mediaFolder;
    }
}
