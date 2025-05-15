<?php

namespace App\CoffeeMachine\Domain\Exception;

class OrderException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
