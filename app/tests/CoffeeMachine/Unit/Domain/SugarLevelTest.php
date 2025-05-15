<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Exception\InvalidSugarLevelException;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use PHPUnit\Framework\TestCase;

class SugarLevelTest extends TestCase
{
    /**
     * @dataProvider validSugarLevelsProvider
     */
    public function testCreateValidSugarLevel(string $level): void
    {
        $sugarLevel = new SugarLevel($level);

        $this->assertSame($level, $sugarLevel->getValue());
        $this->assertSame($level, $sugarLevel->__toString());
    }

    public function testInvalidSugarLevelThrowsException(): void
    {
        $this->expectException(InvalidSugarLevelException::class);
        $this->expectExceptionMessageMatches('/Invalid sugar level "test". Allowed values are:/');

        new SugarLevel('test');
    }

    /**
     * @return list<array{0: string}>
     */
    public function validSugarLevelsProvider(): array
    {
        return [
            ['0'],
            ['1_dose'],
            ['2_doses'],
            ['3_doses'],
        ];
    }
}
