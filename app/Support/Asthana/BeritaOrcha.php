<?php

namespace App\Support\Asthana;

use App\Models\Asthana\Berita;
use App\Services\OrchaClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Artikel blog Orcha Journey yang ikut tampil sebagai berita di situs Asthana.
 *
 * Artikelnya tinggal di aplikasi Orcha (ditulis admin lewat lemon → Blog
 * Orcha), jadi diambil lewat API Orcha — tidak disalin ke tabel lemon, supaya
 * suntingan, penjadwalan, dan penghapusan di Orcha langsung berlaku tanpa
 * sinkronisasi dua arah.
 *
 * Disimpan 10 menit; disegarkan seketika saat admin menyimpan/menghapus artikel
 * di lemon (lupakan()). Orcha mati = situs tetap jalan dengan berita Asthana.
 * Slug diberi awalan "orcha-" supaya tidak pernah bentrok dengan berita Asthana.
 */
class BeritaOrcha
{
    public const AWALAN = 'orcha-';

    private const KUNCI = 'asthana.berita-orcha';

    /** Semua artikel Orcha yang SUDAH tayang, bentuk kartu, terbaru di atas. */
    public static function semua(): Collection
    {
        $daftar = Cache::get(self::KUNCI);

        if ($daftar === null) {
            $daftar = self::ambilDaftar();
            // Gagal ambil disimpan sebentar saja, supaya situs tidak menunggu
            // Orcha yang mati di setiap kunjungan, tapi cepat pulih.
            Cache::put(self::KUNCI, $daftar ?? [], $daftar === null ? 60 : 600);
            $daftar ??= [];
        }

        // Disaring SAAT DIBACA: artikel terjadwal muncul tepat waktunya
        // walaupun daftarnya diambil sebelum jam tayang.
        return collect($daftar)
            ->filter(fn ($a) => $a['terbit_at'] && Carbon::parse($a['terbit_at'])->lte(now()))
            ->sortByDesc('terbit_at')
            ->values();
    }

    /** Detail satu artikel (slug berawalan "orcha-"), atau null. */
    public static function detail(string $slug): ?array
    {
        $kartu = self::semua()->firstWhere('slug', $slug);
        if (! $kartu) {
            return null;
        }

        $isi = Cache::remember(self::KUNCI.'.isi.'.$kartu['id_orcha'], 600, function () use ($kartu) {
            try {
                $data = app(OrchaClient::class)->ambil('/artikel/'.$kartu['id_orcha'])['data'] ?? [];
            } catch (\Throwable $e) {
                return null;
            }

            return self::mutlakkanIsi((string) ($data['isi'] ?? ''));
        });

        return $kartu + ['isi' => $isi];
    }

    /**
     * Berita gabungan Asthana + Orcha, terbaru di atas.
     *
     * @return Collection<int, array>
     */
    public static function gabungan(): Collection
    {
        $asthana = Berita::terbit()->get()->map(fn (Berita $b) => $b->keKartu() + ['sumber' => 'asthana']);

        return $asthana->concat(self::semua())
            ->sortByDesc(fn ($b) => $b['terbit_at'] ?? '')
            ->values();
    }

    public static function lupakan(): void
    {
        Cache::forget(self::KUNCI);
    }

    public static function milikOrcha(string $slug): bool
    {
        return str_starts_with($slug, self::AWALAN);
    }

    /** @return array<int, array>|null null bila Orcha tidak terjangkau */
    private static function ambilDaftar(): ?array
    {
        $orcha = app(OrchaClient::class);
        if (! $orcha->siap()) {
            return [];
        }

        $hasil = [];
        try {
            for ($halaman = 1; $halaman <= 5; $halaman++) {
                $balasan = $orcha->ambil('/artikel', ['status' => 'tayang', 'per_halaman' => 100, 'halaman' => $halaman, 'page' => $halaman]);
                foreach ($balasan['data'] ?? [] as $a) {
                    $hasil[] = self::keKartu($a);
                }
                if ($halaman >= (int) ($balasan['meta']['halaman_terakhir'] ?? 1)) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Berita Orcha untuk situs Asthana gagal diambil', ['galat' => $e->getMessage()]);

            return null;
        }

        return $hasil;
    }

    private static function keKartu(array $a): array
    {
        return [
            'slug' => self::AWALAN.$a['slug'],
            'judul' => $a['judul'],
            // Lencana kartu: menandai asal artikel.
            'kategori' => 'Orcha Journey',
            'ringkasan' => $a['ringkasan_tampil'] ?? null,
            'sampul_url' => self::mutlak($a['sampul'] ?? null),
            'terbit_at' => $a['terbit_pada'] ?? null,
            'sumber' => 'orcha',
            'tautan_asli' => $a['tautan'] ?? null,
            'id_orcha' => $a['id'],
        ];
    }

    /** Asal situs Orcha (skema + host) dari ORCHA_API_URL. */
    private static function asal(): string
    {
        $u = parse_url((string) config('orcha.url'));

        return isset($u['scheme'], $u['host'])
            ? $u['scheme'].'://'.$u['host'].(isset($u['port']) ? ':'.$u['port'] : '')
            : '';
    }

    /** Gambar Orcha disimpan sebagai "/storage/…" — situs Asthana butuh alamat lengkap. */
    private static function mutlak(?string $jalur): ?string
    {
        if (! filled($jalur)) {
            return null;
        }

        return preg_match('#^https?://#', $jalur) ? $jalur : self::asal().'/'.ltrim($jalur, '/');
    }

    private static function mutlakkanIsi(string $isi): string
    {
        $asal = self::asal();

        return $asal === '' ? $isi : preg_replace('#(src|href)="/(?!/)#', '$1="'.$asal.'/', $isi);
    }
}
