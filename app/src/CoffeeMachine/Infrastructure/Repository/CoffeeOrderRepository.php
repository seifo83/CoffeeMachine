<?php

namespace App\CoffeeMachine\Infrastructure\Repository;

use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class CoffeeOrderRepository implements CoffeeOrderRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findByUuid(string $uuid): ?CoffeeOrder
    {
        return $this->em->getRepository(CoffeeOrder::class)->findOneBy(['uuid' => $uuid]);
    }

    public function save(CoffeeOrder $order): void
    {
        $this->em->persist($order);
        $this->em->flush();
    }

    public function findByMachineUuid(string $machineUuid): array
    {
        return $this->em->getRepository(CoffeeOrder::class)->findBy(['machineUuid' => $machineUuid]);
    }

    public function findByMachineUuidOrderedDesc(string $machineUuid): array
    {
        return $this->em->getRepository(CoffeeOrder::class)->findBy(
            ['machineUuid' => $machineUuid],
            ['createdAt' => 'DESC']
        );
    }
}
