<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594420156 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594420156;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_foundation_article` (
    `id` BINARY(16) NOT NULL,
    `media_url` varchar(255),
    `article_url` varchar(255),
    `date` DATE,
    `author` varchar(255),
    `title` varchar(255),
    `teaser` longtext DEFAULT NULL,
    `content` longtext DEFAULT NULL,
    `has_seen` TINYINT,
    `created_at` DATETIME(3),
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
