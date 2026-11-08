<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Pesan atau lamaran baru dari situs Asthana.
 *
 * Kanal 'database' → lonceng lemon; listener SendWebPushNotification ikut
 * meneruskannya sebagai Web Push. Bentuk datanya sama dengan PesananBaru.
 */
class KabarSitusAsthana extends Notification
{
    use Queueable;

    public function __construct(
        public string $judul,
        public string $isi,
        public string $url,
        public string $ikon,
        public string $warna = 'primary',
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->judul,
            'body' => $this->isi,
            'url' => $this->url,
            'icon' => $this->ikon,
            'color' => $this->warna,
        ];
    }
}
