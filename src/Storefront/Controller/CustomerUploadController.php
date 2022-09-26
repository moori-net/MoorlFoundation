<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Controller;

use MoorlFoundation\Storefront\Event\CustomerUploadDoneEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadFilenameEvent;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\DataAbstractionLayer\MediaFolderRepositoryDecorator;
use Shopware\Core\Content\Media\DataAbstractionLayer\MediaRepositoryDecorator;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CustomerUploadController extends StorefrontController
{
    private EventDispatcherInterface $eventDispatcher;
    private MediaService $mediaService;
    private MediaRepositoryDecorator $mediaRepository;
    private MediaFolderRepositoryDecorator $mediaFolderRepository;
    private EntityRepositoryInterface $mediaDefaultFolderRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MediaService $mediaService,
        MediaRepositoryDecorator $mediaRepository,
        MediaFolderRepositoryDecorator $mediaFolderRepository,
        EntityRepositoryInterface $mediaDefaultFolderRepository
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->mediaService = $mediaService;
        $this->mediaRepository = $mediaRepository;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaDefaultFolderRepository = $mediaDefaultFolderRepository;
    }

    /**
     * @Route("/moorl/customer-upload", name="moorl.customer-upload.send", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function upload(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $files = $request->files->get('file', null);
        $initiator = $request->request->get('initiator', null);
        $hasRedirect = $request->get('redirectTo') && $request->get('redirectTo') !== 'null';

        if (!$files || !$initiator) {
            if ($hasRedirect) {
                return $this->createActionResponse($request);
            } else {
                return new Response();
            }
        }

        $entityId = $request->request->get('entityId', null);
        $files = is_array($files) ? $files : [$files];

        $uploadEvent = new CustomerUploadEvent(
            $salesChannelContext,
            $request,
            $files,
            $initiator,
            $entityId
        );
        $this->eventDispatcher->dispatch($uploadEvent);

        $body = '';

        /** @var UploadedFile\ $file */
        foreach ($files as $key => $file) {
            $isNewMediaFile = true;
            $mediaFolderId = null;

            $mediaFile = new MediaFile(
                $file->getPathname(),
                $file->getMimeType(),
                $file->getClientOriginalExtension(),
                $file->getSize()
            );

            $filenameEvent = new CustomerUploadFilenameEvent(
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                $initiator,
                (string) $key,
                $uploadEvent->getEntity(),
            );
            $this->eventDispatcher->dispatch($filenameEvent);

            /**
             * Try to find media by filename
             */
            if (!$filenameEvent->getMediaId()) {
                $criteria = new Criteria();
                $criteria->setLimit(1);
                $criteria->addFilter(new EqualsFilter('fileName', $filenameEvent->getFilename()));

                /** @var MediaEntity $media */
                $media = $this->mediaRepository->search($criteria, $salesChannelContext->getContext())->first();

                if ($media) {
                    $filenameEvent->setMediaId($media->getId());
                    $isNewMediaFile = false;
                }
            }

            /**
             * if no media id found, then create one:
             * Also create folders and sub folders based by entity/initiator.
             */
            if (!$filenameEvent->getMediaId()) {
                if (!$mediaFolderId) {
                    $mediaFolder = $this->getMediaFolder(
                        $initiator,
                        $salesChannelContext->getContext(),
                        $uploadEvent->getFileFolderName()
                    );

                    if ($uploadEvent->getFileFolderName()) {
                        $mediaFolderId =  $mediaFolder->getChildren()->first()->getId();
                    } else {
                        $mediaFolderId =  $mediaFolder->getId();
                    }
                }

                $filenameEvent->setMediaId(
                    $this->createMediaInFolder(
                        $mediaFolderId,
                        $salesChannelContext->getContext(),
                        $filenameEvent->isPrivate()
                    )
                );
            }

            $mediaId = $this->mediaService->saveMediaFile(
                $mediaFile,
                $filenameEvent->getFilename(),
                $salesChannelContext->getContext(),
                $initiator,
                $filenameEvent->getMediaId(),
                $filenameEvent->isPrivate()
            );

            $media = $this->getMedia($mediaId, $salesChannelContext->getContext());

            /**
             * Do nothing if media is just replaced
             */
            if ($isNewMediaFile) {
                $doneEvent = new CustomerUploadDoneEvent(
                    $filenameEvent->getFilename(),
                    $initiator,
                    (string) $key,
                    $mediaId,
                    $media,
                    $uploadEvent->getEntity(),
                );
                $this->eventDispatcher->dispatch($doneEvent);
            }

            if ($hasRedirect) {
                continue;
            }

            $entityMedia = null;
            $entityCoverId = null;
            $entity = $uploadEvent->getEntity();
            if ($entity) {
                if ($isNewMediaFile) {
                    $entityMedia = $doneEvent->getEntityMedia();
                }
                if (method_exists($entity, 'getCoverId')) {
                    $entityCoverId = $entity->getCoverId();
                }
            }

            $body .= $this->renderView('@Storefront/plugin/moorl-foundation/component/form/customer-upload-image.html.twig', [
                'media' => $media,
                'entityMedia' => $entityMedia,
                'entityCoverId' => $entityCoverId,
            ]);
        }

        if (!$hasRedirect) {
            return new Response($body);
        }

        return $this->createActionResponse($request);
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

    private function getMediaFolder(string $folder, Context $context, ?string $childName = null): ?MediaFolderEntity
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
            $mediaFolder = $this->getMediaFolder('product', $context);
            $mediaFolder->setId(Uuid::randomHex());
            $mediaFolder->setName($folder);
            $mediaFolder->setDefaultFolderId($this->getMediaDefaultFolderId($folder, $context));

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

    public function createMediaInFolder(string $mediaFolderId, Context $context, bool $private = false): string
    {
        $mediaId = Uuid::randomHex();
        $this->mediaRepository->create([[
            'id' => $mediaId,
            'private' => $private,
            'mediaFolderId' => $mediaFolderId,
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
