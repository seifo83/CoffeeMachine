<?php

namespace App\CoffeeMachine\Application\Service;

use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;

class FindLastPendingOrderService
{
    private CoffeeOrderRepositoryInterface $orderRepository;

    public function __construct(CoffeeOrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getLastOrder(string $machineUuid): ?CoffeeOrder
    {
        $orders = $this->orderRepository->findByMachineUuidOrderedDesc($machineUuid);

        return $orders[0] ?? null;
    }
}
