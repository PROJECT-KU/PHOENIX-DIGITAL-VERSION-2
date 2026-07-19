<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi khusus saat seseorang disebut (@mention) di diskusi grup.
 * $task = sub-task milik si penerima agar klik bell membuka Diskusi Grup-nya.
 */
class TaskMentioned extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public string $authorName) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Anda disebut',
            'body' => $this->authorName.' menyebut Anda di task "'.$this->task->nama.'".',
            'url' => route('admin.task-saya.index'),
            'task_id' => $this->task->id,
            'icon' => 'bi-at',
            'color' => 'primary',
        ];
    }
}
