<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Jobs\SendTaskAssignedNotification;

class TaskAssignedListener
{
    public function handle(TaskAssigned $event): void
    {
        SendTaskAssignedNotification::dispatch($event->task);
    }
}
