<?php

namespace App\CoffeeMachine\Application\Query;

class GetMachineByUuidQuery
{
    private string $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
