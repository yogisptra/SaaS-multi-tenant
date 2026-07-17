<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Task $task)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->task->assigned_to),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->uuid,
            'title' => $this->task->title,
            'message' => 'You have been assigned to a new task.',
        ];
    }
}
