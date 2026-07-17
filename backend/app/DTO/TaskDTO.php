<?php

namespace App\DTO;

class TaskDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly string $priority = 'medium',
        public readonly string $status = 'todo',
        public readonly ?int $assigned_to = null,
        public readonly ?string $due_date = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            priority: $data['priority'] ?? 'medium',
            status: $data['status'] ?? 'todo',
            assigned_to: $data['assigned_to'] ?? null,
            due_date: $data['due_date'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date,
        ], fn ($value) => $value !== null);
    }
}
