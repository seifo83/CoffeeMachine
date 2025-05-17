<?php

namespace App\Tests\CoffeeMachine\Unit\Infrastructure;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class MercureTestController
{
    #[Route('/test/mercure', name: 'test_mercure')]
    public function __invoke(HubInterface $hub): Response
    {
        $payload = [
            'orderUuid' => 'test-123',
            'status' => 'PENDING',
            'type' => 'espresso',
            'eventType' => 'TestManualPublish',
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $jsonPayload = json_encode($payload);
        if (false === $jsonPayload) {
            throw new \RuntimeException('Failed to encode payload');
        }

        $update = new Update(
            ['orders/test-123'],
            $jsonPayload
        );

        $hub->publish($update);

        return new Response('Mercure message published to topic: orders/test-123');
    }
}
