<?php declare(strict_types=1);

namespace MoorlFoundation\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1698399427 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1698399427;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
UPDATE `moorl_client` SET `type` = 'filesystem-local' WHERE `type` = 'local';
UPDATE `moorl_client` SET `type` = 'filesystem-aws-s3' WHERE `type` = 'aws-s3';
UPDATE `moorl_client` SET `type` = 'filesystem-ftp' WHERE `type` = 'ftp';
UPDATE `moorl_client` SET `type` = 'filesystem-nextcloud' WHERE `type` = 'nextcloud';
UPDATE `moorl_client` SET `type` = 'filesystem-webdav' WHERE `type` = 'webdav';
UPDATE `moorl_client` SET `type` = 'api-mautic' WHERE `type` = 'mautic';
UPDATE `moorl_client` SET `type` = 'api-hubspot' WHERE `type` = 'hubspot';
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
