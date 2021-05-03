<?php declare(strict_types=1);

namespace MoorlFoundation\Helper;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;

trait DemoDataHelper
{
    private function insertDemoData(ActivateContext $activateContext): void
    {
        /* Insert Demo Data according to Shopware - products, categories, etc */
        $tables = $this->shopwareTables;
        foreach ($tables as $table) {
            $data = $this->getDataFromFile($table, 'demo');
            if (!$data) {
                continue;
            }

            /** @var EntityRepositoryInterface $repository */
            $repository = $this->container->get(sprintf('%s.repository', $table));
            $repository->upsert($data, $activateContext->getContext());
        }

        /* Insert Demo Data according to DewaShop - shops, ingredients, options, etc */
        /**
         * TODO
         * - Associate Shopware Products to Dewa Badges
         */
        $tables = $this->dewaShopTables;
        foreach ($tables as $table) {
            $data = $this->getDataFromFile($table, 'demo');
            if (!$data) {
                continue;
            }

            /** @var EntityRepositoryInterface $repository */
            $repository = $this->container->get(sprintf('%s.repository', $table));
            $repository->upsert($data, $activateContext->getContext());
        }
    }
}
