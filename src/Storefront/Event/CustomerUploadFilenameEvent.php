<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Event;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Symfony\Contracts\EventDispatcher\Event;

class CustomerUploadFilenameEvent extends Event
{
    public const EVENT_NAME = 'moorl.foundation.customer-upload-filename';

    private string $filename;
    private string $initiator;
    private string $key;
    private ?string $mediaId = null;
    private ?Entity $entity = null;
    private bool $private = false;

    public function __construct(
        string $filename,
        string $initiator,
        string $key,
        ?Entity $entity = null
    )
    {
       $this->filename = $filename;
       $this->initiator = $initiator;
       $this->key = $key;
       $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getInitiator(): string
    {
        return $this->initiator;
    }

    /**
     * @param string $initiator
     */
    public function setInitiator(string $initiator): void
    {
        $this->initiator = $initiator;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return Entity|null
     */
    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    /**
     * @param Entity|null $entity
     */
    public function setEntity(?Entity $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return string|null
     */
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    /**
     * @param string|null $mediaId
     */
    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }
}
