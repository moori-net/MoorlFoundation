<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Command;

use MoorlFoundation\Core\Service\EntityAutoCacheService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('moorl:entity-auto-cache')]
class EntityAutoCacheCommand extends Command
{
    public function __construct(private readonly EntityAutoCacheService $entityAutoCacheService,)
    {
        parent::__construct('moorl-foundation:entity-auto-cache');
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->entityAutoCacheService->scanForTimeControlledEntities(null, new ShopwareStyle($input, $output));

        return 1;
    }
}
