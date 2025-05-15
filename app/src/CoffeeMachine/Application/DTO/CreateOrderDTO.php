<?php

namespace App\CoffeeMachine\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $intensity;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $sugar_level;
}
