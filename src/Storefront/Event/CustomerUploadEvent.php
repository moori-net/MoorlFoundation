<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Event;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class CustomerUploadEvent extends Event
{
    final public const EVENT_NAME = 'moorl.foundation.customer-upload';
    private ?Entity $entity = null;
    private string $fileNameTemplate = "%s";
    private ?string $fileFolderName = null;

    /**
     * @param UploadedFile[] $files
     */
    public function __construct(private SalesChannelContext $salesChannelContext, private Request $request, private iterable $files, private string $initiator, private ?string $entityId = null)
    {
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return UploadedFile[]
     */
    public function getFiles(): iterable
    {
        return $this->files;
    }

    /**
     * @param UploadedFile[] $files
     */
    public function setFiles(iterable $files): void
    {
        $this->files = $files;
    }

    public function getFileNameTemplate(): string
    {
        return $this->fileNameTemplate;
    }

    public function setFileNameTemplate(string $fileNameTemplate): void
    {
        $this->fileNameTemplate = $fileNameTemplate;
    }

    public function getFileFolderName(): ?string
    {
        return $this->fileFolderName;
    }

    public function setFileFolderName(?string $fileFolderName): void
    {
        $this->fileFolderName = $fileFolderName;
    }

    public function getInitiator(): string
    {
        return $this->initiator;
    }

    public function setInitiator(string $initiator): void
    {
        $this->initiator = $initiator;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
    }
}
