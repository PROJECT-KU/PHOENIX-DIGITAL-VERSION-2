<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
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
            'title' => 'Task Baru',
            'body' => 'Anda mendapat task: '.$this->task->nama.' (deadline '.$this->task->deadline_selesai?->translatedFormat('d M Y').')',
            'url' => route('admin.task-saya.index'),
            'task_id' => $this->task->id,
            'icon' => 'bi-clipboard-plus',
            'color' => 'primary',
        ];
    }
}
