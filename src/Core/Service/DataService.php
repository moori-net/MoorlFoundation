<?php

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\System\DataInterface;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Theme\ThemeService;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class DataService
{
    private Connection $connection;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private SystemConfigService $systemConfigService;
    private MediaService $mediaService;
    private FileSaver $fileSaver;
    private Context $context;
    private ClientInterface $client;
    private ?string $salesChannelId = null;
    private ?string $themeId = null;
    private string $projectDir;
    private array $mediaCache = [];
    private EntityCollection $taxes;
    private FilesystemInterface $filesystem;
    private ThemeService $themeService;
    /**
     * @var DataInterface[]
     */
    private iterable $dataObjects;

    public function __construct(
        Connection $connection,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        SystemConfigService $systemConfigService,
        MediaService $mediaService,
        FileSaver $fileSaver,
        FilesystemInterface $filesystem,
        ThemeService $themeService,
        string $projectDir,
        iterable $dataObjects
    )
    {
        $this->connection = $connection;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->systemConfigService = $systemConfigService;
        $this->mediaService = $mediaService;
        $this->fileSaver = $fileSaver;
        $this->dataObjects = $dataObjects;
        $this->filesystem = $filesystem;
        $this->themeService = $themeService;
        $this->projectDir = $projectDir;

        $this->context = Context::createDefaultContext();
        $this->client = new Client([
            'timeout' => 200,
            'allow_redirects' => false,
        ]);
    }

    /**
     * @return string|null
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string|null $salesChannelId
     */
    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getOptions(string $type = 'demo'): array
    {
        $options = [];

        foreach ($this->dataObjects as $dataObject) {
            if ($dataObject->getType() !== $type) {
                continue;
            }

            $options[] = [
                'name' => $dataObject->getName(),
                'pluginName' => $dataObject->getPluginName(),
                'type' => $dataObject->getType()
            ];
        }

        return $options;
    }

    public function getOptionsByPluginName(string $pluginName, string $type = 'demo'): array
    {
        $options = [];

        foreach ($this->dataObjects as $dataObject) {
            if ($pluginName !== $dataObject->getPluginName()) {
                continue;
            }
            if ($dataObject->getType() !== $type) {
                continue;
            }

            $options[] = $dataObject->getName();
        }

        return $options;
    }

    public function install(string $pluginName, string $type = 'data', ?string $name = null): void
    {
        $this->initTaxes();

        foreach ($this->dataObjects as $dataObject) {
            if ($pluginName !== $dataObject->getPluginName()) {
                continue;
            }

            if ($dataObject->getType() !== $type) {
                continue;
            }

            if ($name && $name !== $dataObject->getName()) {
                continue;
            }

            $this->initGlobalReplacers($dataObject);
            $this->insertContent($dataObject);
            $this->copyAssets($dataObject);
            $this->addStylesheets($dataObject);

            foreach ($dataObject->getInstallQueries() as $sql) {
                $sql = strtr($sql, $dataObject->getGlobalReplacers());
                $this->connection->executeUpdate($sql);
            }

            foreach ($dataObject->getInstallConfig() as $k => $v) {
                $this->systemConfigService->set($k, $v);
            }

            $dataObject->process();

            if ($this->themeId && $this->salesChannelId) {
                $this->themeService->compileTheme(
                    $this->salesChannelId,
                    $this->themeId,
                    $this->context
                );
            }
        }
    }

    private function getTargetDir(DataInterface $dataObject, bool $isBundle = false): string
    {
        if ($isBundle) {
            return sprintf('bundles/%s/', strtolower($dataObject->getPluginName()));
        }

        return '';
    }

    private function addStylesheets(DataInterface $dataObject, string $type = 'fontFaces'): void
    {
        $cfgKey = sprintf('%s.config.%s', $dataObject->getPluginName(), $type);
        $fontFaces = $this->systemConfigService->get($cfgKey);
        $targetDir = $this->getTargetDir($dataObject);

        foreach ($dataObject->getStylesheets() as $stylesheet) {
            if($fontFaces && strpos($fontFaces, $stylesheet) !== false) {
                continue;
            }

            $append = <<<TWIG
<link rel="stylesheet" href="{{ asset('%s%s') }}">
TWIG;
            $fontFaces = $fontFaces . sprintf($append, $targetDir, $stylesheet);
        }

        $this->systemConfigService->set($cfgKey, $fontFaces);
    }

    private function copyAssets(DataInterface $dataObject): void
    {
        $targetDir = $this->getTargetDir($dataObject);
        $originDir = sprintf('%s/public', $dataObject->getPath());

        if (!is_dir($originDir)) {
            return;
        }

        $this->filesystem->createDir($targetDir);

        $files = Finder::create()
            ->ignoreDotFiles(false)
            ->files()
            ->in($originDir)
            ->getIterator();

        foreach ($files as $file) {
            $fs = fopen($file->getPathname(), 'rb');
            $this->filesystem->putStream($targetDir . $file->getRelativePathname(), $fs);
            if (is_resource($fs)) {
                fclose($fs);
            }
        }
    }

    private function initTaxes(): void
    {
        /** @var EntityRepositoryInterface $repo */
        $repo = $this->definitionInstanceRegistry->getRepository('tax');
        $criteria = new Criteria();
        $criteria->addSorting(New FieldSorting('taxRate', FieldSorting::DESCENDING));
        $this->taxes = $repo->search($criteria, $this->context)->getEntities();
    }

    private function initGlobalReplacers(DataInterface $dataObject): void
    {
        if ($dataObject->getGlobalReplacers()) {
            return;
        }

        $globalReplacers = [
            '{DATA_CREATED_AT}' => $dataObject->getCreatedAt(),
            '{LANGUAGE_ID}' => Defaults::LANGUAGE_SYSTEM,
            '{CURRENCY_ID}' => Defaults::CURRENCY,
            '{VERSION_ID}' => Defaults::LIVE_VERSION,
            '{LOREM_IPSUM_50}' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
        ];

        $sql = sprintf(
            "SELECT LOWER(HEX(`id`)) AS `id` FROM `theme` WHERE `technical_name` = '%s';",
            $dataObject->getPluginName()
        );
        $this->themeId = $this->connection->executeQuery($sql)->fetchColumn();
        $globalReplacers['{THEME_ID}'] = $this->themeId;

        $sql = "SELECT LOWER(HEX(`rule_id`)) AS `id` FROM `rule_condition` WHERE `type` = 'alwaysValid';";
        $globalReplacers['{RULE_ID}'] = $this->connection->executeQuery($sql)->fetchColumn();

        $sql = "SELECT LOWER(HEX(`id`)) AS `id` FROM `delivery_time` LIMIT 1;";
        $globalReplacers['{DELIVERY_TIME_ID}'] = $this->connection->executeQuery($sql)->fetchColumn();

        $sql = "SELECT LOWER(HEX(`id`)) AS `id` FROM `tax` ORDER BY `tax_rate` DESC LIMIT 2;";
        $query = $this->connection->executeQuery($sql);
        $globalReplacers['{TAX_ID_STANDARD}'] = $query->fetchColumn();
        $globalReplacers['{TAX_ID_REDUCED}'] = $query->fetchColumn();

        $sql = "SELECT LOWER(HEX(`id`)) AS `id` FROM `tax` ORDER BY `tax_rate` DESC LIMIT 2;";
        $query = $this->connection->executeQuery($sql);
        $globalReplacers['{TAX_ID_STANDARD}'] = $query->fetchColumn();
        $globalReplacers['{TAX_ID_REDUCED}'] = $query->fetchColumn();

        $sql = "SELECT LOWER(HEX(`language`.`id`)) AS `id`, `locale`.`code` AS `code` FROM `language` LEFT JOIN `locale` ON `locale`.`id` = `language`.`locale_id`";
        $query = $this->connection->executeQuery($sql);
        while (($row = $query->fetchAssociative()) !== false) {
            $globalReplacers[sprintf("{%s}", $row['code'])] = $row['id'];
        }

        if ($this->salesChannelId) {
            $sql = sprintf(
                "SELECT LOWER(HEX(`id`)) AS `id`, LOWER(HEX(`navigation_category_id`)) AS `categoryId` FROM `sales_channel` WHERE `id` = UNHEX('%s');",
                $this->salesChannelId
            );
        } else {
            $sql = sprintf(
                "SELECT LOWER(HEX(`id`)) AS `id`, LOWER(HEX(`navigation_category_id`)) AS `categoryId` FROM `sales_channel` WHERE `type_id` = UNHEX('%s');",
                Defaults::SALES_CHANNEL_TYPE_STOREFRONT
            );
        }
        $query = $this->connection->executeQuery($sql)->fetchAssociative();
        $globalReplacers['{SALES_CHANNEL_ID}'] = $query['id'];
        $globalReplacers['{NAVIGATION_CATEGORY_ID}'] = $query['categoryId'];

        $demoPlaceholderTypes = $dataObject->getDemoPlaceholderTypes();

        foreach ($demoPlaceholderTypes as $type) {
            for ($x = 0; $x < $dataObject->getDemoPlaceholderCount(); $x++) {
                $key = sprintf("{DEMO_%s_%d}", $type, $x);
                $globalReplacers[$key] = md5($dataObject->getPluginName() . $key);
            }
        }

        $globalReplacers = array_merge($globalReplacers, $dataObject->getLocalReplacers());

        $dataObject->setGlobalReplacers($globalReplacers);
    }

    private function insertContent(DataInterface $dataObject): void
    {
        foreach ($dataObject->getTables() as $table) {
            $data = $this->getContentFromFile($table, $dataObject);
            if (!$data) {
                continue;
            }

            /** @var EntityRepositoryInterface $repository */
            $repository = $this->definitionInstanceRegistry->getRepository($table);
            $repository->upsert($data, $this->context);
        }
    }

    private function getContentFromFile(string $table, DataInterface $dataObject): ?array
    {
        $fileName = sprintf('%s/content/%s.json', $dataObject->getPath(), $table);
        if (!file_exists($fileName)) {
            /* This File with the "_" is protected from deletion */
            $fileName = sprintf('%s/content/_%s.json', $dataObject->getPath(), $table);
            if (!file_exists($fileName)) {
                return null;
            }
        }

        $data = json_decode(strtr(file_get_contents($fileName), $dataObject->getGlobalReplacers()), true);

        $this->enrichData($data, $table, $dataObject);

        return $data;
    }

    private function enrichThemeConfig(&$data, string $table, DataInterface $dataObject): void
    {
        foreach ($data as &$item) {
            if (!empty($item['value'])) {
                $mediaId = $this->getMediaId($item['value'], $table, $dataObject);

                if ($mediaId) {
                    $item['value'] = $mediaId;
                }
            }
        }
    }

    private function enrichData(&$data, string $table, DataInterface $dataObject): void
    {
        if (!is_array($data)) {
            if (is_string($data)) {
                preg_match('/{READ_FILE:(.*)}/', $data, $matches, PREG_UNMATCHED_AS_NULL);
                if (!empty($matches[1])) {
                    $filePath = sprintf('%s/%s', $dataObject->getPath(), $matches[1]);

                    if (file_exists($filePath)) {
                        $data = file_get_contents($filePath);
                    }
                }
            }
            return;
        }
        foreach ($data as &$item) {
            if (!is_array($item)) {
                continue;
            }
            /* Handle Translations */
            if (!empty($item['translations']) && is_array($item['translations'])) {
                /* First Entry is always Default */
                $firstKey = array_key_first($item['translations']);
                $merge = $item['translations'][$firstKey];
                /* Remove unused Translations */
                foreach ($item['translations'] as $id => $translation) {
                    preg_match('/{[a-z]{2}-[A-Z]{2}}/', $id, $matches, PREG_UNMATCHED_AS_NULL);
                    if (!empty($matches[0])) {
                        unset($item['translations'][$id]);
                        continue;
                    }
                    if ($id === Defaults::LANGUAGE_SYSTEM) {
                        $firstKey = null;
                    }
                }
                /* Fallback if Translations are unknown */
                if ($firstKey) {
                    if (is_array($merge)) {
                        $item = array_merge($item, $merge);
                    }
                }
            }
            if ($table === 'theme' && !empty($item['configValues'])) {
                $this->enrichThemeConfig($item['configValues'], $table, $dataObject);
                continue;
            }
            if ($table === 'cms_page' && !empty($item['config'])) {
                $this->enrichThemeConfig($item['config'], $table, $dataObject);

                if (!isset($item['id'])) {
                    $item['id'] = md5(serialize($item));
                }
                continue;
            }
            if (isset($item['_skipEnrichData'])) {
                unset($item['_skipEnrichData']);
                continue;
            }
            if (!isset($item['id']) && !isset($item['salesChannelId'])) {
                $item['id'] = md5(serialize($item));
            }
            if (isset($item['mediaId'])) {
                $item['mediaId'] = $this->getMediaId($item['mediaId'], $table, $dataObject);
            }
            if (isset($item['previewMediaId'])) {
                $item['previewMediaId'] = $this->getMediaId($item['previewMediaId'], $table, $dataObject);
            }
            if (isset($item['cover']) && isset($item['cover']['mediaId'])) {
                $item['cover']['mediaId'] = $this->getMediaId($item['cover']['mediaId'], 'product', $dataObject);
                $item['cover']['id'] = md5($item['id']);
            }
            if (isset($item['price']) && isset($item['taxId'])) {
                $item['price'] = [
                    $this->enrichPrice($item['price'], $item['taxId'])
                ];
            }
            $item['createdAt'] = $dataObject->getCreatedAt();
            foreach ($item as &$value) {
                $this->enrichData($value, $table, $dataObject);
            }
        }
    }

    private function fetchFileFromURL(string $url, string $extension): MediaFile
    {
        $request = new Request();
        $request->query->set('url', $url);
        $request->query->set('extension', $extension);
        $request->request->set('url', $url);
        $request->request->set('extension', $extension);
        $request->headers->set('content-type', 'application/json');

        return $this->mediaService->fetchFile($request);
    }

    private function getMediaIdFromUrl(string $name, string $table, DataInterface $dataObject): ?string
    {
        if (isset($this->mediaCache[$name])) {
            return $this->mediaCache[$name];
        }

        $name = str_replace('http:', 'https:', $name);
        $query = explode("?", $name);
        $basename = basename($query[0]);
        $fileInfo = pathinfo($basename);
        $filename = $fileInfo['filename'];
        $extension = $fileInfo['extension'];

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('fileName', $filename),
            new EqualsFilter('fileExtension', $extension),
        );

        $repository = $this->definitionInstanceRegistry->getRepository('media');
        $media = $repository->search($criteria, $this->context)->first();

        if ($media) {
            $mediaId = $media->getId();
        } else {
            $mediaId = $this->mediaService->createMediaInFolder($table, $this->context, false);

            try {
                $uploadedFile = $this->fetchFileFromURL($query[0], $extension);
                $this->fileSaver->persistFileToMedia(
                    $uploadedFile,
                    $filename,
                    $mediaId,
                    $this->context
                );
            } finally {
            }
        }

        $this->mediaCache[$name] = $mediaId;

        return $mediaId;
    }

    private function getMediaId(string $name, string $table, DataInterface $dataObject): ?string
    {
        if (isset($this->mediaCache[$name])) {
            return $this->mediaCache[$name];
        }

        if (strpos($name, 'http') === 0) {
            return $this->getMediaIdFromUrl($name, $table, $dataObject);
        }

        $filePath = sprintf('%s/media/%s', $dataObject->getPath(), $name);
        if (!file_exists($filePath)) {
            $filePath = sprintf('%s/media/%s.jpg', $dataObject->getPath(), $name);
        }
        if (!file_exists($filePath)) {
            return null;
        }

        $file = new File($filePath);
        $fileName = pathinfo($file->getFilename(), \PATHINFO_FILENAME);

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('fileName', $fileName),
            new EqualsFilter('fileExtension', $file->getExtension()),
        );

        $repository = $this->definitionInstanceRegistry->getRepository('media');
        $media = $repository->search($criteria, $this->context)->first();

        if ($media) {
            $mediaId = $media->getId();
        } else {
            $mediaId = $this->mediaService->saveFile(
                $file->getContent(),
                $file->getExtension(),
                $file->getMimeType(),
                $fileName,
                $this->context,
                $table,
                null,
                false
            );
        }

        $this->mediaCache[$name] = $mediaId;

        return $mediaId;
    }

    private function enrichPrice(float $price, string $taxId): ?array
    {
        return [
            'currencyId' => Defaults::CURRENCY,
            'net' => $price / 100 * (100 - $this->taxes->get($taxId)->getTaxRate()),
            'gross' => $price,
            'linked' => true
        ];
    }

    public function remove(string $pluginName, ?string $type = null, ?string $name = null): void
    {
        foreach ($this->dataObjects as $dataObject) {
            if ($pluginName !== $dataObject->getPluginName()) {
                continue;
            }

            if ($type && $dataObject->getType() !== $type) {
                continue;
            }

            if ($name && $name !== $dataObject->getName()) {
                continue;
            }

            $this->initGlobalReplacers($dataObject);
            $this->cleanUpPluginTables($dataObject);
            $this->cleanUpShopwareTables($dataObject);

            foreach ($dataObject->getRemoveQueries() as $sql) {
                $sql = strtr($sql, $dataObject->getGlobalReplacers());
                $this->connection->executeUpdate($sql);
            }
        }
    }

    private function cleanUpPluginTables(DataInterface $dataObject): void
    {
        if (!$dataObject->getPluginTables()) {
            return;
        }

        foreach (array_reverse($dataObject->getPluginTables()) as $table) {
            if (!$this->contentFileExists($table, $dataObject)) {
                continue;
            }

            $sql = sprintf(
                "DELETE FROM `%s` WHERE `created_at` = '%s';",
                $table,
                $dataObject->getCreatedAt()
            );
            try {
                $this->connection->executeUpdate($sql);
            } catch (\Exception $exception) {
                continue;
            }
        }
    }

    private function cleanUpShopwareTables(DataInterface $dataObject): void
    {
        if (!$dataObject->getShopwareTables()) {
            return;
        }

        foreach (array_reverse($dataObject->getShopwareTables()) as $table) {
            if (!$this->contentFileExists($table, $dataObject)) {
                continue;
            }

            $sql = sprintf(
                "DELETE FROM `%s` WHERE `created_at` = '%s';",
                $table,
                $dataObject->getCreatedAt()
            );
            try {
                $this->connection->executeUpdate($sql);
            } catch (\Exception $exception) {
                continue;
            }
        }
    }

    private function contentFileExists(string $table, DataInterface $dataObject): bool
    {
        return file_exists(sprintf('%s/content/%s.json', $dataObject->getPath(), $table));
    }

    private function dropTables(DataInterface $dataObject): void
    {
        if (!$dataObject->getPluginTables()) {
            return;
        }

        foreach ($dataObject->getPluginTables() as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $this->connection->executeUpdate($sql);
        }
    }
}
