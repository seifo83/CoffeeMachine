<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Event\Order\OrderCompleted;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class CoffeeOrderTest extends TestCase
{
    private function createOrder(): CoffeeOrder
    {
        return new CoffeeOrder(
            new CoffeeType('espresso'),
            new CoffeeIntensity('medium'),
            new SugarLevel('1_dose'),
            Uuid::uuid4()->toString()
        );
    }

    public function testCreateCoffeeOrderWithValidData(): void
    {
        $order = $this->createOrder();

        $this->assertNotEmpty($order->getUuid());
        $this->assertSame('espresso', (string) $order->getType());
        $this->assertSame('medium', (string) $order->getIntensity());
        $this->assertSame('1_dose', (string) $order->getSugarLevel());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getUpdatedAt());
        $this->assertSame(OrderStatus::PENDING, $order->getStatus()->getValue());
    }

    public function testToArrayReturnsExpectedStructure(): void
    {
        $order = $this->createOrder();
        $array = $order->toArray();

        $this->assertArrayHasKey('uuid', $array);
        $this->assertSame('espresso', $array['type']);
        $this->assertSame('medium', $array['intensity']);
        $this->assertSame('1_dose', $array['sugar_level']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function testStartFromPending(): void
    {
        $order = $this->createOrder();
        $order->start();

        $this->assertSame(OrderStatus::PREPARING, $order->getStatus()->getValue());

        $events = $order->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderStarted::class, $events[0]);
    }

    public function testStartThrowsIfNotPending(): void
    {
        $order = $this->createOrder();
        $order->start();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Order can only start preparation if it is pending.');
        $order->start();
    }

    public function testCompleteFromPreparing(): void
    {
        $order = $this->createOrder();
        $order->start();
        $order->releaseEvents();

        $order->complete();

        $this->assertSame(OrderStatus::COMPLETED, $order->getStatus()->getValue());

        $events = $order->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderCompleted::class, $events[0]);
    }

    public function testCompleteThrowsIfNotPreparing(): void
    {
        $order = $this->createOrder();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Order can only be completed if it is currently being prepared.');
        $order->complete();
    }

    public function testItEmitsOrderStartedEventWhenOrderIsStarted(): void
    {
        $order = new CoffeeOrder(
            new CoffeeType('espresso'),
            new CoffeeIntensity('low'),
            new SugarLevel('0'),
            'machine-123'
        );

        $order->start();

        $events = $order->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderStarted::class, $events[0]);
        $this->assertSame('espresso', $events[0]->getCoffeeType());
        $this->assertSame('preparing', $events[0]->getOrderStatus());
    }
}
