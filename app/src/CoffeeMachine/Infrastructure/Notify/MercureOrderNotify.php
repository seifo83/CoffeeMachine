<?php

namespace App\CoffeeMachine\Infrastructure\Notify;

use App\CoffeeMachine\Application\Notify\OrderNotifyInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureOrderNotify implements OrderNotifyInterface
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    /**
     * @throws \JsonException
     */
    public function notify(string $orderUuid, string $coffeeType, string $status, ?string $description = null, int $stepIndex = 0): void
    {
        $payload = [
            'orderUuid' => $orderUuid,
            'status' => $status,
            'type' => $coffeeType,
            'description' => $description,
            'stepIndex' => $stepIndex,
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        $update = new Update(
            ["orders/{$orderUuid}"],
            $jsonPayload
        );

        try {
            $this->hub->publish($update);
        } catch (\Exception $e) {
            error_log('Erreur lors de la publication sur Mercure: '.$e->getMessage());
        }
    }
}
