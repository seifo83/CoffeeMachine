<?php

namespace App\CoffeeMachine\Infrastructure\Doctrine\Type;

use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class SugarLevelType extends StringType
{
    public const NAME = 'sugar_level';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof SugarLevel ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SugarLevel
    {
        if (!is_string($value)) {
            return null;
        }

        return new SugarLevel($value);
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
