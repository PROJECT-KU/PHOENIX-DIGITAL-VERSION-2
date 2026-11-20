<?php

namespace App\Notifications;

use App\Models\Surat\SuratDisposisi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * "Ada surat yang didisposisikan ke Anda."
 *
 * Tanpa ini, orang yang ditugaskan hanya tahu kalau kebetulan membuka layar
 * arsip — dan disposisi yang tidak terbaca sama saja dengan disposisi yang
 * tidak pernah dibuat.
 */
class DisposisiBaru extends Notification
{
    use Queueable;

    public function __construct(public SuratDisposisi $disposisi) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $surat = $this->disposisi->surat;
        $batas = $this->disposisi->batas
            ? ' · tenggat '.$this->disposisi->batas->locale('id')->translatedFormat('d M Y')
            : '';

        return [
            'title' => 'Disposisi Surat Baru',
            'body' => ($surat?->perihal ?: 'Surat').': '
                .Str::limit((string) $this->disposisi->instruksi, 90).$batas,
            'url' => $surat ? route('admin.surat.detail', $surat) : route('admin.surat.dashboard'),
            'surat_id' => $surat?->id,
            'icon' => 'bi-send-fill',
            'color' => 'warning',
        ];
    }
}
