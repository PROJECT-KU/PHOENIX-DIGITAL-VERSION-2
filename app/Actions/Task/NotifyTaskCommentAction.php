<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCommented;
use App\Notifications\TaskMentioned;
use Illuminate\Support\Str;

/**
 * Sumber tunggal notifikasi komentar task, dipakai baik dari "Task Saya"
 * maupun "Penyelesaian Task" (admin) agar penerima selalu konsisten.
 *
 * Penerima (kecuali penulis komentar):
 *  - Yang di-@mention di body -> notifikasi khusus "Anda disebut" (TaskMentioned).
 *  - Sisa rantai task: penerima (user_id) + pemberi (assigned_by) + SEMUA atasan
 *    pemberi (rantai ke atas) -> TaskCommented ke halaman "Task Saya".
 *  - Admin ber-izin manage_task -> TaskCommented "toAdmin" (Penyelesaian Task).
 * Himpunan dibuat saling lepas: yang disebut TIDAK dobel dapat notif komentar.
 */
class NotifyTaskCommentAction
{
    public function execute(Task $task, User $author, ?string $body = null): void
    {
        $authorId = $author->id;
        $authorName = $author->name;

        // Semua sub-task dalam grup (komentar di level group).
        $subTasks = Task::where('group_id', $task->group_id)->with('karyawan')->get();

        // Anggota grup yang disebut (@nama-depan) di body -> notif "Anda disebut".
        $mentionedIds = $this->mentionedMemberIds($subTasks, $body, $authorId);
        foreach ($subTasks->whereIn('user_id', $mentionedIds) as $st) {
            $st->karyawan?->notify(new TaskMentioned($st, $authorName));
        }

        // Admin (manage_task) -> versi "toAdmin".
        $admins = User::whereHas('role.permissions', fn ($p) => $p->where('name', 'manage_task'))
            ->where('id', '!=', $authorId)
            ->whereNotIn('id', $mentionedIds)
            ->get();
        $adminIds = $admins->pluck('id')->all();

        // Rantai penerima: tiap sub-task (penerima + pemberi + atasan pemberi).
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
            ->whereNotIn('id', $adminIds)      // admin sudah dapat versi admin
            ->whereNotIn('id', $mentionedIds)  // yang disebut sudah dapat notif mention
            ->get();

        // Tiap penerima ditautkan ke SUB-TASK miliknya (agar buka & clear notif tepat).
        foreach ($chain as $u) {
            $ref = $subTasks->firstWhere('user_id', $u->id) ?? $task;
            $u->notify(new TaskCommented($ref, $authorName, false));
        }
        foreach ($admins as $u) {
            $u->notify(new TaskCommented($task, $authorName, true));
        }
    }

    /**
     * ID anggota grup yang disebut di body (@nama-depan), minus penulis.
     *
     * @return array<int>
     */
    private function mentionedMemberIds($subTasks, ?string $body, int $authorId): array
    {
        if (! $body || ! preg_match_all('/@([\p{L}\p{N}_]+)/u', $body, $m)) {
            return [];
        }
        $handles = array_map(fn ($h) => mb_strtolower($h), $m[1]);

        $ids = [];
        foreach ($subTasks as $st) {
            $user = $st->karyawan;
            if (! $user || $user->id === $authorId) {
                continue;
            }
            $first = mb_strtolower((string) Str::of($user->name)->trim()->explode(' ')->first());
            if ($first !== '' && in_array($first, $handles, true)) {
                $ids[] = $user->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
