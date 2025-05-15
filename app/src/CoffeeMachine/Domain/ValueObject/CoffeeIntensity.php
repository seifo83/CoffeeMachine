<?php

declare(strict_types=1);

namespace App\CoffeeMachine\Domain\ValueObject;

use App\CoffeeMachine\Domain\Exception\InvalidCoffeeIntensityException;

class CoffeeIntensity
{
    private const LOW = 'low';
    private const MEDIUM = 'medium';
    private const HARD = 'hard';

    private const COFFEE_INTENSITIES = [
        self::LOW,
        self::MEDIUM,
        self::HARD,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::COFFEE_INTENSITIES, true)) {
            throw new InvalidCoffeeIntensityException($value, self::COFFEE_INTENSITIES);
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
