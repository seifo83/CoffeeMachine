<?php

namespace App\CoffeeMachine\Domain\Event\Machine;

class MachineStarted
{
    private string $machineUuid;
    private \DateTimeImmutable $occurredAt;

    public function __construct(string $machineUuid)
    {
        $this->machineUuid = $machineUuid;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getMachineUuid(): string
    {
        return $this->machineUuid;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
