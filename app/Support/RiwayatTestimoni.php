<?php

namespace App\Support;

use App\Models\Testimoni;
use App\Models\TestimoniRiwayat;
use Illuminate\Support\Collection;

/**
 * Pencatat jejak moderasi testimoni.
 *
 * Fail-safe: mencatat riwayat TIDAK BOLEH menggagalkan moderasi. Kalau tabelnya
 * belum ter-migrasi di server, moderasi tetap jalan dan hanya jejaknya yang
 * hilang — bukan sebaliknya.
 */
class RiwayatTestimoni
{
    public static function catat(Testimoni $testimoni, string $aksi, ?string $keterangan = null): void
    {
        rescue(fn () => TestimoniRiwayat::create([
            'testimoni_id' => $testimoni->getKey(),
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'keterangan' => $keterangan,
            'created_at' => now(),
        ]), report: false);
    }

    /** Jejak satu testimoni, terbaru dulu. */
    public static function untuk(Testimoni $testimoni): Collection
    {
        return rescue(fn () => TestimoniRiwayat::with('user')
            ->where('testimoni_id', $testimoni->getKey())
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get(), collect(), report: false);
    }
}
