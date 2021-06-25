<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Doctrine\DBAL\Connection;

interface DataInterface
{
    public function getType(): string;
    public function getName(): string;
    public function getPluginName(): string;
    public function getPath(): string;
    public function getCreatedAt(): string;
    public function getTables(): ?array;
    public function getShopwareTables(): ?array;
    public function getPluginTables(): ?array;
    public function getGlobalReplacers(): ?array;
    public function getLocalReplacers(): ?array;
    public function setGlobalReplacers(?array $globalReplacers): void;
    public function process(): void;
    public function getStylesheets(): array;
    /**
     * @deprecated tag:v1.4.11 Will be deleted. Use {MEDIA_FILE:path/to/file.jpg} in Future
     */
    public function getMediaProperties(): array;

    /**
     * @return array
     *
     * In some Cases we have Demo Data mixed with Shopware Data.
     * There we need to put an underscore before the json content file,
     * so this file will be ignored on cleaning up the Shopware Tables.
     * In Addition we have to execute SQL manually.
     */
    public function getRemoveQueries(): array;
    public function getInstallQueries(): array;
    public function getInstallConfig(): array;
    public function getDemoPlaceholderTypes(): array;
    public function getDemoPlaceholderCount(): int;
}