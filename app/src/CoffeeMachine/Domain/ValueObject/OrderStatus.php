<?php

declare(strict_types=1);

namespace App\CoffeeMachine\Domain\ValueObject;

use App\CoffeeMachine\Domain\Exception\InvalidOrderStatusException;

class OrderStatus
{
    public const PENDING = 'pending';
    public const PREPARING = 'preparing';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    private const ORDER_STATUSES = [
        self::PENDING,
        self::PREPARING,
        self::COMPLETED,
        self::CANCELLED,
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::ORDER_STATUSES, true)) {
            throw new InvalidOrderStatusException($value, self::ORDER_STATUSES);
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
