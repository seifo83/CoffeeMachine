<?php

namespace App\CoffeeMachine\Infrastructure\Command;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-data',
    description: 'Creates test data for the coffee machine application',
)]
class CreateTestDataCommand extends Command
{
    private CoffeeMachineRepositoryInterface $machineRepository;

    public function __construct(CoffeeMachineRepositoryInterface $machineRepository)
    {
        parent::__construct();
        $this->machineRepository = $machineRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $machine = new CoffeeMachine(new MachineStatus('on'));
        $this->machineRepository->save($machine);

        $io->success(sprintf('Coffee machine created with UUID: %s', $machine->getUuid()));

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->addOption('target-env', null, InputOption::VALUE_REQUIRED, 'Target environment to create test data for', 'dev')
            ->addOption('init-db', null, InputOption::VALUE_NONE, 'Initialize database schema')
            ->addOption('fixtures', null, InputOption::VALUE_NONE, 'Load test fixtures');
    }

}
