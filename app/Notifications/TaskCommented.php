<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCommented extends Notification
{
    use Queueable;

    /**
     * @param  bool  $toAdmin  true bila penerima adalah admin (komentar dari karyawan)
     */
    public function __construct(public Task $task, public string $authorName, public bool $toAdmin) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Komentar Baru',
            'body' => $this->authorName.' mengomentari task "'.$this->task->nama.'".',
            'url' => $this->toAdmin ? route('admin.penyelesaian-task.index') : route('admin.task-saya.index'),
            'task_id' => $this->task->id,
            'icon' => 'bi-chat-dots',
            'color' => 'info',
        ];
    }
}
