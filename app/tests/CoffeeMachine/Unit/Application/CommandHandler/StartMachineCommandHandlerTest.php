<?php

namespace App\Tests\CoffeeMachine\Unit\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\StartMachineCommand;
use App\CoffeeMachine\Application\CommandHandler\StartMachineCommandHandler;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use PHPUnit\Framework\TestCase;

class StartMachineCommandHandlerTest extends TestCase
{
    public function testStartMachineIsCalledAndSaved(): void
    {
        $uuid = 'machine-uuid';

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->expects($this->once())
            ->method('startMachine');

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->method('findByUuid')->with($uuid)->willReturn($machine);
        $repository->expects($this->once())
            ->method('save')
            ->with($machine);

        $handler = new StartMachineCommandHandler($repository);
        $command = new StartMachineCommand($uuid);

        $handler($command);
    }

    public function testThrowsExceptionWhenMachineNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Machine not found');

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->method('findByUuid')->willReturn(null);

        $handler = new StartMachineCommandHandler($repository);
        $command = new StartMachineCommand('invalid-uuid');

        $handler($command);
    }
}
