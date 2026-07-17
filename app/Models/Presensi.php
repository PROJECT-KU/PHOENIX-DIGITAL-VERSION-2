<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'tanggal',
        'tipe',
        'waktu_masuk',
        'lat_masuk',
        'lng_masuk',
        'jarak_masuk_meter',
        'waktu_pulang',
        'lat_pulang',
        'lng_pulang',
        'jarak_pulang_meter',
        'durasi_menit',
        'status',
        'catatan',
        'is_manual',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_pulang' => 'datetime',
        'lat_masuk' => 'decimal:7',
        'lng_masuk' => 'decimal:7',
        'lat_pulang' => 'decimal:7',
        'lng_pulang' => 'decimal:7',
        'is_manual' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Scoping baris: admin (view_all_presensi) melihat semua, selain itu hanya miliknya.
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canViewAll('presensi')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /* ===== Helper tampilan ===== */

    public function getIsSelesaiAttribute(): bool
    {
        return $this->waktu_pulang !== null;
    }

    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'hadir_offline' => 'Hadir (Offline)',
            'hadir_online' => 'Hadir (Online)',
            'lembur' => 'Lembur',
            default => ucfirst((string) $this->tipe),
        };
    }

    public function getDurasiLabelAttribute(): string
    {
        if (! $this->durasi_menit) {
            return '-';
        }
        $j = intdiv($this->durasi_menit, 60);
        $m = $this->durasi_menit % 60;

        return $j.' jam'.($m ? ' '.$m.' mnt' : '');
    }

    /**
     * Rekap presensi seorang karyawan pada rentang tanggal — dipakai untuk perhitungan gaji.
     *
     * @return array{hari_hadir:int, hari_offline:int, hari_online:int, menit_kerja:int, menit_lembur:int, jam_kerja:float, jam_lembur:float}
     */
    public static function rekapBulan($userId, int $bulan, int $tahun): array
    {
        $start = \Illuminate\Support\Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return static::rekapPeriode($userId, $start->toDateString(), $end->toDateString());
    }

    /**
     * Rekap presensi mengikuti PERIODE GAJI (bukan bulan kalender).
     * Mis. periode Juli 2026 = 21 Juni 2026 s/d 20 Juli 2026.
     */
    public static function rekapPeriodeGaji($userId, int $bulan, int $tahun): array
    {
        [$start, $end] = \App\Support\PeriodeGaji::range($bulan, $tahun);

        return static::rekapPeriode($userId, $start->toDateString(), $end->toDateString());
    }

    public static function rekapPeriode($userId, $start, $end): array
    {
        $rows = static::where('user_id', $userId)
            ->whereBetween('tanggal', [$start, $end])
            ->whereNotNull('waktu_pulang')
            ->get();

        $hadir = $rows->whereIn('tipe', ['hadir_offline', 'hadir_online']);
        $lembur = $rows->where('tipe', 'lembur');

        $menitKerja = (int) $hadir->sum('durasi_menit');
        $menitLembur = (int) $lembur->sum('durasi_menit');

        return [
            'hari_hadir' => $hadir->count(),
            'hari_offline' => $hadir->where('tipe', 'hadir_offline')->count(),
            'hari_online' => $hadir->where('tipe', 'hadir_online')->count(),
            'menit_kerja' => $menitKerja,
            'menit_lembur' => $menitLembur,
            'jam_kerja' => round($menitKerja / 60, 2),
            'jam_lembur' => round($menitLembur / 60, 2),
        ];
    }
}
