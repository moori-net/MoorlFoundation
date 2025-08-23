<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\Flysystem\FilesystemAdapter;

interface ClientFilesystemInterface
{
    public function getClientAdapter(): ?FilesystemAdapter;
}
