<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Event;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CustomerUploadEvent extends Event
{
    public const EVENT_NAME = 'moorl.foundation.customer-upload';

    private SalesChannelContext $salesChannelContext;
    private Request $request;
    /**
     * @var UploadedFile[]
     */
    private iterable $files;
    private string $initiator;
    private ?Entity $entity = null;
    private ?string $entityId = null;
    private string $fileNameTemplate = "%s";
    private ?string $fileFolderName = null;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        Request $request,
        iterable $files,
        string $initiator,
        ?string $entityId = null
    )
    {
       $this->salesChannelContext = $salesChannelContext;
       $this->request = $request;
       $this->files = $files;
       $this->initiator = $initiator;
       $this->entityId = $entityId;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
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

    /**
     * @return string
     */
    public function getFileNameTemplate(): string
    {
        return $this->fileNameTemplate;
    }

    /**
     * @param string $fileNameTemplate
     */
    public function setFileNameTemplate(string $fileNameTemplate): void
    {
        $this->fileNameTemplate = $fileNameTemplate;
    }

    /**
     * @return string|null
     */
    public function getFileFolderName(): ?string
    {
        return $this->fileFolderName;
    }

    /**
     * @param string|null $fileFolderName
     */
    public function setFileFolderName(?string $fileFolderName): void
    {
        $this->fileFolderName = $fileFolderName;
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
    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /**
     * @param string|null $entityId
     */
    public function setEntityId(?string $entityId): void
    {
        $this->entityId = $entityId;
    }
}
