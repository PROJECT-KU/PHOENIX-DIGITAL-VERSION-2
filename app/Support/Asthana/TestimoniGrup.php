<?php

namespace App\Support\Asthana;

use App\Models\Asthana\Testimoni;
use App\Services\OrchaClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Testimoni di beranda situs Asthana = testimoni Asthana sendiri + anak
 * perusahaan.
 *
 * - Asthana : tabel asthana_testimoni (lemon → mode Asthana → Testimoni).
 * - Orcha   : testimoni BERSTATUS 'tayang' di aplikasi Orcha, diambil lewat
 *             API (lemon → Orcha → Testimoni). Disimpan 10 menit, disegarkan
 *             saat admin mengubah testimoni Orcha di lemon.
 * - Reka    : SEGERA HADIR. Reka Technology belum tersambung ke lemon; begitu
 *             tersambung, isi reka() dengan pengambilnya — situs tidak perlu
 *             diubah karena bentuk datanya sama.
 *
 * Urutannya bergiliran antar-sumber (Asthana, Orcha, Reka, Asthana, …) supaya
 * enam kartu di beranda tidak dimonopoli satu perusahaan.
 */
class TestimoniGrup
{
    public const SUMBER = [
        'asthana' => ['nama' => 'PT. Asthana Cipta Mandiri', 'terhubung' => true],
        'orcha' => ['nama' => 'Orcha Journey', 'terhubung' => true],
        'reka' => ['nama' => 'Reka Technology', 'terhubung' => false], // segera hadir
    ];

    private const KUNCI_ORCHA = 'asthana.testimoni-orcha';

    private const MAKS = 9;

    /** @return Collection<int, array> bentuk kartu situs */
    public static function semua(): Collection
    {
        $per = [
            self::asthana(),
            self::orcha(),
            self::reka(),
        ];

        $hasil = collect();
        for ($i = 0; $hasil->count() < self::MAKS; $i++) {
            $adaSisa = false;
            foreach ($per as $daftar) {
                if (isset($daftar[$i])) {
                    $hasil->push($daftar[$i]);
                    $adaSisa = true;
                }
            }
            if (! $adaSisa) {
                break;
            }
        }

        return $hasil->take(self::MAKS)->values();
    }

    public static function lupakanOrcha(): void
    {
        Cache::forget(self::KUNCI_ORCHA);
    }

    /** @return array<int, array> */
    private static function asthana(): array
    {
        return Testimoni::tampil()->get()->map(fn (Testimoni $t) => [
            'nama' => $t->nama,
            'jabatan' => $t->jabatan,
            'perusahaan' => $t->perusahaan,
            'isi' => $t->isi,
            'bintang' => $t->bintang,
            'foto_url' => Testimoni::urlGambar($t->foto),
            'sumber' => 'asthana',
        ])->all();
    }

    /** @return array<int, array> */
    private static function orcha(): array
    {
        $simpan = Cache::get(self::KUNCI_ORCHA);
        if ($simpan !== null) {
            return $simpan;
        }

        $daftar = self::ambilOrcha();
        // Gagal = simpan sebentar saja (Orcha mati tidak boleh memperlambat
        // setiap kunjungan beranda, tapi harus cepat pulih).
        Cache::put(self::KUNCI_ORCHA, $daftar ?? [], $daftar === null ? 60 : 600);

        return $daftar ?? [];
    }

    /** @return array<int, array>|null null bila Orcha tidak terjangkau */
    private static function ambilOrcha(): ?array
    {
        $orcha = app(OrchaClient::class);
        if (! $orcha->siap()) {
            return [];
        }

        $hasil = [];
        try {
            for ($halaman = 1; $halaman <= 3; $halaman++) {
                $balasan = $orcha->ambil('/testimoni', ['per_halaman' => 100, 'page' => $halaman, 'halaman' => $halaman]);
                foreach ($balasan['data'] ?? [] as $t) {
                    // Yang menunggu persetujuan / ditolak tidak boleh tampil.
                    if (($t['status'] ?? null) !== 'tayang' || ! filled($t['isi'] ?? null)) {
                        continue;
                    }
                    $hasil[] = [
                        'nama' => $t['nama'],
                        'jabatan' => null,
                        'perusahaan' => 'Pelanggan Orcha Journey',
                        'isi' => $t['isi'],
                        'bintang' => max(1, min(5, (int) ($t['rating'] ?? 5))),
                        'foto_url' => self::mutlak($t['foto'] ?? null),
                        'sumber' => 'orcha',
                        'id_orcha' => (int) ($t['id'] ?? 0),
                    ];
                }
                if ($halaman >= (int) ($balasan['meta']['halaman_terakhir'] ?? 1)) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Testimoni Orcha untuk situs Asthana gagal diambil', ['galat' => $e->getMessage()]);

            return null;
        }

        // Bintang tertinggi dulu, lalu yang terbaru.
        usort($hasil, fn ($a, $b) => [$b['bintang'], $b['id_orcha']] <=> [$a['bintang'], $a['id_orcha']]);

        return array_map(function ($t) {
            unset($t['id_orcha']);

            return $t;
        }, $hasil);
    }

    /**
     * Reka Technology — SEGERA HADIR.
     *
     * @return array<int, array>
     */
    private static function reka(): array
    {
        return [];
    }

    private static function mutlak(?string $jalur): ?string
    {
        if (! filled($jalur)) {
            return null;
        }
        if (preg_match('#^https?://#', $jalur)) {
            return $jalur;
        }
        $u = parse_url((string) config('orcha.url'));
        $asal = isset($u['scheme'], $u['host']) ? $u['scheme'].'://'.$u['host'].(isset($u['port']) ? ':'.$u['port'] : '') : '';

        return $asal === '' ? null : $asal.'/'.ltrim($jalur, '/');
    }
}
