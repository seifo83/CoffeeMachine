<?php

namespace App\Tests\CoffeeMachine\Unit\Application\EventListener;

use App\CoffeeMachine\Application\EventListener\NotifyFrontWithProcessOrder;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use PHPUnit\Framework\TestCase;

class NotifyFrontWithProcessOrderTest extends TestCase
{
    public function testItGeneratesPayloadFromEvent(): void
    {
        $event = new OrderStarted('order-123', 'espresso', 'PREPARING');

        $listener = new NotifyFrontWithProcessOrder();
        $payload = $listener->buildPayload($event);

        $this->assertSame('order-123', $payload['orderUuid']);
        $this->assertSame('espresso', $payload['coffeeType']);
        $this->assertSame('PREPARING', $payload['status']);
        $this->assertSame(OrderStarted::class, $payload['eventType']);
        $this->assertArrayHasKey('timestamp', $payload);
    }
}
