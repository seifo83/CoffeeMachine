<?php

namespace App\CoffeeMachine\Infrastructure\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private string $username;
    private string $password;

    /** @var string[] */
    private array $roles;
    private string $machineId;

    /**
     * @param string[] $roles
     */
    public function __construct(string $username, string $password, array $roles, string $machineId)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->machineId = $machineId;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getMachineId(): string
    {
        return $this->machineId;
    }

    public function eraseCredentials(): void
    {
    }
}
