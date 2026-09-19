<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris jejak moderasi testimoni. Lihat App\Support\RiwayatTestimoni. */
class TestimoniRiwayat extends Model
{
    protected $table = 'testimoni_riwayat';

    public const UPDATED_AT = null;

    protected $fillable = ['testimoni_id', 'user_id', 'aksi', 'keterangan'];

    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Warna & ikon per aksi, dipakai di jendela detail. */
    public function tampilan(): array
    {
        return match ($this->aksi) {
            'dibuat' => ['bi-plus-circle-fill', '#7c3aed', 'Dibuat'],
            'disetujui' => ['bi-check-circle-fill', '#16a34a', 'Disetujui'],
            'ditolak' => ['bi-x-circle-fill', '#dc2626', 'Ditolak'],
            'disorot' => ['bi-star-fill', '#d97706', 'Disorot'],
            'sorot-dilepas' => ['bi-star', '#64748b', 'Sorotan dilepas'],
            'diubah' => ['bi-pencil-fill', '#2563eb', 'Diubah'],
            'diarsipkan' => ['bi-archive-fill', '#64748b', 'Diarsipkan'],
            'dipulihkan' => ['bi-arrow-counterclockwise', '#0ea5e9', 'Dipulihkan'],
            default => ['bi-dot', '#64748b', ucfirst($this->aksi)],
        };
    }
}
