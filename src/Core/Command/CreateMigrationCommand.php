<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\MigrationService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\KernelPluginCollection;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Shopware\Core\Framework\Plugin\Requirement\RequirementsValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Framework\Plugin\Exception\PluginNotInstalledException;
use Shopware\Core\Framework\Plugin\Requirement\Exception\RequirementStackException;
use Shopware\Core\Framework\Plugin\Requirement\Exception\MissingRequirementException;

#[AsCommand(
    name: 'moorl:migration:create',
    description: 'Creates migration for entity schema',
)]
class CreateMigrationCommand extends Command
{
    public function __construct(
        private readonly MigrationService $migrationService,
        private readonly EntityRepository $pluginRepo,
        private readonly PluginLifecycleService $pluginLifecycleService,
        private readonly RequirementsValidator $requirementValidator,
        private readonly KernelPluginCollection $pluginCollection,
        private PluginCollection $uninstalledPlugins = new PluginCollection()
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('plugins', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Plugins')
            ->addOption(
                'mode',
                'm',
                InputOption::VALUE_REQUIRED,
                'Mode',
                MigrationService::MODE_DRY,
                [
                    MigrationService::MODE_DRY,
                    MigrationService::MODE_LIVE,
                    MigrationService::MODE_FILE,
                    MigrationService::MODE_CLEANUP
                ]
            )
            ->addOption('drop', 'r', InputOption::VALUE_NONE, 'Allow to drop tables or columns')
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

        $mode = $input->getOption('mode');

        $io->title($plugins->count() . ' plugins found');

        $this->migrationService->setIo($io);

        foreach ($plugins as $plugin) {
            $io->title('DAL generate migration for ' . $plugin->getName());

            $actionRequired = $this->migrationService->createMigration(
                $plugin->getName(),
                $mode,
                (bool) $input->getOption('drop'),
                (bool) $input->getOption('sort')
            );

            if ($actionRequired && $input->getOption('auto')) {
                if ($mode === MigrationService::MODE_CLEANUP) {
                    // Used migration files have been deleted. The plugin have to be re-installed
                    $io->text('Refresh plugin ' . $plugin->getName());
                    $this->refreshPluginWithDependencies($plugin, $context, $io);
                } elseif ($mode === MigrationService::MODE_FILE) {
                    $io->text('Update plugin ' . $plugin->getName());
                    $this->pluginLifecycleService->updatePlugin($plugin, $context);
                }
            }
        }

        $this->installAll($context, $io);

        return self::SUCCESS;
    }

    private function refreshPluginWithDependencies(PluginEntity $plugin, Context $context, ShopwareStyle $io): void
    {
        $this->uninstallDependencies($plugin, $context, $io);
    }

    private function installAll(Context $context, ShopwareStyle $io): void
    {
        foreach ($this->uninstalledPlugins as $disabledPlugin) {
            $this->installDependencies($disabledPlugin, $context, $io);
        }
    }

    private function installDependencies(PluginEntity $plugin, Context $context, ShopwareStyle $io): void
    {
        $dependencies = [];

        try {
            $this->requirementValidator->validateRequirements($plugin, $context, 'install');
        } catch (RequirementStackException $exception) {
            /** @var MissingRequirementException $requirement */
            foreach ($exception->getRequirements() as $requirement) {
                $composerName = $requirement->getParameter('requirement');
                $dependency = $this->getByComposerName($composerName, $context);
                if (!$dependency) {
                    $io->warning(sprintf("Dependency not found: %s", $composerName));
                    continue;
                }
                $dependencies[] = $dependency;
                $this->uninstalledPlugins->add($dependency);
            }
        }

        if (!$this->uninstalledPlugins->has($plugin->getId())) {
            return;
        }

        foreach ($dependencies as $dependency) {
            $this->installDependencies($dependency, $context, $io);
        }

        $io->text('Install plugin ' . $plugin->getName());
        $this->pluginLifecycleService->installPlugin($plugin, $context);
        $io->text('Activate plugin ' . $plugin->getName());
        $this->pluginLifecycleService->activatePlugin($plugin, $context);
        $this->uninstalledPlugins->remove($plugin->getId());
    }

    private function uninstallDependencies(PluginEntity $plugin, Context $context, ShopwareStyle $io): void
    {
        foreach ($this->getDependantPlugins($plugin, $context) as $dependant) {
            $this->uninstallDependencies($dependant, $context, $io);
        }
        if ($this->uninstalledPlugins->has($plugin->getId())) {
            return;
        }

        $io->text('Uninstall plugin ' . $plugin->getName());

        try {
            $this->pluginLifecycleService->uninstallPlugin($plugin, $context);
        } catch (PluginNotInstalledException $exception) {
        }

        $this->uninstalledPlugins->add($plugin);
    }

    private function getDependantPlugins(PluginEntity $plugin, Context $context): array
    {
        $dependantPlugins = $this->getEntities($this->pluginCollection->all(), $context)->getEntities()->getElements();

        return $this->requirementValidator->resolveActiveDependants(
            $plugin,
            $dependantPlugins
        );
    }

    private function parsePluginArgument(array $arguments, Context $context): ?PluginCollection
    {
        $plugins = array_unique($arguments);
        $filter = [];

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('installedAt', FieldSorting::DESCENDING));

        // try exact match first
        if (\count($plugins) === 1) {
            if ($plugins[0] === "_") {
                return $this->pluginRepo->search($criteria, $context)->getEntities();
            }

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

        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filter));

        /** @var PluginCollection $pluginCollection */
        $pluginCollection = $this->pluginRepo->search($criteria, $context)->getEntities();

        if ($pluginCollection->count() <= 1) {
            return $pluginCollection;
        }

        return $pluginCollection;
    }

    private function getEntities(array $plugins, Context $context): EntitySearchResult
    {
        $names = array_map(static fn (Plugin $plugin) => $plugin->getName(), $plugins);

        return $this->pluginRepo->search(
            (new Criteria())->addFilter(new EqualsAnyFilter('name', $names)),
            $context
        );
    }

    private function getByComposerName(string $composerName, Context $context): ?PluginEntity
    {
        return $this->pluginRepo->search(
            (new Criteria())->addFilter(new EqualsFilter('composerName', $composerName)),
            $context
        )->first();
    }
}
