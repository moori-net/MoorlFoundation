<?php declare(strict_types=1);

namespace MoorlFoundation\Demo\CmsTools;

use MoorlFoundation\Core\System\DataExtension;
use MoorlFoundation\Core\System\DataInterface;
use MoorlFoundation\MoorlFoundation;

class DemoCmsTools extends DataExtension implements DataInterface
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
        return [
            'cms_page',
            'category'
        ];
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
        return 'cms-tools';
    }

    public function getType(): string
    {
        return 'demo';
    }

    public function getPath(): string
    {
        return __DIR__;
    }
}
