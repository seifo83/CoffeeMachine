<?php

namespace App\CoffeeMachine\Domain\Exception;

class InvalidOrderStatusException extends \InvalidArgumentException
{
    /**
     * @param string[] $orderStatus
     */
    public function __construct(string $value, array $orderStatus)
    {
        $message = sprintf(
            'Invalid order status "%s". Allowed statues are: %s', $value,
            implode(', ', $orderStatus)
        );

        parent::__construct($message);
    }
}
