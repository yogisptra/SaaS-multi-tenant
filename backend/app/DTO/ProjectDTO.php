<?php

namespace App\DTO;

class ProjectDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly string $status = 'pending',
        public readonly ?string $start_date = null,
        public readonly ?string $due_date = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'pending',
            start_date: $data['start_date'] ?? null,
            due_date: $data['due_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
        ], fn ($value) => $value !== null);
    }
}
