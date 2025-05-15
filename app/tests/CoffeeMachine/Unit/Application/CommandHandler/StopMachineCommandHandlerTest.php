<?php

namespace App\Tests\CoffeeMachine\Unit\Application\CommandHandler;

use App\CoffeeMachine\Application\Command\StopMachineCommand;
use App\CoffeeMachine\Application\CommandHandler\StopMachineCommandHandler;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use PHPUnit\Framework\TestCase;

class StopMachineCommandHandlerTest extends TestCase
{
    public function testInvokeStopsMachineAndSavesIt(): void
    {
        $uuid = 'machine-uuid';

        $command = new StopMachineCommand($uuid);

        $machine = $this->createMock(CoffeeMachine::class);
        $machine->expects($this->once())
            ->method('stopMachine');

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByUuid')
            ->with($uuid)
            ->willReturn($machine);

        $repository->expects($this->once())
            ->method('save')
            ->with($machine);

        $handler = new StopMachineCommandHandler($repository);
        $handler($command);
    }

    public function testThrowsExceptionWhenMachineNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Machine not found');

        $command = new StopMachineCommand('test');

        $repository = $this->createMock(CoffeeMachineRepositoryInterface::class);
        $repository->method('findByUuid')->willReturn(null);

        $handler = new StopMachineCommandHandler($repository);

        $handler($command);
    }
}
