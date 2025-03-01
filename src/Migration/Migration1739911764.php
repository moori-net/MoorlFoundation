<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1739911764 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1739911764;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_image_map` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16),
    `active` TINYINT(1) NOT NULL,
    `technical_name` varchar(255),
    `options` JSON,
    `custom_fields` JSON,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),

    PRIMARY KEY (`id`),
    
    CONSTRAINT `json.moorl_image_map.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
    CONSTRAINT `fk.moorl_image_map.media_id` 
        FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_image_map_translation` (
    `moorl_image_map_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` longtext,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`moorl_image_map_id`, `language_id`),
    
    CONSTRAINT `fk.moorl_image_map_translation.language_id` 
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_image_map_translation.moorl_image_map_id` 
        FOREIGN KEY (`moorl_image_map_id`) 
        REFERENCES `moorl_image_map` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE     
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_image_map_item` (
    `id` BINARY(16) NOT NULL,
    `moorl_image_map_id` BINARY(16) NOT NULL,
    `moorl_marker_id` BINARY(16) NULL,
    `entity_name` varchar(255),
    `entity_id` BINARY(16),
    `svg_shape` json DEFAULT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`id`),
    
    CONSTRAINT `fk.moorl_image_map_item.moorl_image_map_id` 
        FOREIGN KEY (`moorl_image_map_id`) 
        REFERENCES `moorl_image_map` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_image_map_item.moorl_marker_id` 
        FOREIGN KEY (`moorl_marker_id`) 
        REFERENCES `moorl_marker` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_image_map_item_translation` (
    `moorl_image_map_item_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` longtext,
    `url` varchar(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    
    PRIMARY KEY (`moorl_image_map_item_id`, `language_id`),
    
    CONSTRAINT `fk.moorl_image_map_item_translation.language_id` 
        FOREIGN KEY (`language_id`) 
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_image_map_item_translation.moorl_image_map_item_id` 
        FOREIGN KEY (`moorl_image_map_item_id`) 
        REFERENCES `moorl_image_map_item` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE     
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
