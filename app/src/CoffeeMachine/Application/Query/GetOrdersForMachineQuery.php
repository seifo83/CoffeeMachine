<?php

namespace App\CoffeeMachine\Application\Query;

class GetOrdersForMachineQuery
{
    private string $machineUuid;

    public function __construct(string $machineUuid)
    {
        $this->machineUuid = $machineUuid;
    }

    public function getMachineUuid(): string
    {
        return $this->machineUuid;
    }
}
