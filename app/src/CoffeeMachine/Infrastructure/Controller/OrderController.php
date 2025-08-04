<?php

namespace App\CoffeeMachine\Infrastructure\Controller;

use App\CoffeeMachine\Application\Command\CancelOrderCommand;
use App\CoffeeMachine\Application\Command\CreateOrderCommand;
use App\CoffeeMachine\Application\DTO\CreateOrderDTO;
use App\CoffeeMachine\Application\DTO\OrderDTO;
use App\CoffeeMachine\Application\Query\GetOrdersForMachineQuery;
use App\CoffeeMachine\Application\Service\MachineAccessValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/machines/{machineUuid}/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private MessageBusInterface $queryBus,
        private MachineAccessValidator $machineValidator,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getOrders(string $machineUuid): JsonResponse
    {
        $query = new GetOrdersForMachineQuery($machineUuid);

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

    #[Route('', methods: ['POST'])]
    public function createOrder(CreateOrderDTO $createOrderDTO, string $machineUuid): JsonResponse
    {
        $this->machineValidator->assertMachineIsReady($machineUuid);

        $command = new CreateOrderCommand(
            $machineUuid,
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

    #[Route('/last', methods: ['DELETE'])]
    public function cancelLastOrder(string $machineUuid): JsonResponse
    {
        try {
            $this->machineValidator->assertMachineIsReady($machineUuid);

            $command = new CancelOrderCommand($machineUuid);
            $this->commandBus->dispatch($command);

            return new JsonResponse([
                'code' => 200,
                'success' => true,
                'message' => 'Order cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'code' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
