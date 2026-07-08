<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead($id)
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $notif = $user->notifications()->where('id', $id)->first();
        if ($notif) {
            $notif->markAsRead();
            $url = $notif->data['url'] ?? null;
            $taskId = $notif->data['task_id'] ?? null;
            if ($url) {
                if ($taskId) {
                    $url .= (str_contains($url, '?') ? '&' : '?').'open_task='.$taskId;
                }

                return redirect($url);
            }
        }

        return null;
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        // Hanya notifikasi bulan berjalan yang ditandai (yang lama tak lagi tampil).
        $this->bulanIni($user->unreadNotifications())->update(['read_at' => now()]);
    }

    /**
     * Batasi notifikasi ke bulan & tahun kalender SAAT INI (bukan filter periode task).
     * Efeknya: notifikasi bulan lalu otomatis "reset"/hilang begitu masuk bulan baru.
     */
    protected function bulanIni($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.layout.notification-bell', [
            'items' => $user ? $this->bulanIni($user->notifications())->latest()->take(15)->get() : collect(),
            'unread' => $user ? $this->bulanIni($user->unreadNotifications())->count() : 0,
        ]);
    }
}
