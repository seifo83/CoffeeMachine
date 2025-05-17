<?php

namespace App\CoffeeMachine\Infrastructure\Controller;

use App\CoffeeMachine\Application\Query\GetMachineByUserQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class GetMachineController extends AbstractController
{
    #[Route('/api/machine', methods: ['GET'])]
    public function __invoke(UserInterface $user, MessageBusInterface $queryBus): JsonResponse
    {
        $query = new GetMachineByUserQuery($user);
        $envelope = $queryBus->dispatch($query);
        $stamp = $envelope->last(HandledStamp::class);

        /** @var ?string $machineUuid */
        $machineUuid = $stamp?->getResult();

        if (!$machineUuid) {
            return new JsonResponse(['error' => 'Machine not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['uuid' => $machineUuid]);
    }
}
