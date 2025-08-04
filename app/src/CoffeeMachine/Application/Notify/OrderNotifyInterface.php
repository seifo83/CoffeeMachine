<?php

namespace App\CoffeeMachine\Application\Notify;

interface OrderNotifyInterface
{
    public function notify(string $orderUuid, string $coffeeType, string $status, ?string $description = null, int $stepIndex = 0): void;
}
