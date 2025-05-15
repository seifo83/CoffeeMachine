<?php

namespace App\Tests\CoffeeMachine\Unit\Infrastructure;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

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

        $update = new Update(
            ['orders/test-123'],
            json_encode($payload)
        );

        $hub->publish($update);

        return new Response('Mercure message published to topic: orders/test-123');
    }
}
