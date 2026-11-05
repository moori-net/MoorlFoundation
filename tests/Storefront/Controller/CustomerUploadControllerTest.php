<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Storefront\Controller;

require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
require_once dirname(__DIR__, 3) . '/src/Storefront/Controller/CustomerUploadController.php';
require_once dirname(__DIR__, 3) . '/src/Storefront/Event/CustomerUploadEvent.php';
require_once dirname(__DIR__, 3) . '/src/Storefront/Event/CustomerUploadFilenameEvent.php';
require_once dirname(__DIR__, 3) . '/src/Storefront/Event/CustomerUploadDoneEvent.php';

use MoorlFoundation\Storefront\Controller\CustomerUploadController;
use MoorlFoundation\Storefront\Event\CustomerUploadEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadFilenameEvent;
use MoorlFoundation\Storefront\Event\CustomerUploadDoneEvent;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaDefaultFolder\MediaDefaultFolderEntity;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CustomerUploadControllerTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            unlink($path);
        }
    }

    public function testUploadRouteRequiresACustomerLogin(): void
    {
        $route = (new \ReflectionClass(CustomerUploadController::class))
            ->getAttributes(Route::class)[0]->newInstance();

        self::assertTrue($route->getDefaults()['_loginRequired'] ?? false);
    }

    public function testRejectsMissingCustomerBeforeDispatchOrStorage(): void
    {
        [$controller, $context, $events, $state] = $this->harness();
        $context->method('getCustomer')->willReturn(null);
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 403);
        self::assertSame(0, $state->uploadEvents);
    }

    public function testRejectsGuestCustomers(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $customer->setGuest(true);
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 403);
    }

    public function testRejectsUnrecognizedInitiatorWithoutWritingMedia(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness(false);
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request($this->image(), ['initiator' => 'product']), $state, 403);
    }

    public function testRejectsAnEntityIdDifferentFromTheAuthorizedEntity(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request($this->image(), ['entityId' => str_repeat('b', 32)]), $state, 403);
    }

    public function testRejectsAnotherCustomerAsUploadEntity(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $events->addListener(CustomerUploadEvent::class, static function (CustomerUploadEvent $event): void {
            $other = new CustomerEntity();
            $other->setId(str_repeat('b', 32));
            $event->setEntity($other);
        }, -100);
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 403);
    }

    public function testAnExistingGlobalFilenameIsNeverOverwritten(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $done = [];
        $events->addListener(CustomerUploadDoneEvent::class, static function (CustomerUploadDoneEvent $event) use (&$done): void {
            $done[] = $event->getMediaId();
        });

        $response = $controller->upload($this->request($this->image()), $context);

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $state->created);
        self::assertCount(1, $state->saved);
        self::assertNotSame(str_repeat('f', 32), $state->saved[0]['id']);
        self::assertNotSame('logo', $state->saved[0]['filename']);
        self::assertSame([$state->created[0]['id']], $done);
        self::assertSame($done[0], json_decode($response->getContent(), true)['mediaId']);
    }

    public function testSameFilenameUploadsProduceIndependentMedia(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $controller->upload($this->request($this->image()), $context);
        $controller->upload($this->request($this->image()), $context);
        self::assertCount(2, $state->created);
        self::assertNotSame($state->saved[0]['id'], $state->saved[1]['id']);
        self::assertNotSame($state->saved[0]['filename'], $state->saved[1]['filename']);
    }

    public function testRejectsAnExplicitExistingMediaTarget(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $events->addListener(CustomerUploadFilenameEvent::class, static function (CustomerUploadFilenameEvent $event): void {
            $event->setMediaId(str_repeat('f', 32));
        });
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 403);
    }

    public function testRejectsActiveContentEvenWhenNamedAsAnImage(): void
    {
        foreach ([['<html><script>alert(1)</script></html>', 'photo.png'], ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'photo.svg']] as [$bytes, $name]) {
            [$controller, $context, $events, $state, $customer] = $this->harness();
            $context->method('getCustomer')->willReturn($customer);
            $this->assertRejected($controller, $context, $this->request($this->file($bytes, $name)), $state, 415);
        }
    }

    public function testRejectsAnImageWithTheWrongExtension(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request($this->image('photo.jpg')), $state, 415);
    }

    public function testRejectsAnInvalidPhpUpload(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request($this->file('x', 'photo.png', UPLOAD_ERR_PARTIAL)), $state, 400);
    }

    public function testChecksTheWholeBatchBeforeWritingItsFirstFile(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request([$this->image(), $this->file('not an image', 'photo.png')]), $state, 415);
    }

    public function testRejectsTooManyFiles(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request(array_fill(0, 11, $this->image())), $state, 413);
    }

    public function testRejectsOversizedFiles(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $file = $this->image();
        $handle = fopen($file->getPathname(), 'c');
        ftruncate($handle, 10 * 1024 * 1024 + 1);
        fclose($handle);
        clearstatcache(true, $file->getPathname());
        self::assertSame(10 * 1024 * 1024 + 1, $file->getSize());
        $this->assertRejected($controller, $context, $this->request($file), $state, 413);
    }

    public function testHonorsSubscriberFileFiltering(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $events->addListener(CustomerUploadEvent::class, static function (CustomerUploadEvent $event): void {
            $event->setFiles([]);
        }, -100);
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 400);
    }

    public function testRejectsExcessiveDecodedImageDimensions(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $image = $this->image();
        $bytes = file_get_contents($image->getPathname());
        $bytes = substr_replace($bytes, pack('NN', 50000, 50000), 16, 8);
        $bytes = substr_replace($bytes, hash('crc32b', substr($bytes, 12, 17), true), 29, 4);
        file_put_contents($image->getPathname(), $bytes);
        $this->assertRejected($controller, $context, $this->request($image), $state, 413);
    }

    public function testRejectsAnOversizedBatch(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $files = [];
        for ($i = 0; $i < 3; $i++) {
            $file = $this->image();
            $handle = fopen($file->getPathname(), 'c');
            ftruncate($handle, 8 * 1024 * 1024);
            fclose($handle);
            clearstatcache(true, $file->getPathname());
            $files[] = $file;
        }
        $this->assertRejected($controller, $context, $this->request($files), $state, 413);
    }

    public function testRejectsMalformedUploadParameters(): void
    {
        foreach ([['initiator' => '../product'], ['entityId' => 'not-a-uuid']] as $parameters) {
            [$controller, $context, $events, $state, $customer] = $this->harness();
            $context->method('getCustomer')->willReturn($customer);
            $this->assertRejected($controller, $context, $this->request($this->image(), $parameters), $state, 400);
        }
    }

    public function testRejectsNestedFileArrays(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $this->assertRejected($controller, $context, $this->request([[$this->image()]]), $state, 400);
    }

    public function testPreservesPrivateMediaAndDoneEventPrivacy(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $events->addListener(CustomerUploadFilenameEvent::class, static function (CustomerUploadFilenameEvent $event): void {
            $event->setPrivate(true);
        });
        $private = null;
        $events->addListener(CustomerUploadDoneEvent::class, static function (CustomerUploadDoneEvent $event) use (&$private): void {
            $private = $event->isPrivate();
        });
        $controller->upload($this->request($this->image()), $context);
        self::assertTrue($state->created[0]['private']);
        self::assertTrue($state->saved[0]['private']);
        self::assertTrue($private);
    }

    public function testDeletesTheNewMediaPlaceholderWhenStorageFails(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $state->failStorage = true;
        try {
            $controller->upload($this->request($this->image()), $context);
            self::fail('Storage failure must propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Storage unavailable', $exception->getMessage());
        }
        self::assertCount(1, $state->created);
        self::assertSame([['id' => $state->created[0]['id']]], $state->deleted);
    }

    public function testMissingProductFolderFailsWithoutRecursiveFolderCreation(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $state->missingAllFolders = true;
        $this->assertRejected($controller, $context, $this->request($this->image()), $state, 503);
        self::assertCount(2, $state->folderQueries);
        self::assertSame([], $state->foldersCreated);
    }

    public function testCreatesAnEntitySubfolderWithoutMutatingTheProductTemplate(): void
    {
        [$controller, $context, $events, $state, $customer] = $this->harness();
        $context->method('getCustomer')->willReturn($customer);
        $state->missingUploadFolder = true;
        $events->addListener(CustomerUploadEvent::class, static function (CustomerUploadEvent $event): void {
            $event->setFileFolderName('ad-123');
        }, -100);

        $response = $controller->upload($this->request($this->image()), $context);

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $state->foldersCreated);
        self::assertSame($state->foldersCreated[0]['id'], $state->foldersCreated[1]['parentId']);
        self::assertSame($state->foldersCreated[1]['id'], $state->created[0]['mediaFolderId']);
        self::assertSame(str_repeat('c', 32), $state->productFolder->getId());
        self::assertSame(0, $state->productFolder->getChildren()->count());
    }

    private function assertRejected(CustomerUploadController $controller, SalesChannelContext $context, Request $request, \stdClass $state, int $status): void
    {
        try {
            $controller->upload($request, $context);
            self::fail('Upload must be rejected.');
        } catch (HttpExceptionInterface $exception) {
            self::assertSame($status, $exception->getStatusCode());
        }
        self::assertSame([], $state->created);
        self::assertSame([], $state->saved);
    }

    private function request(UploadedFile|array $files, array $parameters = []): Request
    {
        return Request::create('/moorl/customer-upload', 'POST', array_replace(['initiator' => 'appflix_customer_avatar'], $parameters), [], ['file' => $files]);
    }

    private function image(string $name = 'logo.png'): UploadedFile
    {
        return $this->file(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j6S8AAAAASUVORK5CYII='), $name);
    }

    private function file(string $bytes, string $name, int $error = UPLOAD_ERR_OK): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'foundation-upload-test-');
        $this->temporaryFiles[] = $path;
        file_put_contents($path, $bytes);
        return new UploadedFile($path, $name, null, $error, true);
    }

    private function harness(bool $authorize = true): array
    {
        $state = (object) [
            'created' => [], 'saved' => [], 'deleted' => [], 'uploadEvents' => 0, 'failStorage' => false,
            'missingAllFolders' => false, 'missingUploadFolder' => false, 'folderQueries' => [], 'foldersCreated' => [],
        ];
        $customer = new CustomerEntity();
        $customer->setId(str_repeat('a', 32));
        $customer->setGuest(false);
        $context = $this->createMock(SalesChannelContext::class);
        $context->method('getContext')->willReturn(Context::createDefaultContext());
        $events = new EventDispatcher();
        $events->addListener(CustomerUploadEvent::class, static function (CustomerUploadEvent $event) use ($state, $customer, $authorize): void {
            $state->uploadEvents++;
            if ($authorize) {
                $event->setEntity($customer);
            }
        });
        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaRepository->method('create')->willReturnCallback(static function (array $payload) use ($state) {
            array_push($state->created, ...$payload);
            return new \Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent(Context::createDefaultContext(), new \Shopware\Core\Framework\Event\NestedEventCollection(), []);
        });
        $mediaRepository->method('delete')->willReturnCallback(static function (array $payload) use ($state) {
            array_push($state->deleted, ...$payload);
            return new \Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent(Context::createDefaultContext(), new \Shopware\Core\Framework\Event\NestedEventCollection(), []);
        });
        $mediaRepository->method('search')->willReturnCallback(static function (Criteria $criteria, Context $context): EntitySearchResult {
            $media = new MediaEntity();
            $media->setId($criteria->getIds()[0] ?? str_repeat('f', 32));
            return new EntitySearchResult('media', 1, new MediaCollection([$media]), null, $criteria, $context);
        });
        $folderRepository = $this->createMock(EntityRepository::class);
        $folderRepository->method('search')->willReturnCallback(static function (Criteria $criteria, Context $context) use ($state): EntitySearchResult {
            $name = $criteria->getFilters()[0]->getValue();
            $state->folderQueries[] = $name;
            if (count($state->folderQueries) > 2) {
                throw new \RuntimeException('Unexpected recursive folder resolution');
            }
            if ($state->missingAllFolders || ($state->missingUploadFolder && $name !== 'product')) {
                return new EntitySearchResult('media_folder', 0, new MediaFolderCollection(), null, $criteria, $context);
            }
            $folder = new MediaFolderEntity();
            $folder->setId(str_repeat('c', 32));
            $folder->setConfigurationId(str_repeat('d', 32));
            $folder->setChildren(new MediaFolderCollection());
            if ($name === 'product') {
                $state->productFolder = $folder;
            }
            return new EntitySearchResult('media_folder', 1, new MediaFolderCollection([$folder]), null, $criteria, $context);
        });
        $folderRepository->method('create')->willReturnCallback(static function (array $payload) use ($state) {
            array_push($state->foldersCreated, ...$payload);
            return new \Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent(Context::createDefaultContext(), new \Shopware\Core\Framework\Event\NestedEventCollection(), []);
        });
        $defaultFolderRepository = $this->createMock(EntityRepository::class);
        $defaultFolderRepository->method('search')->willReturnCallback(static function (Criteria $criteria, Context $context): EntitySearchResult {
            $folder = new MediaDefaultFolderEntity();
            $folder->setId(str_repeat('e', 32));
            return new EntitySearchResult('media_default_folder', 1, new MediaDefaultFolderCollection([$folder]), null, $criteria, $context);
        });
        $mediaService = $this->createMock(MediaService::class);
        $mediaService->method('saveMediaFile')->willReturnCallback(static function (MediaFile $file, string $filename, Context $context, ?string $folder, ?string $id, bool $private) use ($state): string {
            if ($state->failStorage) {
                throw new \RuntimeException('Storage unavailable');
            }
            $state->saved[] = ['id' => $id, 'filename' => $filename, 'private' => $private];
            return $id;
        });
        $controller = new class($events, $mediaService, $mediaRepository, $folderRepository, $defaultFolderRepository) extends CustomerUploadController {
            protected function renderView(string $view, array $parameters = []): string
            {
                return json_encode(['mediaId' => $parameters['media']->getId()], JSON_THROW_ON_ERROR);
            }
        };
        return [$controller, $context, $events, $state, $customer];
    }
}
