<?php

namespace App\Tests\CoffeeMachine\Unit\Application\QueryHandler;

use App\CoffeeMachine\Application\DTO\OrderDTO;
use App\CoffeeMachine\Application\Query\GetOrdersForMachineQuery;
use App\CoffeeMachine\Application\QueryHandler\GetOrdersForMachineQueryHandler;
use App\CoffeeMachine\Domain\Entity\CoffeeOrder;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use PHPUnit\Framework\TestCase;

class GetOrdersForMachineQueryHandlerTest extends TestCase
{
    public function testReturnsOrderDtos(): void
    {
        $uuid = 'machine-uuid';

        $order = $this->createMock(CoffeeOrder::class);
        $order->method('getUuid')->willReturn('order-uuid');
        $order->method('getType')->willReturn(new CoffeeType('espresso'));
        $order->method('getIntensity')->willReturn(new CoffeeIntensity('low'));
        $order->method('getSugarLevel')->willReturn(new SugarLevel('0'));
        $order->method('getStatus')->willReturn($order->getStatus());
        $order->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $order->method('getUpdatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 10:05:00'));

        $repository = $this->createMock(CoffeeOrderRepositoryInterface::class);
        $repository->method('findByMachineUuid')->with($uuid)->willReturn([$order]);

        $handler = new GetOrdersForMachineQueryHandler($repository);
        $query = new GetOrdersForMachineQuery($uuid);

        $result = $handler($query);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(OrderDTO::class, $result[0]);
        $this->assertSame('order-uuid', $result[0]->uuid);
        $this->assertSame('espresso', $result[0]->type);
        $this->assertSame('low', $result[0]->intensity);
        $this->assertSame('0', $result[0]->sugarLevel);
        $this->assertSame('', $result[0]->status);
        $this->assertSame('2023-01-01 10:00:00', $result[0]->createdAt);
        $this->assertSame('2023-01-01 10:05:00', $result[0]->updatedAt);
    }

    public function testReturnsEmptyArrayWhenNoOrders(): void
    {
        $uuid = 'machine-uuid';

        $repository = $this->createMock(CoffeeOrderRepositoryInterface::class);
        $repository->method('findByMachineUuid')->with($uuid)->willReturn([]);

        $handler = new GetOrdersForMachineQueryHandler($repository);
        $query = new GetOrdersForMachineQuery($uuid);

        $result = $handler($query);

        $this->assertCount(0, $result);
    }
}
