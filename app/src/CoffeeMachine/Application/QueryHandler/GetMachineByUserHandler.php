<?php

namespace App\CoffeeMachine\Application\QueryHandler;

use App\CoffeeMachine\Application\Query\GetMachineByUserQuery;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Infrastructure\Security\User;

class GetMachineByUserHandler
{
    public function __construct(
        private readonly CoffeeMachineRepositoryInterface $machineRepository,
    ) {
    }

    public function __invoke(GetMachineByUserQuery $query): ?string
    {
        /** @var User $user */
        $user = $query->getUser();
        $machineId = $user->getMachineId();

        $machine = $this->machineRepository->findByUuid($machineId);

        return $machine?->getUuid();
    }
}
