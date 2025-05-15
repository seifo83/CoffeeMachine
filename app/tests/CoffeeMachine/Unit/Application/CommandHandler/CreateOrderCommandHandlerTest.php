<?php

namespace App\Tests\CoffeeMachine\Unit\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\CreateOrderCommand;
use App\CoffeeMachine\Application\CommandHandler\CreateOrderCommandHandler;
use App\CoffeeMachine\Application\Message\StartOrderMessage;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Exception\OrderNotFoundException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateOrderCommandHandlerTest extends TestCase
{
    public function testCreateOrderSuccessfully(): void
    {
        $machineUuid = 'machine-uuid';
        $orderUuid = 'order-uuid';

        $command = new CreateOrderCommand(
            $machineUuid,
            'espresso',
            'low',
            '0'
        );

        $order = $this->createMock(CoffeeOrder::class);
        $order->method('getUuid')->willReturn($orderUuid);

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->expects($this->once())
            ->method('createOrder')
            ->with(
                $this->isInstanceOf(CoffeeType::class),
                $this->isInstanceOf(CoffeeIntensity::class),
                $this->isInstanceOf(SugarLevel::class)
            )
            ->willReturn($order);

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->with($machineUuid)->willReturn($machine);
        $machineRepo->expects($this->once())->method('save')->with($machine);

        $orderRepo = $this->createMock(CoffeeOrderRepositoryInterface::class);
        $orderRepo->expects($this->once())->method('save')->with($order);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($orderUuid, $machineUuid) {
                return $message instanceof StartOrderMessage
                    && $message->getOrderUuid() === $orderUuid
                    && $message->getMachineUuid() === $machineUuid;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new CreateOrderCommandHandler($machineRepo, $orderRepo, $messageBus);

        $result = $handler($command);

        $this->assertEquals($orderUuid, $result);
    }

    /**
     * @throws \Exception
     */
    public function testThrowsWhenMachineNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Machine not found');

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->willReturn(null);

        $orderRepo = $this->createMock(CoffeeOrderRepositoryInterface::class);

        $messageBus = $this->createMock(MessageBusInterface::class);

        $command = new CreateOrderCommand('test', 'espresso', 'hard', '2_doses');

        $handler = new CreateOrderCommandHandler($machineRepo, $orderRepo, $messageBus);

        $handler($command);
    }

    /**
     * @throws \Exception
     */
    public function testThrowsWhenCreateOrderFails(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->method('createOrder')->willReturn(null);

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->willReturn($machine);

        $orderRepo = $this->createMock(CoffeeOrderRepositoryInterface::class);

        $messageBus = $this->createMock(MessageBusInterface::class);

        $command = new CreateOrderCommand('test', 'espresso', 'low', '0');

        $handler = new CreateOrderCommandHandler($machineRepo, $orderRepo, $messageBus);

        $handler($command);
    }
}
