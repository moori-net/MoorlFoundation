<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1733279813 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1698399427;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("UPDATE `cms_slot` SET `type` = REPLACE(`type`, 'moorl-search-hero', 'moorl-hero-banner');");
        $connection->executeStatement("UPDATE `cms_slot` SET `type` = REPLACE(`type`, 'appflix-ad-hero', 'moorl-hero-banner');");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
