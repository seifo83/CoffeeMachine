<?php

namespace App\CoffeeMachine\Infrastructure\Controller;

use App\CoffeeMachine\Application\Command\StartMachineCommand;
use App\CoffeeMachine\Application\Command\StopMachineCommand;
use App\CoffeeMachine\Application\DTO\MachineDTO;
use App\CoffeeMachine\Application\Query\GetMachineByUuidQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/machines')]
class CoffeeMachineController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
    ) {
    }

    #[Route('/{uuid}', methods: ['GET'])]
    public function getMachine(string $uuid): JsonResponse
    {
        $query = new GetMachineByUuidQuery($uuid);

        $envelope = $this->queryBus->dispatch($query);
        $stamp = $envelope->last(HandledStamp::class);

        /** @var MachineDTO|null $machine */
        $machine = $stamp?->getResult();

        if (!$machine) {
            return new JsonResponse(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($machine->toArray());
    }

    #[Route('/{uuid}/start', methods: ['POST'])]
    public function startMachine(string $uuid): JsonResponse
    {
        $command = new StartMachineCommand($uuid);

        try {
            $this->commandBus->dispatch($command);

            return new JsonResponse(['message' => 'Machine started successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{uuid}/stop', methods: ['POST'])]
    public function stopMachine(string $uuid): JsonResponse
    {
        $command = new StopMachineCommand($uuid);

        try {
            $this->commandBus->dispatch($command);

            return new JsonResponse(['message' => 'Machine stopped successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
