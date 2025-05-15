<?php

namespace App\Tests\CoffeeMachine\Functional\Infrastructure;

use App\CoffeeMachine\Domain\Entity\CoffeeMachine;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertTrue;

class CoffeeMachineControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ?EntityManagerInterface $entityManager = null;
    private string $machineId = '';
    private string $token = '';

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $this->entityManager = $em;

        $this->resetDatabase();
        $this->createTestData();
        $this->token = $this->getToken();
    }

    private function resetDatabase(): void
    {
        if (!$this->entityManager) {
            throw new \RuntimeException('EntityManager is null.');
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        try {
            $schemaTool->dropSchema($metadata);
        } catch (\Exception) {
        }

        try {
            $schemaTool->createSchema($metadata);
        } catch (\Exception $e) {
            throw new \RuntimeException('Impossible de créer le schéma de la base de données: '.$e->getMessage());
        }
    }

    private function createTestData(): void
    {
        if (!$this->entityManager) {
            throw new \RuntimeException('EntityManager is null.');
        }

        $machine = new CoffeeMachine(new MachineStatus('on'));
        $this->entityManager->persist($machine);

        $order = $machine->createOrder(
            new CoffeeType('espresso'),
            new CoffeeIntensity('medium'),
            new SugarLevel('1_dose')
        );

        if (null !== $order) {
            $this->entityManager->persist($order);
        }

        $this->entityManager->flush();

        $this->machineId = $machine->getUuid();
    }

    protected function getToken(): string
    {
        $json = json_encode(['username' => 'admin', 'password' => 'admin']);
        if (!is_string($json)) {
            throw new \RuntimeException('Invalid JSON encode.');
        }

        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $json
        );

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            throw new \RuntimeException('Invalid response content.');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['token']) || !is_string($data['token'])) {
            throw new \RuntimeException('Invalid token data.');
        }

        return $data['token'];
    }

    public function testGetMachine(): void
    {
        $this->client->request('GET', '/api/machines/'.$this->machineId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertArrayHasKey('uuid', $data);
        assertArrayHasKey('status', $data);
        assertEquals($this->machineId, $data['uuid']);
        assertEquals('on', $data['status']);
    }

    public function testGetMachineNotFound(): void
    {
        $this->client->request('GET', '/api/machines/non-existent-uuid', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testStartMachine(): void
    {
        $this->client->request('POST', '/api/machines/'.$this->machineId.'/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertArrayHasKey('message', $data);
    }

    public function testStopMachine(): void
    {
        $this->client->request('POST', '/api/machines/'.$this->machineId.'/stop', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertArrayHasKey('message', $data);
    }

    public function testGetOrders(): void
    {
        $this->client->request('GET', '/api/machines/'.$this->machineId.'/orders', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<int, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertNotEmpty($data);
    }

    public function testCreateOrder(): void
    {
        $orderData = [
            'type' => 'latte',
            'intensity' => 'low',
            'sugar_level' => '2_doses',
        ];

        $json = json_encode($orderData);
        if (!is_string($json)) {
            throw new \RuntimeException('Invalid JSON encode.');
        }

        $this->client->request('POST', '/api/machines/'.$this->machineId.'/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertArrayHasKey('uuid', $data);
    }

    public function testCancelLastOrder(): void
    {
        $this->client->request('DELETE', '/api/machines/'.$this->machineId.'/orders/last', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->token,
        ]);

        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        if (!is_string($content)) {
            $this->fail('Invalid response content');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        assertArrayHasKey('success', $data);
        assertTrue((bool) $data['success']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (null !== $this->entityManager) {
            $this->entityManager->close();
        }

        $this->entityManager = null;
    }
}
