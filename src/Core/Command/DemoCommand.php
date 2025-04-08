<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\DataService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'moorl:demo')]
class DemoCommand extends Command
{
    public function __construct(
        private readonly DataService $dataService,
        protected ?OutputInterface $output = null
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('plugin', InputArgument::REQUIRED, 'Plugin')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $context = Context::createCLIContext();
        $io = new ShopwareStyle($input, $output);

        $plugin = $input->getArgument('plugin');
        $type = $input->getOption('type');

        $io->title("Demo assistant: " . $plugin);

        try {
            $io->info("Uninstalling...");

            $this->dataService->remove($plugin, $type);

            $io->info("Installing...");

            $counter = $this->dataService->install($plugin, $type);

            $io->success($counter . " demos has been installed");
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        return self::SUCCESS;
    }
}
