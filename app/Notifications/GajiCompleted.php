<?php

namespace App\Notifications;

use App\Models\GajiKaryawans;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dikirim ke karyawan saat gajinya ditandai LUNAS (status completed).
 * Sengaja hanya saat transisi ke completed — gaji draft belum jadi haknya,
 * dan mengedit gaji yang sudah completed tidak mengirim notifikasi lagi.
 */
class GajiCompleted extends Notification
{
    use Queueable;

    public function __construct(public GajiKaryawans $gaji) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Gaji Sudah Dibayarkan',
            'body' => 'Gaji periode '.$this->gaji->periode_label.' sebesar '
                .$this->gaji->total_formatted.' telah dibayarkan pada '
                .$this->gaji->tanggal_transaksi_formatted.'. Slip gaji bisa diunduh.',
            'url' => route('admin.gajikaryawan.index'),
            'gaji_id' => $this->gaji->id,
            'icon' => 'bi-cash-coin',
            'color' => 'success',
        ];
    }
}
