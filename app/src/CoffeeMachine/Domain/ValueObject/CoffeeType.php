<?php

declare(strict_types=1);

namespace App\CoffeeMachine\Domain\ValueObject;

use App\CoffeeMachine\Domain\Exception\InvalidCoffeeTypeException;

class CoffeeType
{
    private const ESPRESSO = 'espresso';
    private const CAPPUCCINO = 'cappuccino';
    private const LATTE = 'latte';
    private const AMERICANO = 'americano';
    private const MOCHA = 'mocha';

    private const COFFEE_TYPE = [
        self::ESPRESSO,
        self::CAPPUCCINO,
        self::LATTE,
        self::AMERICANO,
        self::MOCHA,
    ];
    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::COFFEE_TYPE, true)) {
            throw new InvalidCoffeeTypeException($value, self::COFFEE_TYPE);
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
