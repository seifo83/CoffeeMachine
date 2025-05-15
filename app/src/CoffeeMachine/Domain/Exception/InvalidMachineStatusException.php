<?php

namespace App\CoffeeMachine\Domain\Exception;

class InvalidMachineStatusException extends \InvalidArgumentException
{
    /**
     * @param string[] $machineStatus
     */
    public function __construct(string $status, array $machineStatus)
    {
        $message = sprintf(
            'Invalid machine status "%s". Allowed statuses are: %s',
            $status,
            implode(', ', $machineStatus)
        );
        parent::__construct($message);
    }
}
