<?php

namespace App\Services;

use App\Exceptions\OrchaTidakTerjangkau;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Pemanggil API Orcha Journey.
 *
 * Admin lemon tidak punya akun Orcha. Yang dipercaya Orcha adalah kunci rahasia
 * antar server; siapa admin yang sedang bertindak ikut dikirim sebagai jejak
 * audit saja. Penentu boleh-tidaknya tetap permission di lemon.
 *
 * Semua kegagalan dibungkus jadi OrchaTidakTerjangkau supaya halaman bisa
 * menampilkan pesan yang jelas, bukan layar kosong yang menyesatkan.
 */
class OrchaClient
{
    public function siap(): bool
    {
        return filled(config('orcha.url')) && filled(config('orcha.kunci'));
    }

    private function permintaan(): PendingRequest
    {
        return Http::withHeaders([
            'X-Orcha-Key' => config('orcha.kunci'),
            'X-Orcha-Admin' => auth()->user()?->email ?? '-',
            'Accept' => 'application/json',
        ])
            ->timeout(config('orcha.timeout'))
            ->baseUrl(config('orcha.url'));
    }

    /**
     * @throws OrchaTidakTerjangkau
     */
    public function ambil(string $jalur, array $parameter = []): array
    {
        if (! $this->siap()) {
            throw new OrchaTidakTerjangkau('Sambungan ke Orcha belum disetel. Isi ORCHA_API_URL dan ORCHA_API_KEY di .env.');
        }

        try {
            $balasan = $this->permintaan()->get($jalur, $parameter);
        } catch (\Throwable $e) {
            throw new OrchaTidakTerjangkau('Server Orcha tidak bisa dihubungi. Coba lagi beberapa saat lagi.');
        }

        if ($balasan->failed()) {
            throw new OrchaTidakTerjangkau($this->pesanGagal($balasan->status()));
        }

        return $balasan->json() ?? [];
    }

    /**
     * @throws OrchaTidakTerjangkau
     */
    public function ubah(string $jalur, array $data = []): array
    {
        if (! $this->siap()) {
            throw new OrchaTidakTerjangkau('Sambungan ke Orcha belum disetel.');
        }

        try {
            $balasan = $this->permintaan()->patch($jalur, $data);
        } catch (\Throwable $e) {
            throw new OrchaTidakTerjangkau('Server Orcha tidak bisa dihubungi. Perubahan belum tersimpan.');
        }

        if ($balasan->status() === 422) {
            $pesan = collect($balasan->json('errors', []))->flatten()->first();

            throw new OrchaTidakTerjangkau($pesan ?: 'Isian ditolak oleh Orcha.');
        }

        if ($balasan->failed()) {
            throw new OrchaTidakTerjangkau($this->pesanGagal($balasan->status()));
        }

        return $balasan->json() ?? [];
    }

    /**
     * Ringkasan untuk badge sidebar.
     *
     * Disimpan sebentar dan dibungkus rescue: sidebar tampil di setiap halaman
     * lemon, jadi Orcha yang sedang mati tidak boleh ikut mematikan lemon.
     */
    public function perluDitindak(): int
    {
        if (! $this->siap()) {
            return 0;
        }

        return Cache::remember('orcha.perlu-ditindak', now()->addMinute(), function () {
            return rescue(function () {
                $angka = $this->ambil('/dashboard')['data']['perlu_ditindak'] ?? [];

                return (int) array_sum($angka);
            }, 0, false);
        });
    }

    private function pesanGagal(int $kode): string
    {
        return match ($kode) {
            401 => 'Kunci API Orcha ditolak. Pastikan ORCHA_API_KEY sama persis di kedua aplikasi.',
            403 => 'IP server ini belum diizinkan oleh Orcha.',
            404 => 'Data yang diminta tidak ada di Orcha.',
            429 => 'Terlalu banyak permintaan ke Orcha. Tunggu sebentar.',
            503 => 'Orcha belum menyalakan API-nya (ORCHA_API_KEY di sisi Orcha masih kosong).',
            default => "Orcha membalas dengan kode {$kode}.",
        };
    }
}
