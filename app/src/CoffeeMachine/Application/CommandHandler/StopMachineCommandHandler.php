<?php

namespace App\CoffeeMachine\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\StopMachineCommand;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class StopMachineCommandHandler
{
    private CoffeeMachineRepositoryInterface $repository;

    public function __construct(CoffeeMachineRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(StopMachineCommand $command): void
    {
        $machine = $this->repository->findByUuid($command->getMachineUuid());

        if (!$machine) {
            throw new \Exception('Machine not found');
        }

        $machine->stopMachine();
        $this->repository->save($machine);
    }
}
