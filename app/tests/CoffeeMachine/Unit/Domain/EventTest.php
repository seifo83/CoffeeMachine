<?php

namespace App\Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCancelled;
use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCreated;
use App\CoffeeMachine\Domain\Event\Order\OrderCompleted;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testOrderStartedEvent(): void
    {
        $event = new OrderStarted('uuid-123', 'espresso', 'PREPARING');

        $this->assertSame('uuid-123', $event->getOrderUuid());
        $this->assertSame('espresso', $event->getCoffeeType());
        $this->assertSame('PREPARING', $event->getOrderStatus());
    }

    public function testOrderCompletedEvent(): void
    {
        $event = new OrderCompleted('uuid-123', 'espresso', 'COMPLETED');

        $this->assertSame('uuid-123', $event->getOrderUuid());
        $this->assertSame('espresso', $event->getCoffeeType());
        $this->assertSame('COMPLETED', $event->getOrderStatus());
    }

    public function testMachineOrderCreatedEvent(): void
    {
        $event = new MachineOrderCreated('uuid-123', 'espresso', 'PENDING');

        $this->assertSame('uuid-123', $event->getOrderUuid());
        $this->assertSame('espresso', $event->getCoffeeType());
        $this->assertSame('PENDING', $event->getOrderStatus());
    }

    public function testMachineOrderCancelledEvent(): void
    {
        $event = new MachineOrderCancelled('uuid-123', 'espresso', 'CANCELLED');

        $this->assertSame('uuid-123', $event->getOrderUuid());
        $this->assertSame('espresso', $event->getCoffeeType());
        $this->assertSame('CANCELLED', $event->getOrderStatus());
    }
}
