<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Exception\InvalidCoffeeIntensityException;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use PHPUnit\Framework\TestCase;

class CoffeeIntensityTest extends TestCase
{
    /**
     * @dataProvider validIntensitiesProvider
     */
    public function testCreateValidIntensity(string $intensity): void
    {
        $coffeeIntensity = new CoffeeIntensity($intensity);

        $this->assertSame($intensity, $coffeeIntensity->getValue());
        $this->assertSame($intensity, $coffeeIntensity->__toString());
    }

    public function testInvalidIntensityThrowsException(): void
    {
        $this->expectException(InvalidCoffeeIntensityException::class);
        $this->expectExceptionMessageMatches('/Invalid coffee intensity "test". Allowed values are:/');

        new CoffeeIntensity('test');
    }

    /**
     * @return list<array{0: string}>
     */
    public function validIntensitiesProvider(): array
    {
        return [
            ['low'],
            ['medium'],
            ['hard'],
        ];
    }
}
