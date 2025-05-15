<?php

namespace App\CoffeeMachine\Domain\Entity;

use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCancelled;
use App\CoffeeMachine\Domain\Event\Machine\MachineOrderCreated;
use App\CoffeeMachine\Domain\Event\Machine\MachineStarted;
use App\CoffeeMachine\Domain\Event\Machine\MachineStopped;
use App\CoffeeMachine\Domain\Exception\OrderException;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\MachineStatus;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use Ramsey\Uuid\Uuid;

class CoffeeMachine
{
    private string $uuid;
    private MachineStatus $status;

    /** @var CoffeeOrder[] */
    private array $orders = [];

    /** @var object[] */
    private array $recordedEvents = [];

    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        ?MachineStatus $status = null,
        ?string $uuid = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
        $this->status = $status ?? new MachineStatus('off');
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStatus(): MachineStatus
    {
        return $this->status;
    }

    /**
     * @return CoffeeOrder[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function startMachine(): string
    {
        if ('on' === $this->status->getValue()) {
            return 'The machine is already on.';
        }

        if ('error' === $this->status->getValue()) {
            return 'Unable to start the machine. Please contact maintenance.';
        }

        $this->status = new MachineStatus('on');
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new MachineStarted($this->uuid));

        return 'Welcome! The coffee machine is ready to use.';
    }

    public function stopMachine(): string
    {
        if ('off' === $this->status->getValue()) {
            return 'The machine is already off.';
        }

        $this->status = new MachineStatus('off');
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new MachineStopped($this->uuid));

        return 'Goodbye! The coffee machine has been turned off.';
    }

    public function createOrder(
        CoffeeType $type,
        CoffeeIntensity $intensity,
        SugarLevel $sugarLevel,
    ): ?CoffeeOrder {
        $order = new CoffeeOrder($type, $intensity, $sugarLevel, $this->uuid);
        $this->orders[] = $order;

        $this->recordEvent(new MachineOrderCreated($order->getUuid(), $order->getType()->getValue(), $order->getStatus()->getValue()));

        return $order;
    }

    /**
     * @throws OrderException
     */
    public function cancelOrder(CoffeeOrder $order): bool
    {
        if ($order->getMachineUuid() !== $this->uuid) {
            throw new OrderException('This order does not belong to this machine.');
        }

        switch ($order->getStatus()->getValue()) {
            case OrderStatus::CANCELLED:
                throw new OrderException('Order was already cancelled.');
            case OrderStatus::COMPLETED:
                throw new OrderException('A completed order cannot be cancelled.');
            case OrderStatus::PREPARING:
                throw new OrderException('An order in preparation cannot be cancelled.');
            case OrderStatus::PENDING:
                $this->recordEvent(new MachineOrderCancelled($order->getUuid(), $order->getType()->getValue(), $order->getStatus()->getValue()));
                $order->cancel();

                return true;

            default:
                throw new OrderException('Unknown order status.');
        }
    }

    private function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => (string) $this->status,
            'orders_count' => count($this->orders),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
