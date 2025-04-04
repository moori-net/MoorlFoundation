<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\MigrationService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'moorl:migration:create',
    description: 'Creates migration for entity schema',
)]
class CreateMigrationCommand extends Command
{
    public function __construct(
        private readonly MigrationService $migrationService,
        private readonly EntityRepository $pluginRepo,
        private readonly PluginLifecycleService $pluginLifecycleService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('plugins', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Plugins')
            ->addOption('live', 'l', InputOption::VALUE_NONE, 'Live migration (do not create files)')
            ->addOption('drop', 'd', InputOption::VALUE_NONE, 'Allow to drop tables or columns')
            ->addOption('sort', 's', InputOption::VALUE_NONE, 'Sort table columns')
            ->addOption('auto', 'a', InputOption::VALUE_NONE, 'Auto update plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createCLIContext();
        $io = new ShopwareStyle($input, $output);

        $plugins = $this->parsePluginArgument($input->getArgument('plugins'), $context);
        if (!$plugins) {
            return self::SUCCESS;
        }

        $this->migrationService->setIo($io);

        foreach ($plugins as $plugin) {
            $io->title('DAL generate migration for ' . $plugin->getName());

            $this->migrationService->createMigration(
                $plugin->getName(),
                (bool) $input->getOption('drop'),
                (bool) $input->getOption('live'),
                (bool) $input->getOption('sort')
            );

            if ($input->getOption('auto')) {
                $io->text('Updating plugin ' . $plugin->getName());

                $this->pluginLifecycleService->updatePlugin($plugin, $context);
            }
        }

        return self::SUCCESS;
    }

    private function parsePluginArgument(array $arguments, Context $context): ?PluginCollection
    {
        $plugins = array_unique($arguments);
        $filter = [];

        // try exact match first
        if (\count($plugins) === 1) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('name', $plugins[0]));

            /** @var PluginCollection $matches */
            $matches = $this->pluginRepo->search($criteria, $context)->getEntities();
            if ($matches->count() === 1) {
                return $matches;
            }
        }

        foreach ($plugins as $plugin) {
            $filter[] = new ContainsFilter('name', $plugin);
        }

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('name'));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filter));

        /** @var PluginCollection $pluginCollection */
        $pluginCollection = $this->pluginRepo->search($criteria, $context)->getEntities();

        if ($pluginCollection->count() <= 1) {
            return $pluginCollection;
        }

        return $pluginCollection;
    }
}
