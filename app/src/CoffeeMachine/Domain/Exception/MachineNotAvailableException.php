<?php

namespace App\CoffeeMachine\Domain\Exception;

class MachineNotAvailableException extends \DomainException
{
    public function __construct(string $uuid)
    {
        parent::__construct(sprintf('Machine %s is not available (must be ON)', $uuid));
    }
}
