<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Exception\InvalidOrderStatusException;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    /**
     * @dataProvider validStatusesProvider
     */
    public function testCreateValidOrderStatus(string $status): void
    {
        $orderStatus = new OrderStatus($status);

        $this->assertSame($status, $orderStatus->getValue());
        $this->assertSame($status, (string) $orderStatus);
    }

    public function testInvalidOrderStatusThrowsException(): void
    {
        $this->expectException(InvalidOrderStatusException::class);

        new OrderStatus('test');
    }

    /**
     * @return list<array{0: string}>
     */
    public function validStatusesProvider(): array
    {
        return [
            ['pending'],
            ['preparing'],
            ['completed'],
            ['cancelled'],
        ];
    }
}
