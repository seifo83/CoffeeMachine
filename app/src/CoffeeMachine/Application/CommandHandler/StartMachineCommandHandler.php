<?php

namespace App\CoffeeMachine\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\StartMachineCommand;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StartMachineCommandHandler
{
    private CoffeeMachineRepositoryInterface $repository;

    public function __construct(CoffeeMachineRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(StartMachineCommand $command): void
    {
        $machine = $this->repository->findByUuid($command->getMachineUuid());

        if (!$machine) {
            throw new \Exception('Machine not found');
        }

        $machine->startMachine();
        $this->repository->save($machine);
    }
}
