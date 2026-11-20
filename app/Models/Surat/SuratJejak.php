<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris riwayat perubahan sebuah surat.
 *
 * Dicatat SENDIRI oleh model Surat, bukan oleh layar: perubahan bisa datang
 * dari borang, dari halaman detail, dari perintah terjadwal, atau dari
 * perbaikan data — dan riwayat yang hanya mencatat sebagian lebih menyesatkan
 * daripada tidak ada riwayat sama sekali.
 */
class SuratJejak extends Model
{
    protected $table = 'surat_jejak';

    protected $fillable = ['surat_id', 'aksi', 'keterangan', 'oleh'];

    public const AKSI = [
        'dicatat' => ['Dicatat', 'bi-journal-plus', '#17a974'],
        'diubah' => ['Diubah', 'bi-pencil', '#0284c7'],
        'status' => ['Status', 'bi-flag', '#7c3aed'],
        'disposisi' => ['Disposisi', 'bi-send', '#d97706'],
        'lampiran' => ['Pindaian', 'bi-paperclip', '#0891b2'],
        'dihapus' => ['Dihapus', 'bi-trash', '#e11d48'],
        'dipulihkan' => ['Dipulihkan', 'bi-arrow-counterclockwise', '#17a974'],
        'dimusnahkan' => ['Dimusnahkan', 'bi-fire', '#9f1239'],
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oleh');
    }

    public function getAksiLabelAttribute(): string
    {
        return self::AKSI[$this->aksi][0] ?? ucfirst($this->aksi);
    }

    public function getIkonAttribute(): string
    {
        return self::AKSI[$this->aksi][1] ?? 'bi-dot';
    }

    public function getWarnaAttribute(): string
    {
        return self::AKSI[$this->aksi][2] ?? '#64748b';
    }

    /** "20 November 2026, 14.05" — tanggal lengkap, karena riwayat dibaca jauh di kemudian hari. */
    public function getWaktuTeksAttribute(): string
    {
        return $this->created_at?->locale('id')->translatedFormat('d F Y, H.i') ?? '-';
    }

    public function getPelakuTeksAttribute(): string
    {
        return $this->pelaku?->name ?: 'Sistem';
    }
}
