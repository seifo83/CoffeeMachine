<?php

namespace App\CoffeeMachine\Domain\Event\Order;

use App\CoffeeMachine\Domain\Event\AbstractCoffeeOrderEvent;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;

class OrderStarted extends AbstractCoffeeOrderEvent
{
    public function __construct(string $orderUuid, string $coffeeType, int $stepIndex)
    {
        parent::__construct($orderUuid, $coffeeType, OrderStatus::PREPARING);

        $this->setStepIndex($stepIndex);
    }
}
