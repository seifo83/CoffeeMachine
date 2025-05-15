<?php

namespace Tests\CoffeeMachine\Unit\Domain;

use App\CoffeeMachine\Domain\Exception\InvalidCoffeeTypeException;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use PHPUnit\Framework\TestCase;

class CoffeeTypeTest extends TestCase
{
    /**
     * @dataProvider validCoffeeTypesProvider
     */
    public function testCreateValidCoffeeType(string $type): void
    {
        $coffeeType = new CoffeeType($type);

        $this->assertSame($type, $coffeeType->getValue());
        $this->assertSame($type, $coffeeType->__toString());
    }

    public function testInvalidCoffeeTypeThrowsException(): void
    {
        $this->expectException(InvalidCoffeeTypeException::class);
        $this->expectExceptionMessageMatches('/Invalid coffee type "test". Allowed types are:/');

        new CoffeeType('test');
    }

    /**
     * @return list<array{0: string}>
     */
    public function validCoffeeTypesProvider(): array
    {
        return [
            ['espresso'],
            ['cappuccino'],
            ['latte'],
            ['americano'],
            ['mocha'],
        ];
    }
}
