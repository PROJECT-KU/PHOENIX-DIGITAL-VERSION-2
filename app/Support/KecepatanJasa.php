<?php

namespace App\Support;

use App\Models\OrderUpload;
use Carbon\Carbon;

/**
 * Seberapa cepat pekerjaan jasa selesai.
 *
 * Untuk penjual barang, mutu layanan terlihat dari barangnya. Untuk penjual
 * JASA, mutunya adalah waktu — dan waktu itu sudah lama tersimpan
 * (created_at saat naskah masuk, selesai_at saat hasilnya siap) tanpa pernah
 * dijumlahkan. Tanpa angka ini, pekerjaan yang makin lambat baru ketahuan
 * lewat keluhan.
 *
 * Dihitung di PHP, bukan lewat TIMESTAMPDIFF: fungsi itu milik MySQL,
 * sedangkan pengujian berjalan di SQLite. Jumlah barisnya ratusan, bukan
 * jutaan, jadi tidak ada yang dikorbankan.
 */
class KecepatanJasa
{
    /** Batas wajar satu pengecekan selesai. */
    public const AMBANG_JAM = 24;

    /**
     * @return array{selesai:int, rata_jam:?float, lewat_sehari:int, terlama_jam:?float, tepat_persen:?float}
     */
    public static function periode(Carbon $mulai, Carbon $akhirEksklusif): array
    {
        $selesai = OrderUpload::whereNotNull('selesai_at')
            ->where('selesai_at', '>=', $mulai)
            ->where('selesai_at', '<', $akhirEksklusif)
            ->get(['created_at', 'selesai_at']);

        $jam = $selesai
            ->map(fn ($u) => $u->created_at && $u->selesai_at
                ? max(Carbon::parse($u->created_at)->diffInMinutes(Carbon::parse($u->selesai_at)) / 60, 0)
                : null)
            ->filter(fn ($j) => $j !== null)
            ->values();

        if ($jam->isEmpty()) {
            return ['selesai' => 0, 'rata_jam' => null, 'lewat_sehari' => 0, 'terlama_jam' => null, 'tepat_persen' => null];
        }

        $lewat = $jam->filter(fn ($j) => $j > self::AMBANG_JAM)->count();

        return [
            'selesai' => $jam->count(),
            'rata_jam' => round($jam->avg(), 1),
            'lewat_sehari' => $lewat,
            'terlama_jam' => round($jam->max(), 1),
            'tepat_persen' => round((($jam->count() - $lewat) / $jam->count()) * 100, 1),
        ];
    }

    /** "3,5 jam" / "2,1 hari" — jam mentah tidak terbaca setelah lewat sehari. */
    public static function labelJam(?float $jam): string
    {
        if ($jam === null) {
            return '—';
        }

        if ($jam < 1) {
            return max((int) round($jam * 60), 1).' menit';
        }

        if ($jam < 48) {
            return number_format($jam, 1, ',', '.').' jam';
        }

        return number_format($jam / 24, 1, ',', '.').' hari';
    }
}
