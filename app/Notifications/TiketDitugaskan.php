<?php

namespace App\Notifications;

use App\Models\CustomerMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Pemberitahuan ke petugas saat sebuah tiket helpdesk diserahkan kepadanya.
 *
 * Tanpa ini penugasan berjalan diam-diam: yang ditugaskan baru tahu kalau
 * kebetulan membuka menu dan menyaring "Tiket saya".
 */
class TiketDitugaskan extends Notification
{
    use Queueable;

    public function __construct(public CustomerMessage $pesan) {}

    /** Tidak pernah menggagalkan aksi utama (penugasannya sendiri). */
    public static function kirim(CustomerMessage $pesan, ?User $petugas): void
    {
        // Menugaskan diri sendiri tidak perlu diberitahukan.
        if (! $petugas || $petugas->id === auth()->id()) {
            return;
        }

        try {
            $petugas->notify(new self($pesan));
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi penugasan tiket: '.$e->getMessage());
        }
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        [$prioritas] = $this->pesan->tampilanPrioritas();

        return [
            'title' => 'Tiket helpdesk untuk Anda 🎫',
            'body' => $this->pesan->ticket.' dari '.$this->pesan->name
                .' (prioritas '.$prioritas.', batas balas '.$this->pesan->batasJam().' jam).',
            'url' => route('admin.customer-message.detail', $this->pesan->id),
            'icon' => 'bi-person-check',
            'color' => 'info',
        ];
    }
}
