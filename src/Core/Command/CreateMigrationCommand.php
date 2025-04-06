<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\MigrationService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
    public const ALL_PLUGINS = 'all';

    public function __construct(
        private readonly MigrationService $migrationService,
        private readonly EntityRepository $pluginRepo,
        private readonly PluginLifecycleService $pluginLifecycleService,
        private readonly RequirementsValidator $requirementValidator,
        private readonly KernelPluginCollection $pluginCollection,
        private readonly PluginCollection $uninstalledPlugins = new PluginCollection(),
        private ?PluginCollection $allPlugins = null
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
                $dependency = $this->getByComposerName($composerName);
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
        foreach ($this->getDependantPlugins($plugin) as $dependant) {
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

    private function getDependantPlugins(PluginEntity $plugin): array
    {
        return $this->requirementValidator->resolveActiveDependants(
            $plugin,
            $this->getEntities($this->pluginCollection->all())->getElements()
        );
    }

    private function parsePluginArgument(array $arguments, Context $context): ?PluginCollection
    {
        $plugins = array_unique($arguments);
        $criteria = new Criteria();
        $this->allPlugins = $this->pluginRepo->search($criteria, $context)->getEntities();

        if (\count($plugins) === 1 && $plugins[0] === self::ALL_PLUGINS) {
            return $this->allPlugins;
        }

        return $this->allPlugins->filter(fn (PluginEntity $plugin) => in_array(
            $plugin->getName(),
            $plugins
        ));
    }

    private function getEntities(array $plugins): PluginCollection
    {
        $names = array_map(static fn (Plugin $plugin) => $plugin->getName(), $plugins);

        return $this->allPlugins->filter(fn (PluginEntity $plugin) => in_array(
            $plugin->getName(),
            $names
        ));
    }

    private function getByComposerName(string $composerName): ?PluginEntity
    {
        return $this->allPlugins->firstWhere(fn (PluginEntity $plugin) =>
            $plugin->getComposerName() === $composerName
        );
    }
}
