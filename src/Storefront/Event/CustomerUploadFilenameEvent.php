<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Event;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Symfony\Contracts\EventDispatcher\Event;

class CustomerUploadFilenameEvent extends Event
{
    final public const EVENT_NAME = 'moorl.foundation.customer-upload-filename';
    private ?string $mediaId = null;
    private bool $private = false;

    public function __construct(private string $filename, private string $initiator, private string $key, private ?Entity $entity = null)
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

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
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
