<?php

namespace App\CoffeeMachine\Domain\Repository;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;

interface CoffeeMachineRepositoryInterface
{
    public function findByUuid(string $uuid): ?CoffeeMachine;

    public function save(CoffeeMachine $machine): void;

    /**
     * @return CoffeeMachine[]
     */
    public function findAll(): array;
}
