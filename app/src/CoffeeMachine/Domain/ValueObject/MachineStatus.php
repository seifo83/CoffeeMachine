<?php

declare(strict_types=1);

namespace App\CoffeeMachine\Domain\ValueObject;

use App\CoffeeMachine\Domain\Exception\InvalidMachineStatusException;

class MachineStatus
{
    public const ON = 'on';
    public const OFF = 'off';
    public const ERROR = 'error';
    private const MACHINE_STATUS = [
        self::ON,
        self::OFF,
        self::ERROR,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::MACHINE_STATUS, true)) {
            throw new InvalidMachineStatusException($value, self::MACHINE_STATUS);
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
