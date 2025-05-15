<?php

namespace App\CoffeeMachine\Application\QueryHandler;

use App\CoffeeMachine\Application\DTO\OrderDTO;
use App\CoffeeMachine\Application\Query\GetOrdersForMachineQuery;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetOrdersForMachineQueryHandler
{
    private CoffeeOrderRepositoryInterface $repository;

    public function __construct(CoffeeOrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return OrderDTO[]
     */
    public function __invoke(GetOrdersForMachineQuery $query): array
    {
        $orders = $this->repository->findByMachineUuid($query->getMachineUuid());

        $orderDtos = [];
        foreach ($orders as $order) {
            $orderDtos[] = new OrderDTO(
                $order->getUuid(),
                (string) $order->getType(),
                (string) $order->getIntensity(),
                (string) $order->getSugarLevel(),
                (string) $order->getStatus(),
                $order->getCreatedAt()->format('Y-m-d H:i:s'),
                $order->getUpdatedAt()->format('Y-m-d H:i:s')
            );
        }

        return $orderDtos;
    }
}
