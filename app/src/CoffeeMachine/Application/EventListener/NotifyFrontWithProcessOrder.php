<?php

namespace App\CoffeeMachine\Application\EventListener;

use App\CoffeeMachine\Domain\Event\AbstractCoffeeOrderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsEventListener]
class NotifyFrontWithProcessOrder
{
    public function __construct(private HubInterface $hub)
    {
    }

    public function __invoke(AbstractCoffeeOrderEvent $event): void
    {
        $payload = $this->buildPayload($event);

        $jsonPayload = json_encode($payload);
        if (false === $jsonPayload) {
            throw new \RuntimeException('Failed to encode payload as JSON');
        }

        $update = new Update(
            ["orders/{$event->getOrderUuid()}"],
            $jsonPayload
        );

        $this->hub->publish($update);
    }

    /**
     * @return array<string, string>
     */
    public function buildPayload(AbstractCoffeeOrderEvent $event): array
    {
        return [
            'orderUuid' => $event->getOrderUuid(),
            'status' => $event->getOrderStatus(),
            'type' => $event->getCoffeeType(),
            'eventType' => $event::class,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
