<?php

namespace App\Tests\CoffeeMachine\Unit\Application\QueryHandler;

use App\CoffeeMachine\Application\DTO\MachineDTO;
use App\CoffeeMachine\Application\Query\GetMachineByUuidQuery;
use App\CoffeeMachine\Application\QueryHandler\GetMachineByUuidQueryHandler;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GetMachineByUuidQueryHandlerTest extends TestCase
{
    public function testReturnsMachineDtoWhenMachineExists(): void
    {
        $uuid = 'machine-uuid';

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->method('getUuid')->willReturn($uuid);
        $machine->method('getStatus')->willReturn($machine->getStatus());
        $machine->method('getOrders')->willReturn([1, 2, 3]);
        $machine->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $machine->method('getUpdatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->method('findByUuid')->with($uuid)->willReturn($machine);

        $handler = new GetMachineByUuidQueryHandler($repository);

        $query = new GetMachineByUuidQuery($uuid);
        $dto = $handler($query);

        $this->assertInstanceOf(MachineDTO::class, $dto);
        $this->assertSame($uuid, $dto->uuid);
        $this->assertSame('', $dto->status);
        $this->assertSame(3, $dto->ordersCount);
        $this->assertSame('2023-01-01 10:00:00', $dto->createdAt);
        $this->assertSame('2023-01-01 12:00:00', $dto->updatedAt);
    }

    public function testReturnsNullWhenMachineNotFound(): void
    {
        $uuid = 'invalid-uuid';

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->method('findByUuid')->with($uuid)->willReturn(null);

        $handler = new GetMachineByUuidQueryHandler($repository);

        $query = new GetMachineByUuidQuery($uuid);
        $result = $handler($query);

        $this->assertNull($result);
    }
}
