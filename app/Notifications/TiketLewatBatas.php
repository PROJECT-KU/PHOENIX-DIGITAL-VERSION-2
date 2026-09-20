<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Pengingat harian: tiket helpdesk yang sudah lewat batas waktu membalas.
 *
 * Satu notifikasi berisi rekap, bukan satu per tiket — supaya loncengnya tidak
 * banjir dan malah diabaikan.
 */
class TiketLewatBatas extends Notification
{
    use Queueable;

    public function __construct(public Collection $tiket) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $jumlah = $this->tiket->count();
        $contoh = $this->tiket->take(3)->map(fn ($t) => $t->ticket.' · '.$t->name)->implode(', ');

        return [
            'title' => $jumlah.' tiket helpdesk lewat batas ⏰',
            'body' => $contoh.($jumlah > 3 ? ' dan '.($jumlah - 3).' lainnya.' : '.'),
            'url' => route('admin.customer-message.index', ['tab' => 'semua', 'batas' => 'lewat']),
            'icon' => 'bi-alarm',
            'color' => 'danger',
        ];
    }
}
