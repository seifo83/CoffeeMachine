<?php

namespace App\CoffeeMachine\Domain\Exception;

class InvalidCoffeeTypeException extends \InvalidArgumentException
{
    /**
     * @param string[] $coffeeTypes
     */
    public function __construct(string $value, array $coffeeTypes)
    {
        $message = sprintf(
            'Invalid coffee type "%s". Allowed types are: %s',
            $value,
            implode(', ', $coffeeTypes)
        );

        parent::__construct($message);
    }
}
