<?php

namespace App\CoffeeMachine\Infrastructure\Controller;

use App\CoffeeMachine\Application\Command\CancelOrderCommand;
use App\CoffeeMachine\Application\Command\CreateOrderCommand;
use App\CoffeeMachine\Application\Command\StartMachineCommand;
use App\CoffeeMachine\Application\Command\StopMachineCommand;
use App\CoffeeMachine\Application\DTO\CreateOrderDTO;
use App\CoffeeMachine\Application\DTO\MachineDTO;
use App\CoffeeMachine\Application\DTO\OrderDTO;
use App\CoffeeMachine\Application\Query\GetMachineByUuidQuery;
use App\CoffeeMachine\Application\Query\GetOrdersForMachineQuery;
use App\CoffeeMachine\Application\Service\MachineAccessValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/machines')]
class CoffeeMachineController extends AbstractController
{
    private MessageBusInterface $commandBus;
    private MessageBusInterface $queryBus;
    private MachineAccessValidator $machineValidator;

    public function __construct(MessageBusInterface $commandBus, MessageBusInterface $queryBus, MachineAccessValidator $machineValidator)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->machineValidator = $machineValidator;
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

    #[Route('/{uuid}/orders', methods: ['GET'])]
    public function getOrders(string $uuid): JsonResponse
    {
        $query = new GetOrdersForMachineQuery($uuid);

        $envelope = $this->queryBus->dispatch($query);
        $stamp = $envelope->last(HandledStamp::class);
        $orders = $stamp?->getResult();

        if (!is_array($orders)) {
            return new JsonResponse(['error' => 'Invalid response'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(array_map(
            fn ($order) => $order instanceof OrderDTO ? $order->toArray() : [],
            $orders
        ));
    }

    #[Route('/{uuid}/orders', methods: ['POST'])]
    public function createOrder(CreateOrderDTO $createOrderDTO, string $uuid): JsonResponse
    {
        $this->machineValidator->assertMachineIsReady($uuid);

        $command = new CreateOrderCommand(
            $uuid,
            $createOrderDTO->type,
            $createOrderDTO->intensity,
            $createOrderDTO->sugar_level
        );

        try {
            $envelope = $this->commandBus->dispatch($command);
            $stamp = $envelope->last(HandledStamp::class);
            $orderUuid = $stamp?->getResult();

            if (!$orderUuid) {
                return new JsonResponse(['error' => 'Order could not be created'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse(['uuid' => $orderUuid], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{uuid}/orders/last', methods: ['DELETE'])]
    public function cancelLastOrder(string $uuid): JsonResponse
    {
        try {
            $this->machineValidator->assertMachineIsReady($uuid);

            $command = new CancelOrderCommand($uuid);
            $this->commandBus->dispatch($command);

            return new JsonResponse(['code' => 200, 'success' => true, 'message' => 'Order cancelled successfully']);
        } catch (HandlerFailedException $e) {
            $nested = $e->getPrevious();
            $errorMessage = $nested ? $nested->getMessage() : 'An error occurred during order cancellation';

            return new JsonResponse([
                'message' => $errorMessage,
                'code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
