<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;

trait EntityMediaFolderTrait
{
    protected ?string $mediaFolderId = null;
    protected ?MediaFolderEntity $mediaFolder = null;

    /**
     * @param string|null $mediaFolderId
     */
    public function setMediaFolderId(?string $mediaFolderId): void
    {
        $this->mediaFolderId = $mediaFolderId;
    }

    /**
     * @return string|null
     */
    public function getMediaFolderId(): ?string
    {
        return $this->mediaFolderId;
    }

    /**
     * @param MediaFolderEntity|null $mediaFolder
     */
    public function setMediaFolder(?MediaFolderEntity $mediaFolder): void
    {
        $this->mediaFolder = $mediaFolder;
    }

    /**
     * @return MediaFolderEntity|null
     */
    public function getMediaFolder(): ?MediaFolderEntity
    {
        return $this->mediaFolder;
    }
}
