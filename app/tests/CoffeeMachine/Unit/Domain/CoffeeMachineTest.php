<?php

namespace App\Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCancelled;
use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCreated;
use App\CoffeeMachine\Domain\Event\Machine\MachineStarted;
use App\CoffeeMachine\Domain\Event\Machine\MachineStopped;
use App\CoffeeMachine\Domain\Exception\OrderException;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use PHPUnit\Framework\TestCase;

class CoffeeMachineTest extends TestCase
{
    private function createMachine(?string $status = null): CoffeeMachine
    {
        return new CoffeeMachine(
            $status ? new MachineStatus($status) : null
        );
    }

    public function testMachineStartsCorrectly(): void
    {
        $machine = $this->createMachine('off');
        $message = $machine->startMachine();

        $this->assertSame('on', $machine->getStatus()->getValue());
        $this->assertSame('Welcome! The coffee machine is ready to use.', $message);

        $events = $machine->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(MachineStarted::class, $events[0]);
    }

    public function testStartWhenAlreadyOn(): void
    {
        $machine = $this->createMachine('on');
        $message = $machine->startMachine();

        $this->assertSame('on', $machine->getStatus()->getValue());
        $this->assertSame('The machine is already on.', $message);

        $this->assertEmpty($machine->releaseEvents());
    }

    public function testStartWhenInError(): void
    {
        $machine = $this->createMachine('error');
        $message = $machine->startMachine();

        $this->assertSame('error', $machine->getStatus()->getValue());
        $this->assertSame('Unable to start the machine. Please contact maintenance.', $message);

        $this->assertEmpty($machine->releaseEvents());
    }

    public function testStopMachine(): void
    {
        $machine = $this->createMachine('on');
        $message = $machine->stopMachine();

        $this->assertSame('off', $machine->getStatus()->getValue());
        $this->assertSame('Goodbye! The coffee machine has been turned off.', $message);

        $events = $machine->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(MachineStopped::class, $events[0]);
    }

    public function testStopWhenAlreadyOff(): void
    {
        $machine = $this->createMachine('off');
        $message = $machine->stopMachine();

        $this->assertSame('The machine is already off.', $message);
        $this->assertEmpty($machine->releaseEvents());
    }

    public function testCreateOrderWhenMachineOn(): void
    {
        $machine = $this->createMachine('on');

        $order = $machine->createOrder(
            new CoffeeType('espresso'),
            new CoffeeIntensity('hard'),
            new SugarLevel('2_doses'),
        );

        $this->assertNotNull($order);
        $this->assertCount(1, $machine->getOrders());

        $events = $machine->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(MachineOrderCreated::class, $events[0]);
    }

    public function testCreateOrderWhenMachineOff(): void
    {
        $machine = $this->createMachine('off');
        $machine->startMachine();

        $order = $machine->createOrder(
            new CoffeeType('espresso'),
            new CoffeeIntensity('hard'),
            new SugarLevel('2_doses')
        );

        $this->assertNotNull($order);
        $this->assertCount(1, $machine->getOrders());
    }

    /**
     * @throws OrderException
     */
    public function testCancelExistingOrder(): void
    {
        $machine = $this->createMachine('on');
        $order = $machine->createOrder(
            new CoffeeType('latte'),
            new CoffeeIntensity('low'),
            new SugarLevel('0')
        );
        $machine->releaseEvents();

        $this->assertNotNull($order);
        $result = $machine->cancelOrder($order);

        $this->assertTrue($result);

        $events = $machine->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(MachineOrderCancelled::class, $events[0]);
    }

    public function testCancelOrderFailsIfOrderDoesNotBelongToMachine(): void
    {
        $machine = $this->createMachine('on');

        $orderMock = $this->createMock(CoffeeOrder::class);
        $orderMock->method('getMachineUuid')->willReturn('different-machine-uuid');

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('This order does not belong to this machine.');

        $machine->cancelOrder($orderMock);
    }

    public function testCancelOrderFailsIfOrderAlreadyCancelled(): void
    {
        $machine = $this->createMachine('on');

        $orderMock = $this->createMock(CoffeeOrder::class);
        $orderMock->method('getMachineUuid')->willReturn($machine->getUuid());
        $orderMock->method('getStatus')->willReturn(new OrderStatus(OrderStatus::CANCELLED));

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('Order was already cancelled.');

        $machine->cancelOrder($orderMock);
    }
}
