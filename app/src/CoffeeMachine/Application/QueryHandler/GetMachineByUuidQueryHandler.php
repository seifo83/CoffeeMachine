<?php

namespace App\CoffeeMachine\Application\QueryHandler;

use App\CoffeeMachine\Application\DTO\MachineDTO;
use App\CoffeeMachine\Application\Query\GetMachineByUuidQuery;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetMachineByUuidQueryHandler
{
    private CoffeeMachineRepositoryInterface $repository;

    public function __construct(CoffeeMachineRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetMachineByUuidQuery $query): ?MachineDTO
    {
        $machine = $this->repository->findByUuid($query->getUuid());

        if (!$machine) {
            return null;
        }

        return new MachineDTO(
            $machine->getUuid(),
            (string) $machine->getStatus(),
            count($machine->getOrders()),
            $machine->getCreatedAt()->format('Y-m-d H:i:s'),
            $machine->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
