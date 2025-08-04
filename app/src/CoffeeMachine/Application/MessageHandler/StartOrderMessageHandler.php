<?php

namespace App\CoffeeMachine\Application\MessageHandler;

use App\CoffeeMachine\Application\Message\StartOrderMessage;
use App\CoffeeMachine\Application\Notify\OrderNotifyInterface;
use App\CoffeeMachine\Domain\Event\Order\OrderCompleted;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use App\CoffeeMachine\Domain\Exception\MachineNotFoundException;
use App\CoffeeMachine\Domain\Exception\OrderNotFoundException;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\Repository\CoffeeOrderRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class StartOrderMessageHandler
{
    public function __construct(
        private readonly CoffeeOrderRepositoryInterface $orderRepository,
        private readonly CoffeeMachineRepositoryInterface $machineRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly OrderNotifyInterface $orderNotifier,
    ) {
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

            $this->orderNotifier->notify(
                $order->getUuid(),
                $order->getType()->getValue(),
                'received',
                'Commande reçue et en attente de traitement'
            );
            sleep(2);

            $order->start();
            $this->orderRepository->save($order);

            $event = new OrderStarted($order->getUuid(), $order->getType()->getValue(), 1);
            $this->eventDispatcher->dispatch($event);

            $steps = [
                ['grinding', 'Mouture des grains de café en cours', 2],
                ['heating', "Chauffe de l'eau en cours", 3],
                ['brewing', 'Infusion en cours', 4],
                ['finalizing', 'Finalisation de la préparation', 5],
            ];

            foreach ($steps as [$status, $description, $stepIndex]) {
                sleep(1);
                $this->orderNotifier->notify(
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

            $this->orderNotifier->notify(
                $order->getUuid(),
                $order->getType()->getValue(),
                'ready',
                'Votre commande est prête. Bonne dégustation !',
                6
            );
        } catch (\Exception $e) {
            error_log('Erreur dans StartOrderMessageHandler: '.$e->getMessage());

            if (isset($order)) {
                $this->orderNotifier->notify(
                    $order->getUuid(),
                    $order->getType()->getValue(),
                    'ready',
                    'Votre commande est prête. Bonne dégustation !',
                    6
                );
            }
        }
    }
}
