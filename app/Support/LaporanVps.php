<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Jembatan antara VPS bot Turnitin dan agen Telegram.
 *
 * VPS TIDAK membuka port apa pun. Ia yang menelepon Phoenix (POST /lapor) tiap
 * detak, menitipkan keadaan mesinnya, lalu membawa pulang perintah yang
 * menunggu — kalau ada. Dengan begitu tidak ada pintu masuk baru ke server itu,
 * dan tidak ada kredensial yang berkeliaran di internet selain token bot yang
 * sudah dipakai.
 */
class LaporanVps
{
    private const KUNCI = 'vps_bot_laporan';

    private const KUNCI_PERINTAH = 'vps_bot_perintah';

    /** Perintah yang boleh dititipkan lewat Telegram. */
    public const PERINTAH = ['jeda', 'lanjut', 'restart'];

    /** Laporan dianggap basi sesudah ini — VPS berdetak tiap menit. */
    public const BASI_MENIT = 10;

    public static function simpan(array $data): void
    {
        Cache::put(self::KUNCI, $data + ['diterima_at' => now()->toIso8601String()], now()->addDay());
    }

    public static function terakhir(): ?array
    {
        $data = Cache::get(self::KUNCI);

        return is_array($data) ? $data : null;
    }

    public static function umurMenit(): ?int
    {
        $data = self::terakhir();

        if (! $data || empty($data['diterima_at'])) {
            return null;
        }

        return (int) Carbon::parse($data['diterima_at'])->diffInMinutes(now());
    }

    public static function basi(): bool
    {
        $umur = self::umurMenit();

        return $umur === null || $umur > self::BASI_MENIT;
    }

    /** Titipkan satu perintah; diambil VPS pada detak berikutnya (≤1 menit). */
    public static function titipPerintah(string $perintah): bool
    {
        if (! in_array($perintah, self::PERINTAH, true)) {
            return false;
        }

        Cache::put(self::KUNCI_PERINTAH, ['perintah' => $perintah, 'pada' => now()->toIso8601String()], now()->addMinutes(30));

        return true;
    }

    /** Ambil sekali pakai: perintah yang sama tidak boleh dijalankan dua kali. */
    public static function ambilPerintah(): ?string
    {
        $data = Cache::pull(self::KUNCI_PERINTAH);

        return is_array($data) ? ($data['perintah'] ?? null) : null;
    }

    public static function perintahMenunggu(): ?string
    {
        $data = Cache::get(self::KUNCI_PERINTAH);

        return is_array($data) ? ($data['perintah'] ?? null) : null;
    }
}
