<?php

declare(strict_types=1);

namespace App\CoffeeMachine\Domain\ValueObject;

use App\CoffeeMachine\Domain\Exception\InvalidSugarLevelException;

class SugarLevel
{
    private const ZERO = '0';
    private const ONE = '1_dose';
    private const TWO = '2_doses';
    private const THREE = '3_doses';

    private const SUGAR_LEVELS = [
        self::ZERO,
        self::ONE,
        self::TWO,
        self::THREE,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::SUGAR_LEVELS, true)) {
            throw new InvalidSugarLevelException($value, self::SUGAR_LEVELS);
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
