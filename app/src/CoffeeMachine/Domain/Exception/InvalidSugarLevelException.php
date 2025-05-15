<?php

namespace App\CoffeeMachine\Domain\Exception;

class InvalidSugarLevelException extends \InvalidArgumentException
{
    /**
     * @param string[] $sugarLevels
     */
    public function __construct(string $value, array $sugarLevels)
    {
        $message = sprintf(
            'Invalid sugar level "%s". Allowed values are: %s', $value,
            implode(', ', $sugarLevels)
        );

        parent::__construct($message);
    }
}
