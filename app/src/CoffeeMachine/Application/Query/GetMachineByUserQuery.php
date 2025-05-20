<?php

namespace App\CoffeeMachine\Application\Query;

use Symfony\Component\Security\Core\User\UserInterface;

class GetMachineByUserQuery
{
    public function __construct(
        private readonly UserInterface $user,
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
