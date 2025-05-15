<?php

namespace App\CoffeeMachine\Domain\Repository;

use App\CoffeeMachine\Domain\Entity\CoffeeOrder;

interface CoffeeOrderRepositoryInterface
{
    public function findByUuid(string $uuid): ?CoffeeOrder;

    public function save(CoffeeOrder $order): void;

    /**
     * @return CoffeeOrder[]
     */
    public function findByMachineUuid(string $machineUuid): array;

    /**
     * @return CoffeeOrder[]
     */
    public function findByMachineUuidOrderedDesc(string $machineUuid): array;
}
