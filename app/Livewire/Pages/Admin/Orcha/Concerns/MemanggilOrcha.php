<?php

namespace App\Livewire\Pages\Admin\Orcha\Concerns;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Services\OrchaClient;

/**
 * Bagian yang dipakai bersama semua halaman Orcha di lemon.
 *
 * Datanya datang dari API, bukan dari basis data lemon, jadi paginasinya juga
 * ikut apa kata Orcha (meta.halaman, meta.halaman_terakhir) — bukan
 * WithPagination bawaan Livewire.
 */
trait MemanggilOrcha
{
    /** Pesan gagal yang layak dibaca admin; kosong berarti aman. */
    public string $galat = '';

    public string $cari = '';

    public string $filterStatus = '';

    public int $halaman = 1;

    public function updatedCari(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->halaman = 1;
    }

    public function keHalaman(int $nomor): void
    {
        $this->halaman = max(1, $nomor);
    }

    protected function orcha(): OrchaClient
    {
        return app(OrchaClient::class);
    }

    /**
     * Ambil data; kalau gagal, kembalikan bentuk kosong supaya halaman tetap
     * tergambar rapi dengan pesan galat, bukan layar error.
     */
    protected function muat(string $jalur, array $parameter = []): array
    {
        $this->galat = '';

        try {
            return $this->orcha()->ambil($jalur, $parameter);
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();

            return ['data' => [], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 0]];
        }
    }

    /**
     * Parameter daftar yang seragam: cari, status, halaman.
     */
    /**
     * Baris per halaman untuk daftar.
     *
     * Metode, bukan properti: properti trait yang ditimpa kelas pemakainya
     * dengan nilai berbeda adalah galat fatal di PHP. Metode boleh ditimpa
     * begitu saja.
     *
     * Ditimpa oleh daftar berbentuk kartu bergrid, yang perlu angka habis dibagi
     * jumlah kolomnya supaya baris terakhirnya tidak menyisakan satu kartu
     * menggantung sendirian.
     */
    protected function perHalaman(): int
    {
        return 10;
    }

    protected function parameterDaftar(array $tambahan = []): array
    {
        return array_filter(array_merge([
            'cari' => $this->cari,
            'status' => $this->filterStatus,
            'page' => $this->halaman,
            'per_halaman' => $this->perHalaman(),
        ], $tambahan), fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    /**
     * Daftar pilihan status dari Orcha, supaya lemon tidak menyalin-tempel.
     * Disimpan sebentar karena isinya jarang berubah.
     */
    protected function rujukan(string $kunci): array
    {
        $semua = cache()->remember('orcha.rujukan', now()->addMinutes(10), function () {
            try {
                return $this->orcha()->ambil('/rujukan')['data'] ?? [];
            } catch (OrchaTidakTerjangkau) {
                return [];
            }
        });

        return $semua[$kunci] ?? [];
    }

    /**
     * Ubah data di Orcha lalu tampilkan hasilnya lewat SweetAlert yang sama
     * dengan halaman lemon lain.
     */
    protected function kirimPerubahan(string $jalur, array $data, string $pesanSukses): void
    {
        try {
            $this->orcha()->ubah($jalur, $data);
            cache()->forget('orcha.perlu-ditindak');
            $this->dispatch('order-updated', message: $pesanSukses);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Simpan data baru atau perubahan (boleh dengan gambar).
     *
     * Mengembalikan true bila tersimpan, supaya pemanggilnya tahu kapan boleh
     * menutup formulir atau berpindah halaman.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $gambar
     */
    /**
     * Simpan data baru atau perubahan (boleh dengan gambar).
     *
     * Bila $tujuan diisi, pemberitahuan sukses ditampilkan DULU di halaman
     * ini, baru berpindah setelah popupnya menutup. Sebelumnya urutannya
     * terbalik — berpindah dulu lalu menitipkan pesan lewat sesi — dan
     * popupnya ikut terbuang saat isi halaman ditukar, jadi hanya sempat
     * terlihat sekejap tanpa teks.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $gambar
     */
    protected function kirimData(string $jalur, array $data, string $pesanSukses, $gambar = null, ?string $tujuan = null): bool
    {
        try {
            $this->orcha()->kirim($jalur, $data, $gambar);

            $tujuan
                ? $this->dispatch('orcha-sukses-pindah', message: $pesanSukses, url: $tujuan)
                : $this->dispatch('order-updated', message: $pesanSukses);

            return true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return false;
        }
    }

    protected function hapusData(string $jalur, string $pesanSukses): bool
    {
        try {
            $this->orcha()->hapus($jalur);
            $this->dispatch('order-updated', message: $pesanSukses);

            return true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return false;
        }
    }
}
