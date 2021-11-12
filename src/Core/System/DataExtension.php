<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

class DataExtension
{
    private ?array $globalReplacers = null;

    /**
     * @param string $key
     * @param string|null $fallback
     * @return string|null
     */
    public function getReplacer(string $key, ?string $fallback = null): ?string
    {
        $key = sprintf("{%s}", strtoupper($key));

        return isset($this->globalReplacers[$key]) ? $this->globalReplacers[$key] : $fallback;
    }

    /**
     * @return bool
     */
    public function customerRequired(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCleanUp(): bool
    {
        return true;
    }

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

    public function getPreInstallQueries(): array
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

    public function getTables(): ?array
    {
        return [];
    }

    public function getShopwareTables(): ?array
    {
        return [];
    }

    public function getPluginTables(): ?array
    {
        return [];
    }

    /**
     * @deprecated tag:v1.4.16 Use {ID:XYZ123} in your JSON File instead
     */
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

    /**
     * @deprecated tag:v1.4.11 Will be deleted. Use {MEDIA_FILE:path/to/file.jpg} in Future
     */
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

    /**
     * @deprecated tag:v1.4.16 Use {ID:XYZ123} in your JSON File instead
     */
    public function getDemoPlaceholderCount(): int
    {
        return 500;
    }

    public function getLocalReplacers(): array
    {
        return [];
    }

    public function getName(): string
    {
        return 'standard';
    }

    public function getType(): string
    {
        return 'demo';
    }
}
