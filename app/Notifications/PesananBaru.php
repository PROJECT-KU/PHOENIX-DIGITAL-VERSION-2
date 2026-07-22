<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Notifikasi ke admin saat ada pesanan toko yang BARU dibayar (paid).
 *
 * Dikirim lewat channel 'database' → muncul di lonceng. Listener
 * SendWebPushNotification ikut mengirimkannya sebagai Web Push, sehingga admin
 * tetap dapat popup walau website/tab sedang ditutup (asalkan admin sudah
 * mengaktifkan notifikasi perangkat sekali).
 */
class PesananBaru extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * Kirim ke semua admin yang boleh melihat Pesanan Toko. Dibungkus agar
     * kegagalan notifikasi TIDAK pernah menggagalkan aksi utama (mis. poller
     * QRIS menandai order lunas).
     */
    public static function kirim(Order $order): void
    {
        try {
            $admins = User::whereHas('role.permissions', fn ($q) => $q->where('name', 'view_pemesanantoko'))->get();

            if ($admins->isNotEmpty()) {
                NotificationFacade::send($admins, new self($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi pesanan baru: '.$e->getMessage());
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $nama = $this->order->customer->nama ?? 'Pelanggan';
        $total = 'Rp '.number_format((int) $this->order->total, 0, ',', '.');

        return [
            'title' => 'Pesanan baru 🎉',
            'body' => "Pesanan {$this->order->order_number} sudah dibayar ({$total}) — {$nama}. Siap diproses.",
            'url' => route('admin.pesanantoko.detail', $this->order),
            'icon' => 'bi-cart-check',
            'color' => 'success',
        ];
    }
}
