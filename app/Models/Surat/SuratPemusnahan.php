<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Berita acara pemusnahan arsip.
 *
 * Masa simpan dicatat justru supaya suatu hari arsipnya bisa dimusnahkan
 * dengan SAH. Tanpa berita acaranya, penandaan retensi berhenti sebagai
 * peringatan yang tidak pernah bisa ditutup — dan daftar "masa simpan habis"
 * tumbuh terus sampai tidak ada yang membacanya lagi.
 *
 * Suratnya sendiri TIDAK ikut terhapus. Yang dimusnahkan adalah berkas
 * KERTASNYA; catatan di buku agenda justru harus tetap ada, karena berita
 * acara ini menunjuk padanya.
 */
class SuratPemusnahan extends Model
{
    protected $table = 'surat_pemusnahan';

    protected $fillable = ['nomor', 'tanggal', 'penanggung_jawab', 'catatan', 'dibuat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (self $ba) {
            if (blank($ba->nomor)) {
                $ba->nomor = self::nomorBaru($ba->tanggal);
            }

            if (blank($ba->dibuat_oleh) && auth()->check()) {
                $ba->dibuat_oleh = auth()->id();
            }
        });
    }

    /**
     * BA-2026-001, berurut per tahun.
     *
     * Dihitung dari nomor TERBESAR, bukan dari jumlah baris: berita acara
     * yang dibatalkan tetap memegang nomornya.
     */
    public static function nomorBaru($tanggal = null): string
    {
        $tahun = $tanggal ? (int) \Illuminate\Support\Carbon::parse($tanggal)->format('Y') : (int) now()->format('Y');
        $pola = "BA-{$tahun}-";

        $terakhir = static::where('nomor', 'like', $pola.'%')->orderByDesc('nomor')->value('nomor');
        $urut = $terakhir ? ((int) substr((string) $terakhir, strlen($pola))) + 1 : 1;

        return $pola.str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'pemusnahan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function getTanggalTeksAttribute(): string
    {
        return $this->tanggal?->locale('id')->translatedFormat('d F Y') ?? '-';
    }
}
