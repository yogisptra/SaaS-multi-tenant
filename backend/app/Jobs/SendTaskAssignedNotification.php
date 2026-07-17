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

class SendTaskAssignedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Task $task)
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
            'title' => 'New Task Assigned',
            'message' => "You have been assigned to task \"{$task->title}\" in project \"{$task->project->name}\".",
            'is_read' => false,
        ]);

        // Simulate email via Log
        Log::info('📧 [EMAIL SIMULATION] Task Assignment Notification', [
            'to' => $task->assignee->email,
            'to_name' => $task->assignee->name,
            'subject' => "New Task Assigned: {$task->title}",
            'body' => "Hi {$task->assignee->name}, you have been assigned to task \"{$task->title}\" in project \"{$task->project->name}\". Due date: " . ($task->due_date?->format('Y-m-d') ?? 'Not set'),
        ]);

        Log::info("✅ Notification created for user {$task->assignee->name} (ID: {$task->assignee->id})");
    }
}
