<?php declare(strict_types=1);

namespace MoorlFoundation\Helper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Doctrine\DBAL\Connection;
use Shopware\Core\System\Tax\TaxCollection;

trait PluginDataHelper
{
    private function insertPluginData(InstallContext $installContext): void
    {
        if (!isset($this->shopwareTables)) {
            return;
        }

        foreach ($this->shopwareTables as $table) {
            $data = $this->getDataFromFile($table, 'plugin');
            if (!$data) {
                continue;
            }
            /** @var EntityRepositoryInterface $repository */
            $repository = $this->container->get(sprintf('%s.repository', $table));
            $repository->upsert($data, $installContext->getContext());
        }
    }

    private function removePluginData(): void
    {
        if (isset($this->pluginTables)) {
            $this->dropTables($this->pluginTables);
        }

        if (isset($this->shopwareTables) && isset($this->createdAtIdentifier)) {
            $connection = $this->container->get(Connection::class);

            foreach (array_reverse($this->shopwareTables) as $table) {
                $sql = sprintf("DELETE FROM `%s` WHERE `created_at` = '%s';", $table, $this->createdAtIdentifier);
                try {
                    $connection->executeUpdate($sql);
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
    }

    private function dropTables($tables): void
    {
        $connection = $this->container->get(Connection::class);

        foreach ($tables as $table) {
            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `%s`;', $table);
            $connection->executeUpdate($sql);
        }
    }

    private function getDataFromFile(string $table, string $type): ?array
    {
        $fileName = sprintf('%s/%s-data/%s.json', $this->getBasePath(), $type, $table);
        if (!file_exists($fileName)) {
            return null;
        }

        $data = json_decode(strtr(file_get_contents($fileName), $this->getGlobalReplacers()), true);

        $this->enrichData($data, $table);

        return $data;
    }

    private function enrichData(&$data, $table): void
    {
        if (!is_array($data) || !isset($this->createdAtIdentifier)) {
            return;
        }

        foreach ($data as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!isset($item['id']) && !isset($item['salesChannelId'])) {
                $item['id'] = md5(serialize($item));
            }
            if (isset($item['mediaId'])) {
                $item['mediaId'] = $this->getMediaId($item['mediaId'], $table);
            }
            if (isset($item['cover']) && isset($item['cover']['mediaId'])) {
                $item['cover']['mediaId'] = $this->getMediaId($item['cover']['mediaId'], $table);
                $item['cover']['id'] = md5($item['id']);
            }
            if (isset($item['price']) && isset($item['taxId'])) {
                $item['price'] = [
                    $this->enrichPrice($item['price'], $item['taxId'])
                ];
            }
            $item['createdAt'] = $this->createdAtIdentifier;
            foreach ($item as &$value) {
                if (is_array($value) && count($value) > 0) {
                    $this->enrichData($value, $table);
                }
            }
        }
    }

    private function getGlobalReplacers(): array
    {
        if (isset($this->globalReplacers) && $this->globalReplacers) {
            return $this->globalReplacers;
        }

        $connection = $this->container->get(Connection::class);

        $globalReplacers = [
            '{LANGUAGE_ID}' => Defaults::LANGUAGE_SYSTEM,
            '{CURRENCY_ID}' => Defaults::CURRENCY,
            '{VERSION_ID}' => Defaults::LIVE_VERSION
        ];

        $sql = "SELECT LOWER(HEX(`rule_id`)) AS `id` FROM `rule_condition` WHERE `type` = 'alwaysValid';";
        $globalReplacers['{RULE_ID}'] = $connection->executeQuery($sql)->fetchColumn();

        $sql = "SELECT LOWER(HEX(`id`)) AS `id` FROM `delivery_time` LIMIT 1;";
        $globalReplacers['{DELIVERY_TIME_ID}'] = $connection->executeQuery($sql)->fetchColumn();

        $sql = "SELECT LOWER(HEX(`id`)) AS `id` FROM `tax` ORDER BY `tax_rate` DESC LIMIT 2;";
        $query = $connection->executeQuery($sql);
        $globalReplacers['{TAX_ID_STANDARD}'] = $query->fetchColumn();
        $globalReplacers['{TAX_ID_REDUCED}'] = $query->fetchColumn();

        $sql = sprintf(
            "SELECT LOWER(HEX(`id`)) AS `id`, LOWER(HEX(`navigation_category_id`)) AS `categoryId` FROM `sales_channel` WHERE `type_id` = UNHEX('%s');",
            Defaults::SALES_CHANNEL_TYPE_STOREFRONT
        );
        $query = $connection->executeQuery($sql)->fetchAssociative();
        $globalReplacers['{SALES_CHANNEL_ID}'] = $query['id'];
        $globalReplacers['{NAVIGATION_CATEGORY_ID}'] = $query['categoryId'];

        foreach (['CATEGORY','PRODUCT'] as $type) {
            for ($x = 0; $x < 10; $x++) {
                $key = sprintf("{DEMO_%s_%d}", $type, $x);
                $globalReplacers[$key] = md5($key);
            }
        }

        $this->globalReplacers = $globalReplacers;
        return $globalReplacers;
    }

    private function getTaxes(): TaxCollection
    {
        if (isset($this->taxes)) {
            return $this->taxes;
        }
        /** @var EntityRepositoryInterface $repo */
        $repo = $this->container->get('tax.repository');
        $criteria = new Criteria();
        $criteria->addSorting(New FieldSorting('taxRate', FieldSorting::DESCENDING));
        /** @var TaxCollection $taxes */
        $taxes = $repo->search($criteria, $this->getContext())->getEntities();
        $this->taxes = $taxes;
        return $taxes;
    }

    private function enrichPrice(float $price, string $taxId): ?array
    {
        return [
            'currencyId' => Defaults::CURRENCY,
            'net' => $price / 100 * (100 - $this->getTaxes()->get($taxId)->getTaxRate()),
            'gross' => $price,
            'linked' => true
        ];
    }

    private function getContext(): Context
    {
        if (isset($this->context)) {
            return $this->context;
        }

        return Context::createDefaultContext();
    }
}
