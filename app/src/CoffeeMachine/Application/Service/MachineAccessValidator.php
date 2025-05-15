<?php

namespace App\CoffeeMachine\Application\Service;

use App\CoffeeMachine\Application\Query\GetMachineByUuidQuery;
use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Exception\MachineNotAvailableException;
use App\CoffeeMachine\Domain\Exception\MachineNotFoundException;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MachineAccessValidator
{
    public function __construct(private readonly MessageBusInterface $queryBus)
    {
    }

    /**
     * @throws MachineNotFoundException
     * @throws MachineNotAvailableException
     */
    public function assertMachineIsReady(string $uuid): void
    {
        $envelope = $this->queryBus->dispatch(new GetMachineByUuidQuery($uuid));
        $stamp = $envelope->last(HandledStamp::class);

        if (!$stamp) {
            throw new MachineNotFoundException($uuid);
        }

        /** @var CoffeeMachine|null $machine */
        $machine = $stamp->getResult();

        if (!$machine) {
            throw new MachineNotFoundException($uuid);
        }

        /** @var MachineStatus $status */
        $status = $machine->getStatus();

        if ('on' !== (string) $status) {
            throw new MachineNotAvailableException($uuid);
        }
    }
}
