<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1636082647 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1636082647;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_sorting` (
    `id` BINARY(16) NOT NULL,
    `url_key` VARCHAR(255) NOT NULL,
    `entity` VARCHAR(255) NOT NULL,
    `priority` INT(11) unsigned NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `fields` JSON NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `locked` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at` DATETIME(3) NULL,
    
    PRIMARY KEY (`id`),
    
    CONSTRAINT `json.moorl_sorting.fields` CHECK (JSON_VALID(`fields`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `moorl_sorting_translation` (
    `moorl_sorting_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `label` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    
    PRIMARY KEY (`moorl_sorting_id`, `language_id`),
    
    CONSTRAINT `fk.moorl_sorting_translation.language_id` FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.moorl_sorting_translation.moorl_sorting_id` FOREIGN KEY (`moorl_sorting_id`)
        REFERENCES `moorl_sorting` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
