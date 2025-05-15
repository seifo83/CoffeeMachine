<?php

namespace App\CoffeeMachine\Infrastructure\Doctrine\Type;

use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class CoffeeIntensityType extends StringType
{
    public const NAME = 'coffee_intensity';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof CoffeeIntensity ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CoffeeIntensity
    {
        if (!is_string($value)) {
            return null;
        }

        return new CoffeeIntensity($value);
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
