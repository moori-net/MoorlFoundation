<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\MigrationService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
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
    public function __construct(private readonly MigrationService $migrationService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('bundle', InputArgument::REQUIRED, 'Bundle name (plugin name)')
            ->addOption('live', null, InputOption::VALUE_NONE, 'Live migration (do not create files)')
            ->addOption('drop', null, InputOption::VALUE_NONE, 'Allow to drop tables or columns')
            ->addOption('sort', null, InputOption::VALUE_NONE, 'Sort table columns');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bundleName = $input->getArgument('bundle');

        $io = new ShopwareStyle($input, $output);
        $io->title('DAL generate migration');

        $this->migrationService->setIo($io);

        $this->migrationService->createMigration(
            $bundleName,
            (bool) $input->getOption('drop'),
            (bool) $input->getOption('live'),
            (bool) $input->getOption('sort')
        );

        return self::SUCCESS;
    }
}
