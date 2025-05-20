<?php

namespace App\CoffeeMachine\Domain\Event\Order;

use App\CoffeeMachine\Domain\Event\AbstractCoffeeOrderEvent;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;

class OrderCompleted extends AbstractCoffeeOrderEvent
{
    public function __construct(string $orderUuid, string $coffeeType, int $stepIndex = 999)
    {
        parent::__construct($orderUuid, $coffeeType, OrderStatus::COMPLETED);

        $this->setStepIndex($stepIndex);
    }
}
