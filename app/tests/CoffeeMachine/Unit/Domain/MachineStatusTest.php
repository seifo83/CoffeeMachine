<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Exception\InvalidMachineStatusException;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use PHPUnit\Framework\TestCase;

class MachineStatusTest extends TestCase
{
    /**
     * @dataProvider validStatusesProvider
     */
    public function testCreateValidMachineStatus(string $status): void
    {
        $machineStatus = new MachineStatus($status);

        $this->assertSame($status, $machineStatus->getValue());
        $this->assertSame($status, $machineStatus->__toString());
    }

    public function testInvalidMachineStatusThrowsException(): void
    {
        $this->expectException(InvalidMachineStatusException::class);
        $this->expectExceptionMessageMatches('/Invalid machine status "test". Allowed statuses are:/');

        new MachineStatus('test');
    }

    /**
     * @return list<array{0: string}>
     */
    public function validStatusesProvider(): array
    {
        return [
            ['on'],
            ['off'],
            ['error'],
        ];
    }
}
