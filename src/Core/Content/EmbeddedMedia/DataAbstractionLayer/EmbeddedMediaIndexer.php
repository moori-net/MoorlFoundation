<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia\DataAbstractionLayer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityIndexerTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Uuid\Uuid;

class EmbeddedMediaIndexer extends EntityIndexer
{
    use EntityIndexerTrait;

    public function __construct(
        protected Connection $connection,
        protected IteratorFactory $iteratorFactory,
        protected EntityRepository $repository
    ) {
    }

    public function getName(): string
    {
        return 'moorl_media.indexer';
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $sql = "SELECT `id`, `embedded_url` FROM `moorl_media` WHERE `id` IN (:ids) AND `embedded_url` IS NOT NULL AND `type` = 'auto'";

        $data = $this->connection->fetchAllAssociative(
            $sql,
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );

        foreach ($data as $item) {
            $metadata = $this->fetchMetadata($item['embedded_url']);

            $sql = "UPDATE `moorl_media` SET `embedded_id` = :embedded_id, `embedded_url` = :embedded_url, `type` = :type WHERE `id` = :id;";

            $this->connection->executeStatement($sql, ['id' => $item['id'], ...$metadata]);
        }
    }

    private function fetchMetadata(string $embeddedUrl): array
    {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $embeddedUrl, $match)) {
            return [
                'type' => 'youtube',
                'embedded_id' => $match[1],
                'embedded_url' => "https://www.youtube-nocookie.com/embed/" . $match[1] . "?rel=0&enablejsapi=1&version=3&playerapiid=ytplayer",
            ];
        }

        if (preg_match('/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $embeddedUrl, $match)) {
            return [
                'type' => 'vimeo',
                'embedded_id' => $match[3],
                'embedded_url' => "https://player.vimeo.com/video/" . $match[3] . "?dnt=1"
            ];
        }

        return [
            'type' => 'embedded',
            'embedded_id' => null,
            'embedded_url' => $embeddedUrl
        ];
    }
}
