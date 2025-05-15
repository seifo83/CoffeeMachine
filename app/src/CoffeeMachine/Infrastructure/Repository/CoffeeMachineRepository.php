<?php

namespace App\CoffeeMachine\Infrastructure\Repository;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoffeeMachineRepository implements CoffeeMachineRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findByUuid(string $uuid): ?CoffeeMachine
    {
        return $this->em->getRepository(CoffeeMachine::class)->findOneBy(['uuid' => $uuid]);
    }

    public function save(CoffeeMachine $machine): void
    {
        $this->em->persist($machine);
        $this->em->flush();
    }

    public function findAll(): array
    {
        return $this->em->getRepository(CoffeeMachine::class)->findAll();
    }
}
