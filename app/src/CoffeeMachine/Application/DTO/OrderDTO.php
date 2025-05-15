<?php

namespace App\CoffeeMachine\Application\DTO;

class OrderDTO
{
    public string $uuid;
    public string $type;
    public string $intensity;
    public string $sugarLevel;
    public string $status;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        string $uuid,
        string $type,
        string $intensity,
        string $sugarLevel,
        string $status,
        string $createdAt,
        string $updatedAt,
    ) {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->intensity = $intensity;
        $this->sugarLevel = $sugarLevel;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIntensity(): string
    {
        return $this->intensity;
    }

    public function getSugarLevel(): string
    {
        return $this->sugarLevel;
    }

    public function getStatus(): string
    {
        return $this->status;
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
     * @return array{
     *     uuid: string,
     *     type: string,
     *     intensity: string,
     *     sugar_level: string,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'intensity' => $this->intensity,
            'sugar_level' => $this->sugarLevel,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
