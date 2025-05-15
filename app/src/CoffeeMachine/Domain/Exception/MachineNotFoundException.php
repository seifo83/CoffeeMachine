<?php

namespace App\CoffeeMachine\Domain\Exception;

class MachineNotFoundException extends \DomainException
{
    public function __construct(string $machineUuid)
    {
        parent::__construct(sprintf('Machine with UUID %s not found', $machineUuid));
    }
}
