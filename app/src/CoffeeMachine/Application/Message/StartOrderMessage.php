<?php

namespace App\CoffeeMachine\Application\Message;

class StartOrderMessage
{
    private string $orderUuid;
    private string $machineUuid;

    public function __construct(string $orderUuid, string $machineUuid)
    {
        $this->orderUuid = $orderUuid;
        $this->machineUuid = $machineUuid;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getMachineUuid(): string
    {
        return $this->machineUuid;
    }
}
