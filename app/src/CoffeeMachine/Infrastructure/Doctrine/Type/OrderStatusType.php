<?php

namespace App\CoffeeMachine\Infrastructure\Doctrine\Type;

use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class OrderStatusType extends StringType
{
    public const NAME = 'order_status';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof OrderStatus ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OrderStatus
    {
        if (!is_string($value)) {
            return null;
        }

        return new OrderStatus($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
