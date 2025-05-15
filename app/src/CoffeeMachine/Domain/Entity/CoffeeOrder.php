<?php

namespace App\CoffeeMachine\Domain\Entity;

use App\CoffeeMachine\Domain\Event\Order\OrderCompleted;
use App\CoffeeMachine\Domain\Event\Order\OrderStarted;
use App\CoffeeMachine\Domain\ValueObject\CoffeeIntensity;
use App\CoffeeMachine\Domain\ValueObject\CoffeeType;
use App\CoffeeMachine\Domain\ValueObject\OrderStatus;
use App\CoffeeMachine\Domain\ValueObject\SugarLevel;
use Ramsey\Uuid\Uuid;

class CoffeeOrder
{
    private string $uuid;
    private readonly CoffeeType $type;
    private readonly CoffeeIntensity $intensity;
    private readonly SugarLevel $sugarLevel;
    private readonly string $machineUuid;
    private OrderStatus $status;

    /** @var object[] */
    private array $recordedEvents = [];
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        CoffeeType $type,
        CoffeeIntensity $intensity,
        SugarLevel $sugarLevel,
        string $machineUuid,
        ?string $uuid = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4()->toString();
        $this->type = $type;
        $this->intensity = $intensity;
        $this->sugarLevel = $sugarLevel;
        $this->machineUuid = $machineUuid;
        $this->status = new OrderStatus(OrderStatus::PENDING);
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getType(): CoffeeType
    {
        return $this->type;
    }

    public function getIntensity(): CoffeeIntensity
    {
        return $this->intensity;
    }

    public function getSugarLevel(): SugarLevel
    {
        return $this->sugarLevel;
    }

    public function getMachineUuid(): string
    {
        return $this->machineUuid;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function start(): void
    {
        if (OrderStatus::PENDING !== $this->status->getValue()) {
            throw new \LogicException('Order can only start preparation if it is pending.');
        }

        $this->status = new OrderStatus(OrderStatus::PREPARING);
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new OrderStarted($this->uuid, $this->type->getValue(), $this->status->getValue()));
    }

    public function complete(): void
    {
        if (OrderStatus::PREPARING !== $this->status->getValue()) {
            throw new \LogicException('Order can only be completed if it is currently being prepared.');
        }

        $this->status = new OrderStatus(OrderStatus::COMPLETED);
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new OrderCompleted($this->uuid, $this->type->getValue(), $this->status->getValue()));
    }

    public function cancel(): void
    {
        $this->status = new OrderStatus(OrderStatus::CANCELLED);
        $this->updatedAt = new \DateTimeImmutable();
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
     * @return array{
     *     uuid: string,
     *     type: string,
     *     intensity: string,
     *     sugar_level: string,
     *     status: string,
     *     status_label: string,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => (string) $this->type,
            'intensity' => (string) $this->intensity,
            'sugar_level' => (string) $this->sugarLevel,
            'status' => (string) $this->status,
            'status_label' => match ((string) $this->status) {
                'PENDING' => 'En attente',
                'PREPARING' => 'Préparation en cours',
                'COMPLETED' => 'Terminé',
                'CANCELLED' => 'Annulé',
                default => 'Inconnu',
            },
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
