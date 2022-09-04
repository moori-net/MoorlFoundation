<?php declare(strict_types=1);

namespace MoorlFoundation\Data;

use MoorlFoundation\Core\System\DataExtension;
use MoorlFoundation\Core\System\DataInterface;
use MoorlFoundation\MoorlFoundation;

class Data extends DataExtension implements DataInterface
{
    public function getTables(): ?array
    {
        return array_merge(
            $this->getShopwareTables(),
            $this->getPluginTables()
        );
    }

    public function getShopwareTables(): ?array
    {
        return MoorlFoundation::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return MoorlFoundation::PLUGIN_TABLES;
    }

    public function getPluginName(): string
    {
        return MoorlFoundation::NAME;
    }

    public function getCreatedAt(): string
    {
        return MoorlFoundation::DATA_CREATED_AT;
    }

    public function getName(): string
    {
        return 'data';
    }

    public function getType(): string
    {
        return 'data';
    }

    public function getPath(): string
    {
        return __DIR__;
    }
}
