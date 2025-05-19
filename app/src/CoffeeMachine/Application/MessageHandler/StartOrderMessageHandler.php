<?php

namespace App\CoffeeMachine\Application\MessageHandler;

use App\CoffeeMachine\Application\Message\StartOrderMessage;
use App\CoffeeMachine\Domain\Event\Order\OrderCompleted;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use App\CoffeeMachine\Domain\Exception\MachineNotFoundException;
use App\CoffeeMachine\Domain\Exception\OrderNotFoundException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class StartOrderMessageHandler
{
    private CoffeeOrderRepositoryInterface $orderRepository;
    private CoffeeMachineRepositoryInterface $machineRepository;
    private EventDispatcherInterface $eventDispatcher;
    private HubInterface $hub;

    public function __construct(
        CoffeeOrderRepositoryInterface $orderRepository,
        CoffeeMachineRepositoryInterface $machineRepository,
        EventDispatcherInterface $eventDispatcher,
        HubInterface $hub
    ) {
        $this->orderRepository = $orderRepository;
        $this->machineRepository = $machineRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->hub = $hub;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(StartOrderMessage $message): void
    {
        try {
            $order = $this->orderRepository->findByUuid($message->getOrderUuid());
            $machine = $this->machineRepository->findByUuid($message->getMachineUuid());

            if (!$order) {
                throw new OrderNotFoundException($message->getOrderUuid());
            }

            if (!$machine) {
                throw new MachineNotFoundException($message->getMachineUuid());
            }

            $this->publishEvent($order->getUuid(), $order->getType()->getValue(), "received", "Commande reçue et en attente de traitement");
            sleep(1);

            try {
                $order->start();
                $this->orderRepository->save($order);

                $event = new OrderStarted($order->getUuid(), $order->getType()->getValue(), 1);
                $this->eventDispatcher->dispatch($event);

                $steps = [
                    ["grinding", "Mouture des grains de café en cours", 2],
                    ["heating", "Chauffe de l'eau en cours", 3],
                    ["brewing", "Infusion en cours", 4],
                    ["finalizing", "Finalisation de la préparation", 5]
                ];

                foreach ($steps as [$status, $description, $stepIndex]) {
                    sleep(1);
                    $this->publishEvent(
                        $order->getUuid(),
                        $order->getType()->getValue(),
                        $status,
                        $description,
                        $stepIndex
                    );
                }

                sleep(1);

                $order->complete();
                $this->orderRepository->save($order);

                $event = new OrderCompleted($order->getUuid(), $order->getType()->getValue(), 6);
                $this->eventDispatcher->dispatch($event);

                $this->publishEvent(
                    $order->getUuid(),
                    $order->getType()->getValue(),
                    "ready",
                    "Votre commande est prête. Bonne dégustation !",
                    6
                );

            } catch (\LogicException $e) {
                $this->publishEvent(
                    $order->getUuid(),
                    $order->getType()->getValue(),
                    "ready",
                    "Votre commande est prête. Bonne dégustation !",
                    6
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function publishEvent(string $orderUuid, string $coffeeType, string $status, string $description = null, int $stepIndex = 0): void
    {
        $payload = [
            'orderUuid' => $orderUuid,
            'status' => $status,
            'type' => $coffeeType,
            'description' => $description,
            'stepIndex' => $stepIndex,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $jsonPayload = json_encode($payload);

        $update = new Update(
            ["orders/{$orderUuid}"],
            $jsonPayload
        );

        try {
            $this->hub->publish($update);
        } catch (\Exception $e) {
            error_log('Erreur lors de la publication sur Mercure: ' . $e->getMessage());
        }
    }
}