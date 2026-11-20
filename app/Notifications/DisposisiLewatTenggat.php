<?php

namespace App\Notifications;

use App\Models\Surat\SuratDisposisi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Pengingat disposisi yang tenggatnya sudah lewat.
 *
 * Dikirim SEKALI per disposisi (ditandai telat_diberitahu_pada). Pengingat
 * harian untuk hal yang sama membuat orang berhenti membacanya, dan
 * pemberitahuan yang diabaikan lebih buruk daripada tidak ada — ia membuat
 * semua pemberitahuan lain ikut diabaikan.
 */
class DisposisiLewatTenggat extends Notification
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
        $telat = $this->disposisi->batas?->diffInDays(now()) ?? 0;

        return [
            'title' => 'Disposisi Lewat Tenggat',
            'body' => ($surat?->perihal ?: 'Surat').' — telat '.(int) $telat.' hari: '
                .Str::limit((string) $this->disposisi->instruksi, 80),
            'url' => $surat ? route('admin.surat.detail', $surat) : route('admin.surat.dashboard'),
            'surat_id' => $surat?->id,
            'icon' => 'bi-exclamation-triangle-fill',
            'color' => 'danger',
        ];
    }
}
