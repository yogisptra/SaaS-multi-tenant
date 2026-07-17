<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTaskStatusUpdatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Task $task, public string $oldStatus, public string $newStatus)
    {
    }

    public function handle(): void
    {
        $task = $this->task->load(['assignee', 'project']);

        if (!$task->assignee) {
            return;
        }

        // Create notification record in database
        Notification::create([
            'user_id' => $task->assignee->id,
            'title' => 'Task Status Updated',
            'message' => "Your task \"{$task->title}\" in project \"{$task->project->name}\" was moved from {$this->oldStatus} to {$this->newStatus}.",
            'is_read' => false,
        ]);

        // Simulate email via Log
        Log::info('📧 [EMAIL SIMULATION] Task Status Update Notification', [
            'to' => $task->assignee->email,
            'to_name' => $task->assignee->name,
            'subject' => "Task Status Updated: {$task->title}",
            'body' => "Hi {$task->assignee->name}, your task \"{$task->title}\" has been updated to {$this->newStatus}.",
        ]);
    }
}
