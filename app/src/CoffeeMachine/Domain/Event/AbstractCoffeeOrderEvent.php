<?php

namespace App\CoffeeMachine\Domain\Event;

abstract class AbstractCoffeeOrderEvent
{
    protected string $orderUuid;
    protected string $coffeeType;
    protected string $orderStatus;

    protected int $stepIndex = 999;

    public function __construct(string $orderUuid, string $coffeeType, string $orderStatus)
    {
        $this->orderUuid = $orderUuid;
        $this->coffeeType = $coffeeType;
        $this->orderStatus = $orderStatus;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getCoffeeType(): string
    {
        return $this->coffeeType;
    }

    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    public function getStepIndex(): int
    {
        return $this->stepIndex ?? 999;
    }

    public function setStepIndex(int $index): void
    {
        $this->stepIndex = $index;
    }

}
