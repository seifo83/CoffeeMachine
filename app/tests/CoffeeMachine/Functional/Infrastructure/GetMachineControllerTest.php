<?php

namespace App\Tests\CoffeeMachine\Functional\Infrastructure;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\Repository\CoffeeMachineRepositoryInterface;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetMachineControllerTest extends WebTestCase
{
    private const MACHINE_UUID = '491050c8-8cae-4d55-b7d8-d91f70bf71bf';

    /** @var KernelBrowser */
    private static $client;

    /** @var CoffeeMachineRepositoryInterface */
    private static $repository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$client = static::createClient();

        $repository = self::$client->getContainer()->get(CoffeeMachineRepositoryInterface::class);

        if (!$repository instanceof CoffeeMachineRepositoryInterface) {
            throw new \RuntimeException('Le service récupéré n\'est pas du type CoffeeMachineRepositoryInterface');
        }

        self::$repository = $repository;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createMachine();
    }

    private function createMachine(): void
    {
        $existingMachine = self::$repository->findByUuid(self::MACHINE_UUID);
        if ($existingMachine) {
            return;
        }

        $machine = new CoffeeMachine(
            new MachineStatus('on'),
            self::MACHINE_UUID
        );

        self::$repository->save($machine);
    }

    public function testGetMachineReturnsSuccessResponse(): void
    {
        $token = $this->getJwtToken();

        self::$client->request(
            'GET',
            '/api/machine',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $statusCode = self::$client->getResponse()->getStatusCode();
        $content = self::$client->getResponse()->getContent();

        if (false === $content) {
            $this->fail('La réponse ne contient pas de contenu');
        }

        $this->assertSame(Response::HTTP_OK, $statusCode, "Received unexpected status code. Response: $content");

        $this->assertTrue(
            self::$client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'La réponse n\'est pas au format JSON'
        );

        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertEquals(self::MACHINE_UUID, $responseData['uuid']);
    }

    public function testGetMachineWithInvalidUserReturnsForbidden(): void
    {
        self::$client->restart();

        self::$client->request(
            'GET',
            '/api/machine',
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $this->assertContains(
            self::$client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN]
        );
    }

    private function getJwtToken(): string
    {
        self::$client->restart();

        $loginData = [
            'username' => 'admin',
            'password' => 'admin',
        ];

        $jsonData = json_encode($loginData);
        if (false === $jsonData) {
            throw new \RuntimeException('Impossible d\'encoder les données de connexion en JSON');
        }

        self::$client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonData
        );

        $statusCode = self::$client->getResponse()->getStatusCode();
        if (Response::HTTP_OK !== $statusCode) {
            $responseContent = self::$client->getResponse()->getContent();
            if (false === $responseContent) {
                $responseContent = 'Pas de contenu dans la réponse';
            }
            $this->fail("Login failed with status $statusCode: $responseContent");
        }

        $content = self::$client->getResponse()->getContent();

        if (false === $content) {
            throw new \RuntimeException('La réponse ne contient pas de contenu');
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \RuntimeException("Invalid JSON response: $content");
        }

        if (!isset($data['token'])) {
            throw new \RuntimeException("Token not found in response: $content");
        }

        /** @var string $token */
        $token = $data['token'];

        return $token;
    }
}
