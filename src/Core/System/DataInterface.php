<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

interface DataInterface
{
    public function isCleanUp(): bool;
    public function customerRequired(): bool;
    public function getType(): string;
    public function getName(): string;
    public function getPluginName(): string;
    public function getPath(): string;
    public function getCreatedAt(): string;
    public function getTables(): ?array;
    public function getShopwareTables(): ?array;
    public function getPluginTables(): ?array;
    public function getGlobalReplacers(): ?array;
    public function getReplacer(string $key, ?string $fallback = null): ?string;
    public function getLocalReplacers(): ?array;
    public function setGlobalReplacers(?array $globalReplacers): void;
    public function process(): void;
    public function getStylesheets(): array;
    /**
     * @deprecated tag:v1.4.11 Will be deleted. Use {MEDIA_FILE:path/to/file.jpg} in Future
     */
    public function getMediaProperties(): array;

    /**
     * In some Cases we have Demo Data mixed with Shopware Data.
     * There we need to put an underscore before the json content file,
     * so this file will be ignored on cleaning up the Shopware Tables.
     * In Addition we have to execute SQL manually.
     */
    public function getRemoveQueries(): array;
    /**
     * @deprecated change to getBeforeInstallQueries, getAfterInstallQueries
     */
    public function getPreInstallQueries(): array;
    public function getInstallQueries(): array;
    public function getInstallConfig(): array;
    /**
     * @deprecated tag:v1.4.16 Use {ID:XYZ123} in your JSON File instead
     */
    public function getDemoPlaceholderTypes(): array;
    /**
     * @deprecated tag:v1.4.16 Use {ID:XYZ123} in your JSON File instead
     */
    public function getDemoPlaceholderCount(): int;
}