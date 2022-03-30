<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Media\Cms;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Struct\Struct;

class DownloadListStruct extends Struct
{
    protected ?EntityCollection $downloads = null;

    /**
     * @return EntityCollection|null
     */
    public function getDownloads(): ?EntityCollection
    {
        return $this->downloads;
    }

    /**
     * @param EntityCollection|null $downloads
     */
    public function setDownloads(?EntityCollection $downloads): void
    {
        $this->downloads = $downloads;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_download_list';
    }
}
