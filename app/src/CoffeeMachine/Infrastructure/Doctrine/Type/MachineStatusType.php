<?php

namespace App\CoffeeMachine\Infrastructure\Doctrine\Type;

use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class MachineStatusType extends StringType
{
    public const NAME = 'machine_status';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof MachineStatus ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?MachineStatus
    {
        if (!is_string($value)) {
            return null;
        }

        return new MachineStatus($value);
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
