<?php

namespace App\Tests\CoffeeMachine\Unit\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\CancelOrderCommand;
use App\CoffeeMachine\Application\CommandHandler\CancelOrderCommandHandler;
use App\CoffeeMachine\Application\Service\FindLastPendingOrderService;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Exception\OrderException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CancelOrderCommandHandlerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testCancelOrderSuccessfully(): void
    {
        $machineUuid = 'machine-uuid';

        $order = $this->createMock(CoffeeOrder::class);

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->expects($this->once())
            ->method('cancelOrder')
            ->with($order)
            ->willReturn(true);

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->with($machineUuid)->willReturn($machine);
        $machineRepo->expects($this->once())->method('save')->with($machine);

        $service = $this->createMock(FindLastPendingOrderService::class);
        $service->method('getLastOrder')->with($machineUuid)->willReturn($order);

        $handler = new CancelOrderCommandHandler($machineRepo, $service);

        $command = new CancelOrderCommand($machineUuid);
        $result = $handler->__invoke($command);

        $this->assertTrue($result);
    }

    public function testThrowsWhenMachineNotFound(): void
    {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('Machine not found.');

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->willReturn(null);

        $service = $this->createMock(FindLastPendingOrderService::class);

        $handler = new CancelOrderCommandHandler($machineRepo, $service);

        $command = new CancelOrderCommand('invalid-uuid');

        $handler->__invoke($command);
    }

    /**
     * @throws \Exception
     */
    public function testReturnsFalseWhenCancelFails(): void
    {
        $machineUuid = 'machine-uuid';

        $order = $this->createMock(CoffeeOrder::class);

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->method('cancelOrder')->with($order)->willReturn(false);

        $machineRepo = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $machineRepo->method('findByUuid')->willReturn($machine);

        $machineRepo->expects($this->never())->method('save');

        $service = $this->createMock(FindLastPendingOrderService::class);
        $service->method('getLastOrder')->with($machineUuid)->willReturn($order);

        $handler = new CancelOrderCommandHandler($machineRepo, $service);

        $command = new CancelOrderCommand($machineUuid);
        $result = $handler->__invoke($command);

        $this->assertFalse($result);
    }
}
