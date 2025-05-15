<?php

namespace App\CoffeeMachine\Infrastructure\Doctrine\Type;

use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class CoffeeTypeType extends StringType
{
    public const NAME = 'coffee_type';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof CoffeeType ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CoffeeType
    {
        if (!is_string($value)) {
            return null;
        }

        return new CoffeeType($value);
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
