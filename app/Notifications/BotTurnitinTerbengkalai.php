<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Pengingat: ada pengecekan bot yang menunggu tangan admin lebih dari sehari.
 *
 * Panel di dasbor sudah menampilkannya, tetapi panel hanya bekerja bila ada
 * yang membukanya. Pengecekan yang gagal Jumat sore bisa mengendap sampai
 * Senin tanpa satu pun tanda, dan yang menunggu selama itu adalah
 * pelanggannya — bukan kita.
 *
 * Sengaja SATU notifikasi ringkasan per pengiriman, bukan satu per pengecekan:
 * lima pengecekan terbengkalai tidak butuh lima lonceng, dan yang perlu
 * diketahui hanyalah "ada yang menunggu, yang terlama sekian hari".
 */
class BotTurnitinTerbengkalai extends Notification
{
    use Queueable;

    /**
     * @param  int  $jumlah  berapa pengecekan yang menunggu
     * @param  string  $terlama  lama menunggu yang paling tua, mis. "5 hari"
     * @param  ?string  $nomorTerlama  nomor pesanan yang paling lama menunggu
     */
    public function __construct(
        public int $jumlah,
        public string $terlama,
        public ?string $nomorTerlama = null,
    ) {}

    /** Kirim ke semua admin yang boleh melihat Pesanan Toko. */
    public static function kirim(int $jumlah, string $terlama, ?string $nomorTerlama = null): int
    {
        try {
            $admins = User::whereHas('role.permissions', fn ($q) => $q->where('name', 'view_pemesanantoko'))->get();

            if ($admins->isEmpty()) {
                return 0;
            }

            NotificationFacade::send($admins, new self($jumlah, $terlama, $nomorTerlama));

            return $admins->count();
        } catch (\Throwable $e) {
            // Kegagalan notifikasi tidak boleh menggagalkan perintah terjadwal
            // yang memanggilnya — ia berbagi satu proses dengan tugas lain.
            Log::warning('Gagal kirim pengingat bot terbengkalai: '.$e->getMessage());

            return 0;
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $nomor = $this->nomorTerlama ? " ({$this->nomorTerlama})" : '';

        return [
            'title' => 'Pengecekan bot menunggu Anda ⏳',
            'body' => "{$this->jumlah} pengecekan Turnitin berhenti dan menunggu dikerjakan manual. "
                ."Yang terlama sudah {$this->terlama}{$nomor}.",
            'url' => route('admin.dashboard'),
            'icon' => 'bi-robot',
            'color' => 'danger',
        ];
    }
}
