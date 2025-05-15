<?php

namespace App\CoffeeMachine\Application\MessageHandler;

use App\CoffeeMachine\Application\Message\StartOrderMessage;
use App\CoffeeMachine\Domain\Exception\MachineNotFoundException;
use App\CoffeeMachine\Domain\Exception\OrderNotFoundException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class StartOrderMessageHandler
{
    private CoffeeOrderRepositoryInterface $orderRepository;
    private CoffeeMachineRepositoryInterface $machineRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        CoffeeOrderRepositoryInterface $orderRepository,
        CoffeeMachineRepositoryInterface $machineRepository,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->orderRepository = $orderRepository;
        $this->machineRepository = $machineRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(StartOrderMessage $message): void
    {
        $order = $this->orderRepository->findByUuid($message->getOrderUuid());
        $machine = $this->machineRepository->findByUuid($message->getMachineUuid());

        if (!$order) {
            throw new OrderNotFoundException($message->getOrderUuid());
        }

        if (!$machine) {
            throw new MachineNotFoundException($message->getMachineUuid());
        }

        // Order Coffee Started
        sleep(120);
        $order->start();
        $this->orderRepository->save($order);

        foreach ($order->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        // Order Coffee Completed
        sleep(360);
        $order->complete();
        $this->orderRepository->save($order);

        foreach ($order->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
