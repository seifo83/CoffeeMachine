<?php

namespace App\CoffeeMachine\Application\EventListener;

use App\CoffeeMachine\Domain\Event\AbstractCoffeeOrderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class NotifyFrontWithProcessOrder
{
    public function __invoke(AbstractCoffeeOrderEvent $event): void
    {
        $payload = $this->buildPayload($event);
        dump('Notification front', $payload);
    }

    /**
     * @return array{
     *     orderUuid: string,
     *     coffeeType: string,
     *     status: string,
     *     eventType: class-string,
     *     timestamp: string
     * }
     */
    public function buildPayload(AbstractCoffeeOrderEvent $event): array
    {
        return [
            'orderUuid' => $event->getOrderUuid(),
            'coffeeType' => $event->getCoffeeType(),
            'status' => $event->getOrderStatus(),
            'eventType' => $event::class,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
