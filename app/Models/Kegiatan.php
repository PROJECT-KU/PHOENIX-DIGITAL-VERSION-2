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

    /**
     * Jenis kegiatan beserta warnanya.
     *
     * Tiap jenis punya DUA warna: 'warna' untuk keadaan terpilih dan pita di
     * kalender, 'lembut' untuk latar saat tidak terpilih. Warna lembutnya
     * ditulis tetap, bukan dihitung dari yang pekat, supaya hasilnya bisa
     * dilihat langsung di sini dan tidak berubah diam-diam.
     *
     * Nadanya sengaja disamakan dengan tombol bawaan lemon (ungu, mawar,
     * kuning) agar kalender tidak terasa seperti halaman dari aplikasi lain.
     */
    public const JENIS = [
        'rapat' => ['label' => 'Rapat', 'warna' => '#6366f1', 'lembut' => '#eef0ff', 'ikon' => 'people-fill'],
        'tenggat' => ['label' => 'Tenggat', 'warna' => '#f43f5e', 'lembut' => '#ffeef1', 'ikon' => 'flag-fill'],
        'acara' => ['label' => 'Acara', 'warna' => '#0ea5e9', 'lembut' => '#e8f6fe', 'ikon' => 'stars'],
        'libur' => ['label' => 'Libur', 'warna' => '#10b981', 'lembut' => '#e7f8f2', 'ikon' => 'sun-fill'],
        'lainnya' => ['label' => 'Lainnya', 'warna' => '#94a3b8', 'lembut' => '#f1f5f9', 'ikon' => 'three-dots'],
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
        return self::JENIS[$this->jenis]['warna'] ?? '#94a3b8';
    }

    public function lembut(): string
    {
        return self::JENIS[$this->jenis]['lembut'] ?? '#f1f5f9';
    }

    public function ikon(): string
    {
        return self::JENIS[$this->jenis]['ikon'] ?? 'three-dots';
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
