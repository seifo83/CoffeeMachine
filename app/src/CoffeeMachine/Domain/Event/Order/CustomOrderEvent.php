<?php

namespace App\CoffeeMachine\Domain\Event\Order;

use App\CoffeeMachine\Domain\Event\AbstractCoffeeOrderEvent;

class CustomOrderEvent extends AbstractCoffeeOrderEvent
{
    private ?string $description;

    public function __construct(
        string $orderUuid,
        string $coffeeType,
        string $orderStatus,
        ?string $description = null
    ) {
        parent::__construct($orderUuid, $coffeeType, $orderStatus);
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}