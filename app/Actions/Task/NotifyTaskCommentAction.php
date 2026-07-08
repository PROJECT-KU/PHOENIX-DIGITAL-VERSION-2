<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCommented;

/**
 * Sumber tunggal notifikasi komentar task, dipakai baik dari "Task Saya"
 * maupun "Penyelesaian Task" (admin) agar penerima selalu konsisten.
 *
 * Penerima (kecuali penulis komentar):
 *  - Rantai task: penerima (user_id) + pemberi (assigned_by) + SEMUA atasan
 *    pemberi (rantai ke atas) -> tautan ke halaman "Task Saya".
 *  - Admin ber-izin manage_task -> tautan ke "Penyelesaian Task".
 * Kedua himpunan dibuat saling lepas (admin tidak dapat notifikasi dobel).
 */
class NotifyTaskCommentAction
{
    public function execute(Task $task, User $author): void
    {
        $authorId = $author->id;
        $authorName = $author->name;

        // Admin (manage_task) -> versi "toAdmin".
        $admins = User::whereHas('role.permissions', fn ($p) => $p->where('name', 'manage_task'))
            ->where('id', '!=', $authorId)
            ->get();
        $adminIds = $admins->pluck('id')->all();

        // Komentar di level GROUP: kumpulkan penerima dari SEMUA sub-task dalam grup
        // (tiap sub-task: penerima + pemberi + seluruh atasan pemberi).
        $subTasks = Task::where('group_id', $task->group_id)->get();
        $chainIds = [];
        foreach ($subTasks as $st) {
            $chainIds[] = $st->user_id;
            if ($st->assigned_by) {
                $chainIds[] = $st->assigned_by;
                if ($giver = User::find($st->assigned_by)) {
                    $chainIds = array_merge($chainIds, $giver->atasanIds());
                }
            }
        }
        $chainIds = array_values(array_unique(array_filter($chainIds)));

        $chain = User::whereIn('id', $chainIds)
            ->where('id', '!=', $authorId)
            ->whereNotIn('id', $adminIds) // admin sudah dapat versi admin
            ->get();

        // Tiap penerima ditautkan ke SUB-TASK miliknya (agar buka & clear notif tepat),
        // fallback ke task tempat komentar ditulis bila ia bukan anggota grup.
        foreach ($chain as $u) {
            $ref = $subTasks->firstWhere('user_id', $u->id) ?? $task;
            $u->notify(new TaskCommented($ref, $authorName, false));
        }
        foreach ($admins as $u) {
            $u->notify(new TaskCommented($task, $authorName, true));
        }
    }
}
