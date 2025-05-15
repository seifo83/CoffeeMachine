<?php

namespace App\CoffeeMachine\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\CancelOrderCommand;
use App\CoffeeMachine\Application\Service\FindLastPendingOrderService;
use App\CoffeeMachine\Domain\Exception\OrderException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CancelOrderCommandHandler
{
    private CoffeeMachineRepositoryInterface $machineRepository;
    private FindLastPendingOrderService $service;

    public function __construct(
        CoffeeMachineRepositoryInterface $machineRepository,
        FindLastPendingOrderService $service,
    ) {
        $this->machineRepository = $machineRepository;
        $this->service = $service;
    }

    /**
     * @throws OrderException
     */
    public function __invoke(CancelOrderCommand $command): bool
    {
        $machine = $this->machineRepository->findByUuid($command->getMachineUuid());

        if (!$machine) {
            throw new OrderException('Machine not found.');
        }

        $order = $this->service->getLastOrder($command->getMachineUuid());

        if (!$order) {
            throw new OrderException('No pending order found.');
        }

        $success = $machine->cancelOrder($order);

        if ($success) {
            $this->machineRepository->save($machine);

            return true;
        }

        return false;
    }
}
