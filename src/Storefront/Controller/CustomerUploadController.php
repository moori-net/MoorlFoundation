<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use MoorlFoundation\Storefront\Event\CustomerUploadDoneEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadFilenameEvent;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\File\FileNameValidator;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront'], '_loginRequired' => true])]
class CustomerUploadController extends StorefrontController
{
    private const MAX_FILES = 10;
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;
    private const MAX_BATCH_SIZE = 20 * 1024 * 1024;
    private const MAX_IMAGE_PIXELS = 40_000_000;
    private const IMAGE_MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
    ];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MediaService $mediaService,
        private readonly EntityRepository $mediaRepository,
        private readonly EntityRepository $mediaFolderRepository,
        private readonly EntityRepository $mediaDefaultFolderRepository
    )
    {
    }

    #[Route(path: '/moorl/customer-upload', name: 'moorl.customer-upload.send', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function upload(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $customer = $salesChannelContext->getCustomer();
        if (!$customer || $customer->getGuest()) {
            throw RoutingException::customerNotLoggedIn();
        }

        $files = $request->files->get('file');
        $initiator = $request->request->get('initiator');
        $entityId = $request->request->get('entityId');
        $hasRedirect = $request->get('redirectTo') && $request->get('redirectTo') !== 'null';

        if (!is_string($initiator) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $initiator)) {
            throw new BadRequestHttpException('Invalid upload initiator.');
        }
        if ($entityId !== null && (!is_string($entityId) || !Uuid::isValid($entityId))) {
            throw new BadRequestHttpException('Invalid upload entity ID.');
        }

        $files = is_array($files) ? $files : [$files];
        $uploadEvent = new CustomerUploadEvent(
            $salesChannelContext,
            $request,
            $files,
            $initiator,
            $entityId
        );
        $this->eventDispatcher->dispatch($uploadEvent);

        // Subscribers must recognize the initiator and resolve an authorized entity.
        $entity = $uploadEvent->getEntity();
        if (!$entity
            || ($entityId !== null && $entity->getId() !== $entityId)
            || ($entity instanceof CustomerEntity && $entity->getId() !== $customer->getId())
            || (method_exists($entity, 'getCustomerId') && $entity->getCustomerId() !== $customer->getId())
        ) {
            throw new AccessDeniedHttpException('Upload is not authorized for this entity.');
        }

        $eventFiles = $uploadEvent->getFiles();
        $files = is_array($eventFiles) ? $eventFiles : iterator_to_array($eventFiles);
        $mediaFiles = $this->validateFiles($files);
        $uploads = [];

        // Prepare the entire batch before creating any folders or media records.
        foreach ($files as $key => $file) {
            $filenameEvent = new CustomerUploadFilenameEvent(
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                $initiator,
                (string) $key,
                $uploadEvent->getEntity(),
            );
            $this->eventDispatcher->dispatch($filenameEvent);

            // This endpoint creates media only; existing IDs require a separate ownership-checked flow.
            if ($filenameEvent->getMediaId() !== null) {
                throw new AccessDeniedHttpException('Replacing existing media is not allowed.');
            }
            (new FileNameValidator())->validateFileName($filenameEvent->getFilename());
            $filenameEvent->setFilename(Uuid::randomHex() . '-' . $filenameEvent->getFilename());
            (new FileNameValidator())->validateFileName($filenameEvent->getFilename());
            $uploads[$key] = $filenameEvent;
        }

        $context = $salesChannelContext->getContext();
        $mediaFolder = $this->getMediaFolder($initiator, $context, $uploadEvent->getFileFolderName());
        $mediaFolderId = $uploadEvent->getFileFolderName()
            ? $mediaFolder->getChildren()->first()->getId()
            : $mediaFolder->getId();
        $body = '';

        foreach ($uploads as $key => $filenameEvent) {
            $mediaId = $this->createMediaInFolder(
                $mediaFolderId,
                $context,
                $filenameEvent->isPrivate(),
                $files[$key]->getClientOriginalName()
            );
            try {
                $this->mediaService->saveMediaFile(
                    $mediaFiles[$key],
                    $filenameEvent->getFilename(),
                    $context,
                    $initiator,
                    $mediaId,
                    $filenameEvent->isPrivate()
                );
            } catch (\Throwable $exception) {
                $this->mediaRepository->delete([['id' => $mediaId]], $context);
                throw $exception;
            }

            $media = $this->getMedia($mediaId, $context);

            $doneEvent = new CustomerUploadDoneEvent(
                $filenameEvent->getFilename(),
                $initiator,
                (string) $key,
                $mediaId,
                $media,
                $entity,
            );
            $doneEvent->setPrivate($filenameEvent->isPrivate());
            $this->eventDispatcher->dispatch($doneEvent);

            if ($hasRedirect) {
                continue;
            }

            $entityCoverId = null;
            if (method_exists($entity, 'getCoverId')) {
                $entityCoverId = $entity->getCoverId();
            }

            $body .= $this->renderView('@MoorlFoundation/plugin/moorl-foundation/component/form/customer-upload-image.html.twig', [
                'media' => $media,
                'entityMedia' => $doneEvent->getEntityMedia(),
                'entityCoverId' => $entityCoverId,
            ]);
        }

        if (!$hasRedirect) {
            return new Response($body);
        }

        return $this->createActionResponse($request);
    }

    /**
     * @param UploadedFile[] $files
     * @return array<array-key, MediaFile>
     */
    private function validateFiles(array $files): array
    {
        if (!$files) {
            throw new BadRequestHttpException('No files uploaded.');
        }
        if (count($files) > self::MAX_FILES) {
            throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Too many files.');
        }

        $mediaFiles = [];
        $totalSize = 0;
        foreach ($files as $key => $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                throw new BadRequestHttpException('Invalid uploaded file.');
            }
            $size = $file->getSize();
            $totalSize += $size;
            if ($size > self::MAX_FILE_SIZE || $totalSize > self::MAX_BATCH_SIZE) {
                throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Upload exceeds the size limit.');
            }
            if ($size === 0) {
                throw new BadRequestHttpException('Empty uploaded file.');
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $expectedMimeType = self::IMAGE_MIME_TYPES[$extension] ?? null;
            if (!$expectedMimeType || $file->getMimeType() !== $expectedMimeType) {
                throw new UnsupportedMediaTypeHttpException('Only JPEG, PNG, GIF, WebP and AVIF images are allowed.');
            }
            $image = @getimagesize($file->getPathname());
            if (!$image || ($image['mime'] ?? null) !== $expectedMimeType || $image[0] < 1 || $image[1] < 1) {
                throw new UnsupportedMediaTypeHttpException('Invalid image content.');
            }
            if ($image[0] * $image[1] > self::MAX_IMAGE_PIXELS) {
                throw new HttpException(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Image dimensions exceed the limit.');
            }
            $mediaFiles[$key] = new MediaFile($file->getPathname(), $expectedMimeType, $extension, $size);
        }

        return $mediaFiles;
    }

    private function getMediaDefaultFolderId(string $folder, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('entity', $folder));
        $criteria->setLimit(1);

        $mediaDefaultFolder = $this->mediaDefaultFolderRepository->search($criteria, $context)->first();

        if (!$mediaDefaultFolder) {
            $id = Uuid::randomHex();

            $this->mediaDefaultFolderRepository->create([[
                'id' => $id,
                'entity' => $folder,
                'associationFields' => []
            ]], $context);
        } else {
            $id = $mediaDefaultFolder->getId();
        }

        return $id;
    }

    private function getMediaFolder(string $folder, Context $context, ?string $childName = null): MediaFolderEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('media_folder.defaultFolder.entity', $folder));
        $criteria->addAssociation('defaultFolder');
        $criteria->setLimit(1);

        if ($childName) {
            $criteria->addAssociation('children');
            $childrenCriteria = $criteria->getAssociation('children');
            $childrenCriteria->addFilter(new EqualsFilter('name', $childName));
        }

        $mediaFolder = $this->mediaFolderRepository->search($criteria, $context)->first();

        if (!$mediaFolder) {
            if ($folder === 'product') {
                throw new ServiceUnavailableHttpException(null, 'Upload media folder configuration is unavailable.');
            }
            $template = $this->getMediaFolder('product', $context);
            $mediaFolder = new MediaFolderEntity();
            $mediaFolder->setId(Uuid::randomHex());
            $mediaFolder->setConfigurationId($template->getConfigurationId());
            $mediaFolder->setName($folder);
            $mediaFolder->setDefaultFolderId($this->getMediaDefaultFolderId($folder, $context));
            $mediaFolder->setChildren(new MediaFolderCollection());

            $this->createMediaFolder($mediaFolder, $context);
        }

        if ($childName && !$mediaFolder->getChildren()->first()) {
            $childMediaFolder = new MediaFolderEntity();
            $childMediaFolder->setId(Uuid::randomHex());
            $childMediaFolder->setConfigurationId($mediaFolder->getConfigurationId());
            $childMediaFolder->setName($childName);
            $childMediaFolder->setParentId($mediaFolder->getId());

            $this->createMediaFolder($childMediaFolder, $context);

            $mediaFolder->getChildren()->add($childMediaFolder);
        }

        return $mediaFolder;
    }

    private function createMediaFolder(MediaFolderEntity $mediaFolder, Context $context): void
    {
        $payload = [
            'id' => $mediaFolder->getId(),
            'configurationId' => $mediaFolder->getConfigurationId(),
            'name' => $mediaFolder->getName(),
        ];

        if ($mediaFolder->getDefaultFolderId()) {
            $payload['defaultFolderId'] = $mediaFolder->getDefaultFolderId();
        } else if ($mediaFolder->getParentId()) {
            $payload['parentId'] = $mediaFolder->getParentId();
            $payload['useParentConfiguration'] = true;
        }

        $this->mediaFolderRepository->create([$payload], $context);
    }

    public function createMediaInFolder(string $mediaFolderId, Context $context, bool $private = false, ?string $originalFilename = null): string
    {
        $mediaId = Uuid::randomHex();
        $this->mediaRepository->create([[
            'id' => $mediaId,
            'private' => $private,
            'mediaFolderId' => $mediaFolderId,
            'alt' => $originalFilename,
            'title' => $originalFilename,
        ]], $context);

        return $mediaId;
    }

    private function getMedia(string $mediaId, Context $context): MediaEntity
    {
        $criteria = new Criteria([$mediaId]);
        $criteria->setLimit(1);

        return $this->mediaRepository->search($criteria, $context)->first();
    }
}
