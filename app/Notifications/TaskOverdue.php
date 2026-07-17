<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification
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
            'title' => 'Task Terlambat',
            'body' => 'Task "'.$this->task->nama.'" melewati deadline ('.$this->task->deadline_selesai?->translatedFormat('d M Y').') dan ditandai Tidak Selesai.',
            'url' => route('admin.task-saya.index'),
            'task_id' => $this->task->id,
            'icon' => 'bi-exclamation-octagon',
            'color' => 'danger',
        ];
    }
}
