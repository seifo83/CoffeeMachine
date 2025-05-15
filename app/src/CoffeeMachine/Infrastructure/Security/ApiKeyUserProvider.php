<?php

namespace App\CoffeeMachine\Infrastructure\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
class ApiKeyUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    /**
     * @var array<string, array{password: string, roles: string[]}>
     */
    private array $users = [
        'admin' => [
            'password' => '$2y$10$/qXETvLyTkB3gwCmeJB4puhsrYIMmf2KZHOwJ2k1qw6RxeLFfnIdi',
            'roles' => ['ROLE_ADMIN'],
        ],
        'user' => [
            'password' => '$2y$13$A40JfQVTAQMIg3Qr8Jp9o.Hu0UgZz2jPBXAKWTpxF1IcCEwFDGUmO',
            'roles' => ['ROLE_USER'],
        ],
    ];

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!isset($this->users[$identifier])) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        $userData = $this->users[$identifier];

        return new User(
            $identifier,
            $userData['password'],
            $userData['roles']
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
    }
}
