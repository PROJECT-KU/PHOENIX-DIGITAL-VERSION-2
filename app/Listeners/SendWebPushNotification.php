<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

/**
 * Kirim Web Push otomatis untuk SETIAP notifikasi database.
 * Tidak menyentuh class-class Notification maupun logic menu —
 * cukup "menempel" di event NotificationSent.
 */
class SendWebPushNotification
{
    public function __construct(protected WebPushService $webPush) {}

    public function handle(NotificationSent $event): void
    {
        // Hanya untuk channel database (hindari kirim ganda bila ada channel lain).
        if ($event->channel !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;
        if (! $notifiable instanceof User) {
            return;
        }

        try {
            // Ambil data yang sama seperti yang tersimpan di database.
            $data = method_exists($event->notification, 'toArray')
                ? $event->notification->toArray($notifiable)
                : [];

            // Jumlah unread bulan berjalan — SAMA dengan hitungan lonceng,
            // supaya badge di ikon PWA konsisten.
            $unread = $notifiable->unreadNotifications()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();

            $this->webPush->sendToUser($notifiable, [
                'title' => $data['title'] ?? 'lemon',
                'body' => $data['body'] ?? '',
                'url' => $data['url'] ?? '/',
                'unread' => $unread,
            ]);
        } catch (\Throwable $e) {
            // Jangan pernah menggagalkan aksi utama (mis. assign task) karena push.
            Log::warning('Gagal kirim Web Push: '.$e->getMessage());
        }
    }
}
