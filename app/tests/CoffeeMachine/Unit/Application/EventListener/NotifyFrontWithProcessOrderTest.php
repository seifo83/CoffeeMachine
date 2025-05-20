<?php

namespace App\Tests\CoffeeMachine\Unit\Application\EventListener;

use App\CoffeeMachine\Application\EventListener\NotifyFrontWithProcessOrder;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;

class NotifyFrontWithProcessOrderTest extends TestCase
{
    public function testItGeneratesPayloadFromEvent(): void
    {
        $event = new OrderStarted('order-123', 'espresso', 1);

        $hubMock = $this->createMock(HubInterface::class);
        $listener = new NotifyFrontWithProcessOrder($hubMock);

        $payload = $listener->buildPayload($event);

        $this->assertSame('order-123', $payload['orderUuid']);
        $this->assertSame('espresso', $payload['type']);
        $this->assertSame('preparing', $payload['status']);
        $this->assertSame(OrderStarted::class, $payload['eventType']);
        $this->assertArrayHasKey('timestamp', $payload);
    }
}
