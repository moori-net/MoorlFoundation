<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

class DataExtension
{
    private ?array $globalReplacers = null;

    /**
     * @return array|null
     */
    public function getGlobalReplacers(): ?array
    {
        return $this->globalReplacers;
    }

    /**
     * @param array|null $globalReplacers
     */
    public function setGlobalReplacers(?array $globalReplacers): void
    {
        $this->globalReplacers = $globalReplacers;
    }

    public function process(): void
    {
    }

    public function getRemoveQueries(): array
    {
        return [];
    }

    public function getInstallQueries(): array
    {
        return [];
    }

    public function getInstallConfig(): array
    {
        return [];
    }

    public function getStylesheets(): array
    {
        return [];
    }

    public function getDemoPlaceholderTypes(): array
    {
        return [
            'CATEGORY',
            'PRODUCT',
            'CMS_PAGE',
            'CMS_SECTION',
            'CMS_BLOCK',
            'CMS_SLOT',
            'WILD'
        ];
    }

    public function getMediaProperties(): array
    {
        return [
            [
                'table' => null,
                'mediaFolder' => null,
                'properties' => [
                    'mediaId',
                    'previewMediaId'
                ]
            ]
        ];
    }

    public function getDemoPlaceholderCount(): int
    {
        return 500;
    }

    public function getLocalReplacers(): array
    {
        return [];
    }
}
