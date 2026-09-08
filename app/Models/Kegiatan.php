<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Satu kegiatan di kalender: rapat, tenggat, acara, libur.
 */
class Kegiatan extends Model
{
    use HasFactory, HasUuids;

    /** Jenis kegiatan beserta warnanya di kalender. */
    public const JENIS = [
        'rapat' => ['label' => 'Rapat', 'warna' => '#4f46e5', 'ikon' => 'people'],
        'tenggat' => ['label' => 'Tenggat', 'warna' => '#dc2626', 'ikon' => 'flag'],
        'acara' => ['label' => 'Acara', 'warna' => '#0891b2', 'ikon' => 'stars'],
        'libur' => ['label' => 'Libur', 'warna' => '#16a34a', 'ikon' => 'sun'],
        'lainnya' => ['label' => 'Lainnya', 'warna' => '#64748b', 'ikon' => 'dot'],
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'jenis',
        'lokasi',
        'mulai',
        'selesai',
        'seharian',
        'dibuat_oleh',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
        'seharian' => 'boolean',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function peserta(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kegiatan_peserta')->withTimestamps();
    }

    /** Kegiatan yang menyentuh rentang tanggal ini, terurut waktu mulai. */
    public function scopeDalamRentang($query, Carbon $awal, Carbon $akhir)
    {
        return $query
            ->whereBetween('mulai', [$awal->copy()->startOfDay(), $akhir->copy()->endOfDay()])
            ->orderBy('mulai');
    }

    public function label(): string
    {
        return self::JENIS[$this->jenis]['label'] ?? 'Lainnya';
    }

    public function warna(): string
    {
        return self::JENIS[$this->jenis]['warna'] ?? '#64748b';
    }

    public function ikon(): string
    {
        return self::JENIS[$this->jenis]['ikon'] ?? 'dot';
    }

    /**
     * Rentang waktu siap tampil.
     *
     * Kegiatan seharian tidak menyebut jam sama sekali — menuliskan "00:00"
     * membuat orang mengira acaranya dimulai tengah malam.
     */
    public function rentangWaktu(): string
    {
        if ($this->seharian) {
            return 'Seharian';
        }

        $mulai = $this->mulai->translatedFormat('H:i');

        if (! $this->selesai) {
            return $mulai;
        }

        return $this->selesai->isSameDay($this->mulai)
            ? $mulai.' – '.$this->selesai->translatedFormat('H:i')
            : $mulai.' – '.$this->selesai->translatedFormat('d M, H:i');
    }

    /** Sudah lewat? Dipakai untuk meredupkan kegiatan lampau. */
    public function sudahLewat(): bool
    {
        return ($this->selesai ?: $this->mulai)->isPast();
    }
}
