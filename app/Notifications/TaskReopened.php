<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TaskReopened extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public string $authorName, public string $alasan) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Task Direvisi',
            'body' => 'Task "'.$this->task->nama.'" dibuka kembali untuk revisi: '.Str::limit($this->alasan, 90),
            'url' => route('admin.task-saya.index', ['open_task' => $this->task->id]),
            'task_id' => $this->task->id,
            'icon' => 'bi-arrow-counterclockwise',
            'color' => 'warning',
        ];
    }
}
