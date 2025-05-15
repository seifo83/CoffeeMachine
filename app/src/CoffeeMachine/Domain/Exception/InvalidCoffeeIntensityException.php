<?php

namespace App\CoffeeMachine\Domain\Exception;

class InvalidCoffeeIntensityException extends \InvalidArgumentException
{
    /**
     * @param string[] $coffeeIntensity
     */
    public function __construct(string $value, array $coffeeIntensity)
    {
        $message = sprintf(
            'Invalid coffee intensity "%s". Allowed values are: %s',
            $value,
            implode(', ', $coffeeIntensity)
        );

        parent::__construct($message);
    }
}
