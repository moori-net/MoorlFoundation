<?php declare(strict_types=1);

namespace MoorlFoundation\Tests\Core\Framework\DataAbstractionLayer\Indexing;

require_once dirname(__DIR__, 8) . '/vendor/autoload.php';
require_once dirname(__DIR__, 5) . '/src/Core/Framework/DataAbstractionLayer/Indexing/HtmlContentMediaFetcher.php';

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexing\HtmlContentMediaFetcher;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Media\MediaEntity;
use Symfony\Component\HttpFoundation\Request;

class HtmlContentMediaFetcherTest extends TestCase
{
    public function testKeepsUnchangedHtmlAsFragment(): void
    {
        $fetcher = (new \ReflectionClass(HtmlContentMediaFetcher::class))->newInstanceWithoutConstructor();
        $processContent = new \ReflectionMethod(HtmlContentMediaFetcher::class, 'processContent');

        $content = '<p>Größe</p>';

        self::assertSame(
            $content,
            $processContent->invoke($fetcher, $content, 'moorl_magazine_article', Context::createDefaultContext())
        );
    }

    public function testKeepsEmptyAndFalseyContent(): void
    {
        $fetcher = (new \ReflectionClass(HtmlContentMediaFetcher::class))->newInstanceWithoutConstructor();
        $processContent = new \ReflectionMethod(HtmlContentMediaFetcher::class, 'processContent');
        $context = Context::createDefaultContext();

        self::assertSame('', $processContent->invoke($fetcher, '', 'moorl_magazine_article', $context));
        self::assertSame('0', $processContent->invoke($fetcher, '0', 'moorl_magazine_article', $context));
    }

    public function testDoesNotWriteUnchangedContent(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([[
                'moorl_magazine_article_id' => Uuid::fromHexToBytes(Uuid::randomHex()),
                'content' => '<p>Unverändert</p>',
            ]]);
        $connection->expects(self::never())->method('executeStatement');

        $fetcher = new HtmlContentMediaFetcher(
            $this->createMock(DefinitionInstanceRegistry::class),
            $connection,
            $this->createMock(MediaService::class),
            $this->createMock(FileSaver::class)
        );
        $updateLanguage = new \ReflectionMethod(HtmlContentMediaFetcher::class, 'updateLanguage');

        $updateLanguage->invoke(
            $fetcher,
            [Uuid::randomHex()],
            'moorl_magazine_article',
            Context::createDefaultContext(),
            ['content']
        );
    }

    public function testRemovesTemporaryFileWhenUrlDownloadFails(): void
    {
        $tempFile = null;
        $mediaService = $this->createMock(MediaService::class);
        $mediaService->expects(self::once())
            ->method('fetchFile')
            ->willReturnCallback(function (Request $request, ?string $path) use (&$tempFile): void {
                $tempFile = $path;

                throw new \RuntimeException('Download failed');
            });

        $fetcher = new HtmlContentMediaFetcher(
            $this->createMock(DefinitionInstanceRegistry::class),
            $this->createMock(Connection::class),
            $mediaService,
            $this->createMock(FileSaver::class)
        );
        $fetchFileFromUrl = new \ReflectionMethod(HtmlContentMediaFetcher::class, 'fetchFileFromURL');

        try {
            $fetchFileFromUrl->invoke($fetcher, 'https://example.com/image.jpg?token=abc', 'jpg');
            self::fail('Expected the download error to be rethrown.');
        } catch (\RuntimeException) {
        }

        self::assertIsString($tempFile);
        self::assertFileDoesNotExist($tempFile);
    }

    public function testBindsOnlyChangedHtmlFields(): void
    {
        $context = Context::createDefaultContext();
        $articleId = Uuid::fromHexToBytes(Uuid::randomHex());
        $media = new MediaEntity();
        $media->setUniqueIdentifier(Uuid::randomHex());
        $media->setUrl('/media/remote-image.jpg');
        $mediaRepository = $this->createMock(EntityRepository::class);
        $mediaRepository->expects(self::once())
            ->method('search')
            ->willReturn(new EntitySearchResult(
                'media',
                1,
                new MediaCollection([$media]),
                null,
                new Criteria(),
                $context
            ));
        $registry = $this->createMock(DefinitionInstanceRegistry::class);
        $registry->expects(self::once())
            ->method('getRepository')
            ->willReturn($mediaRepository);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([[
                'moorl_magazine_article_id' => $articleId,
                'content_a' => '<p>Unverändert</p>',
                'content_b' => '<img src="https://example.com/image.jpg" data-origin-src="https://wordpress.example/original.jpg">',
            ]]);
        $connection->expects(self::once())
            ->method('executeStatement')
            ->willReturnCallback(static function (string $sql, array $parameters): int {
                self::assertStringContainsString('`content_b` = :content_b', $sql);
                self::assertArrayHasKey('content_b', $parameters);
                self::assertArrayNotHasKey('content_a', $parameters);
                self::assertStringContainsString(
                    'data-origin-src="https://wordpress.example/original.jpg"',
                    $parameters['content_b']
                );

                return 1;
            });

        $fetcher = new HtmlContentMediaFetcher(
            $registry,
            $connection,
            $this->createMock(MediaService::class),
            $this->createMock(FileSaver::class)
        );
        (new \ReflectionProperty(HtmlContentMediaFetcher::class, 'mediaCache'))
            ->setValue($fetcher, ['https://example.com/image.jpg' => Uuid::randomHex()]);
        $updateLanguage = new \ReflectionMethod(HtmlContentMediaFetcher::class, 'updateLanguage');

        $updateLanguage->invoke(
            $fetcher,
            [Uuid::randomHex()],
            'moorl_magazine_article',
            $context,
            ['content_a', 'content_b']
        );
    }
}
