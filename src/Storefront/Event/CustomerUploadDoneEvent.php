<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Event;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Symfony\Contracts\EventDispatcher\Event;

class CustomerUploadDoneEvent extends Event
{
    final public const EVENT_NAME = 'moorl.foundation.customer-upload-done';
    private ?Entity $entityMedia = null;
    private bool $private = false;

    public function __construct(private string $filename, private string $initiator, private string $key, private string $mediaId, private MediaEntity $media, private ?Entity $entity = null)
    {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getInitiator(): string
    {
        return $this->initiator;
    }

    public function setInitiator(string $initiator): void
    {
        $this->initiator = $initiator;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): void
    {
        $this->entity = $entity;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getMedia(): MediaEntity
    {
        return $this->media;
    }

    public function setMedia(MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getEntityMedia(): ?Entity
    {
        return $this->entityMedia;
    }

    public function setEntityMedia(?Entity $entityMedia): void
    {
        $this->entityMedia = $entityMedia;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }
}
