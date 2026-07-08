<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDeadlineSoon extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Deadline Mendekat',
            'body' => 'Task "'.$this->task->nama.'" jatuh tempo besok ('.$this->task->deadline_selesai?->translatedFormat('d M Y').'). Segera selesaikan.',
            'url' => route('admin.task-saya.index'),
            'task_id' => $this->task->id,
            'icon' => 'bi-alarm',
            'color' => 'warning',
        ];
    }
}
