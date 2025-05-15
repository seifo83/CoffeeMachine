<?php

namespace App\CoffeeMachine\Domain\Exception;

class OrderNotFoundException extends \DomainException
{
    public function __construct(?string $orderUuid = null)
    {
        if ($orderUuid) {
            $message = sprintf('Order with UUID %s not found.', $orderUuid);
        } else {
            $message = 'Order could not be created or does not exist.';
        }

        parent::__construct($message);
    }
}
