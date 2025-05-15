<?php

namespace App\CoffeeMachine\Application\DTO;

class MachineDTO
{
    public string $uuid;
    public string $status;
    public int $ordersCount;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        string $uuid,
        string $status,
        int $ordersCount,
        string $createdAt,
        string $updatedAt,
    ) {
        $this->uuid = $uuid;
        $this->status = $status;
        $this->ordersCount = $ordersCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOrdersCount(): int
    {
        return $this->ordersCount;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'orders_count' => $this->ordersCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
