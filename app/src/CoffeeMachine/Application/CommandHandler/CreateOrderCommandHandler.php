<?php

namespace App\CoffeeMachine\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\CreateOrderCommand;
use App\CoffeeMachine\Application\Message\StartOrderMessage;
use App\CoffeeMachine\Domain\Exception\OrderNotFoundException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class CreateOrderCommandHandler
{
    private CoffeeMachineRepositoryInterface $machineRepository;
    private CoffeeOrderRepositoryInterface $orderRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        CoffeeMachineRepositoryInterface $machineRepository,
        CoffeeOrderRepositoryInterface $orderRepository,
        MessageBusInterface $messageBus,
    ) {
        $this->machineRepository = $machineRepository;
        $this->orderRepository = $orderRepository;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws OrderNotFoundException
     * @throws \Exception
     */
    public function __invoke(CreateOrderCommand $command): string
    {
        $machine = $this->machineRepository->findByUuid($command->getMachineUuid());

        if (null === $machine) {
            throw new \Exception('Machine not found');
        }

        $type = new CoffeeType($command->getCoffeeType());
        $intensity = new CoffeeIntensity($command->getIntensity());
        $sugarLevel = new SugarLevel($command->getSugarLevel());

        $order = $machine->createOrder($type, $intensity, $sugarLevel);

        if (null === $order) {
            throw new OrderNotFoundException();
        }

        $this->machineRepository->save($machine);
        $this->orderRepository->save($order);

        $startOrderMessage = new StartOrderMessage(
            $order->getUuid(),
            $command->getMachineUuid()
        );

        $this->messageBus->dispatch($startOrderMessage);

        return $order->getUuid();
    }
}
