<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Notifikasi ke admin saat CUSTOMER mengunggah pengecekan plagiasi/AI baru
 * (paket multi-kuota, mis. file ke-2 dari 5 yang masuk berhari-hari kemudian).
 *
 * Channel 'database' → muncul di lonceng. Listener SendWebPushNotification ikut
 * mengirimnya sebagai Web Push, sehingga admin tetap dapat popup di PWA/desktop
 * walau website ditutup (asal notifikasi perangkat sudah diaktifkan sekali).
 * Notif foreground + suara real-time saat tab terbuka ditangani NotifPoller.
 */
class PengecekanBaru extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * Kirim ke semua admin yang boleh melihat Pesanan Toko. Dibungkus agar
     * kegagalan notifikasi TIDAK pernah menggagalkan unggahan customer.
     */
    public static function kirim(Order $order): void
    {
        try {
            $admins = User::whereHas('role.permissions', fn ($q) => $q->where('name', 'view_pemesanantoko'))->get();

            if ($admins->isNotEmpty()) {
                NotificationFacade::send($admins, new self($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi pengecekan baru: '.$e->getMessage());
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $nama = $this->order->customer->nama ?? 'Pelanggan';

        return [
            'title' => 'Pengecekan baru 📄',
            'body' => "Ada pengecekan plagiasi baru dari {$nama} (pesanan {$this->order->order_number}). Perlu diproses.",
            'url' => route('admin.pesanantoko.detail', $this->order),
            'icon' => 'bi-file-earmark-text',
            'color' => 'info',
        ];
    }
}
