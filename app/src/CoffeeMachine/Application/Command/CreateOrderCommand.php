<?php

namespace App\CoffeeMachine\Application\Command;

class CreateOrderCommand
{
    private string $machineUuid;
    private string $coffeeType;
    private string $intensity;
    private string $sugarLevel;

    public function __construct(
        string $machineUuid,
        string $coffeeType,
        string $intensity,
        string $sugarLevel,
    ) {
        $this->machineUuid = $machineUuid;
        $this->coffeeType = $coffeeType;
        $this->intensity = $intensity;
        $this->sugarLevel = $sugarLevel;
    }

    public function getMachineUuid(): string
    {
        return $this->machineUuid;
    }

    public function getCoffeeType(): string
    {
        return $this->coffeeType;
    }

    public function getIntensity(): string
    {
        return $this->intensity;
    }

    public function getSugarLevel(): string
    {
        return $this->sugarLevel;
    }
}
