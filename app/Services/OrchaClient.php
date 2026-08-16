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
     * Mengambil berkas mentah (PDF) dari Orcha, bukan JSON.
     *
     * Dipakai untuk kwitansi: berkasnya dibuat di Orcha supaya sama persis
     * dengan yang diterima pelanggan, lalu diteruskan apa adanya oleh lemon.
     *
     * @return array{isi: string, nama: string}
     *
     * @throws OrchaTidakTerjangkau
     */
    public function berkas(string $jalur, array $parameter = []): array
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

        // Nama berkasnya ikut apa kata Orcha; kalau tidak disebut, dibuatkan
        // nama sederhana supaya unduhannya tetap punya nama yang masuk akal.
        $nama = 'berkas-orcha.pdf';

        if (preg_match('/filename="?([^"]+)"?/', (string) $balasan->header('Content-Disposition'), $cocok)) {
            $nama = $cocok[1];
        }

        return ['isi' => $balasan->body(), 'nama' => $nama];
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

        return $this->bacaBalasan($balasan);
    }

    /**
     * Terjemahkan balasan jadi larik, atau lemparkan pesan yang layak dibaca.
     *
     * @throws OrchaTidakTerjangkau
     */
    private function bacaBalasan(\Illuminate\Http\Client\Response $balasan): array
    {
        if ($balasan->status() === 422) {
            // Pesan galat per kolom dari Orcha sudah berbahasa Indonesia,
            // jadi cukup diteruskan apa adanya ke admin.
            $pesan = collect($balasan->json('errors', []))->flatten()->first()
                ?: $balasan->json('pesan');

            throw new OrchaTidakTerjangkau($pesan ?: 'Isian ditolak oleh Orcha.');
        }

        if ($balasan->failed()) {
            throw new OrchaTidakTerjangkau($this->pesanGagal($balasan->status()));
        }

        return $balasan->json() ?? [];
    }

    /**
     * Kirim data baru atau perubahan, lengkap dengan gambar bila ada.
     *
     * Gambar diteruskan sebagai multipart ke Orcha — berkasnya disimpan di
     * server Orcha, bukan di lemon, supaya website Orcha tetap bisa
     * menampilkannya tanpa bergantung aplikasi ini.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $gambar
     *
     * @throws OrchaTidakTerjangkau
     */
    public function kirim(string $jalur, array $data = [], $gambar = null): array
    {
        if (! $this->siap()) {
            throw new OrchaTidakTerjangkau('Sambungan ke Orcha belum disetel.');
        }

        try {
            $permintaan = $this->permintaan();

            if ($gambar) {
                $permintaan = $permintaan->attach(
                    'gambar',
                    file_get_contents($gambar->getRealPath()),
                    $gambar->getClientOriginalName()
                );
            }

            // Nilai larik dan boolean harus diratakan dulu: multipart hanya
            // mengenal pasangan nama-nilai berupa teks.
            $balasan = $permintaan->post($jalur, $gambar ? $this->ratakan($data) : $data);
        } catch (\Throwable $e) {
            throw new OrchaTidakTerjangkau('Server Orcha tidak bisa dihubungi. Data belum tersimpan.');
        }

        return $this->bacaBalasan($balasan);
    }

    /**
     * @throws OrchaTidakTerjangkau
     */
    public function hapus(string $jalur): array
    {
        if (! $this->siap()) {
            throw new OrchaTidakTerjangkau('Sambungan ke Orcha belum disetel.');
        }

        try {
            $balasan = $this->permintaan()->delete($jalur);
        } catch (\Throwable $e) {
            throw new OrchaTidakTerjangkau('Server Orcha tidak bisa dihubungi. Data belum dihapus.');
        }

        return $this->bacaBalasan($balasan);
    }

    /**
     * Ubah larik bersarang jadi pasangan bergaya `fasilitas[0]`, dan boolean
     * jadi 1/0 — bentuk yang dipahami multipart.
     */
    private function ratakan(array $data, string $awalan = ''): array
    {
        $hasil = [];

        foreach ($data as $kunci => $nilai) {
            $nama = $awalan === '' ? (string) $kunci : "{$awalan}[{$kunci}]";

            if (is_array($nilai)) {
                $hasil += $this->ratakan($nilai, $nama);
            } elseif (is_bool($nilai)) {
                $hasil[$nama] = $nilai ? '1' : '0';
            } elseif ($nilai !== null) {
                $hasil[$nama] = (string) $nilai;
            }
        }

        return $hasil;
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
