<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\Struct;

class ListingStruct extends Struct
{
    protected ?EntitySearchResult $listing = null;
    protected ?array $queryParams = null;

    public function getListing(): ?EntitySearchResult
    {
        return $this->listing;
    }

    public function setListing(EntitySearchResult $listing): void
    {
        $this->listing = $listing;
    }

    public function getQueryParams(): ?array
    {
        return $this->queryParams;
    }

    public function setQueryParams(?array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_listing';
    }
}
