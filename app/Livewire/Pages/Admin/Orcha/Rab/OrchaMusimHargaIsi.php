<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mengisi tarif satu musim untuk BANYAK harga sekaligus.
 *
 * Layar yang menentukan apakah fitur musim dipakai atau tidak. Tarif musim
 * bisa diisi satu-satu dari borang Master Harga, tetapi 40 tarif Nataru
 * berarti 40 kali buka-isi-simpan — pekerjaan yang karena itu dikerjakan
 * separuh, dan separuh sisanya diam-diam memakai tarif hari biasa sepanjang
 * musim paling mahal dalam setahun.
 *
 * Yang kosong TIDAK disimpan sebagai nol: nol adalah harga yang sah, dan
 * satu-satunya cara mengatakan "harga ini tidak naik di Nataru" adalah tidak
 * adanya barisnya. Mengosongkan kotak yang sudah terisi menghapus tarifnya.
 */
class OrchaMusimHargaIsi extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    public int $musimId;

    #[Url(as: 'kategori', except: '')]
    public string $filterKategori = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    /**
     * Angka yang sedang diketik, per master_harga_id.
     *
     * Dipegang di komponen, bukan dikirim per kotak: admin mengisi dua puluh
     * baris lalu menekan simpan sekali. Menyimpan tiap ketikan berarti dua
     * puluh perjalanan ke Orcha dan dua puluh kesempatan setengah tersimpan.
     *
     * @var array<int, string>
     */
    public array $nilai = [];

    /** Sudah pernah dimuat? Supaya $nilai tidak ditimpa tiap render. */
    public bool $terisi = false;

    public function mount(int $musim): void
    {
        $this->musimId = $musim;
    }

    public function updatedFilterKategori(): void
    {
        $this->muatUlangNilai();
    }

    public function updatedFilterProvinsi(): void
    {
        $this->muatUlangNilai();
    }

    public function updatedCari(): void
    {
        $this->halaman = 1;
        $this->muatUlangNilai();
    }

    /**
     * Saringan diganti: angka yang BELUM disimpan tetap dipegang.
     *
     * Menghapusnya terasa "bersih" tetapi berarti admin yang mengetik lima
     * belas angka lalu menyaring untuk mencari yang keenam belas kehilangan
     * semuanya — tanpa peringatan, karena menyaring bukan tindakan yang
     * terasa berbahaya.
     */
    private function muatUlangNilai(): void
    {
        $this->terisi = false;
    }

    /**
     * Apakah ketikan ini BERISI angka — bukan sekadar bukan-kosong.
     *
     * Pembeda antara "hapus tarifnya" dan "tarifnya nol", dan keduanya
     * berbeda arti: nol adalah harga yang sah, ada tempat yang memang
     * digratiskan di musim tertentu. Memakai `(int) $teks` untuk
     * membedakannya mustahil — "Rp " dan "0" sama-sama jadi 0.
     */
    private function adaAngka($teks): bool
    {
        return preg_match('/\d/', (string) $teks) === 1;
    }

    /**
     * Bertitik untuk dibaca, dan nol TETAP tertulis.
     *
     * keRupiah() milik IsianRupiah memulangkan '' untuk nol — benar di borang
     * lain, salah di sini: kotak yang mengosongkan dirinya sendiri akan
     * menghapus tarif nol yang baru saja diketik, pada penyimpanan berikutnya.
     */
    private function rupiah($teks): string
    {
        return $this->adaAngka($teks) ? number_format($this->angkaDari((string) $teks), 0, ',', '.') : '';
    }

    /**
     * Angkanya ditampilkan bertitik begitu kotaknya ditinggalkan.
     *
     * Satu penangan untuk SELURUH kotak: Livewire memanggil updated() dengan
     * nama medannya, dan menulis satu metode per baris mustahil.
     *
     * Bukan kerapian: kolom di sebelahnya menulis "Rp 450.000", dan angka
     * telanjang 900000 di sebelahnya adalah satu nol yang terlewat baca —
     * jenis salah baca yang sudah pernah memakan selisih jutaan rupiah.
     */
    public function updatedNilai($nilai, $kunci): void
    {
        if ($kunci === null) {
            return;
        }

        $this->nilai[$kunci] = $this->rupiah($nilai);
    }

    public function simpan(): void
    {
        $baris = [];

        foreach ($this->nilai as $id => $angka) {
            $baris[] = [
                'master_harga_id' => (int) $id,
                // Kosong = hapus tarifnya. Nol = harga nol, yang sah.
                'harga' => $this->adaAngka($angka) ? $this->angkaDari((string) $angka) : null,
            ];
        }

        if ($baris === []) {
            $this->dispatch('toast-error', message: 'Belum ada yang diisi.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/musim-harga/{$this->musimId}/pasang", ['baris' => $baris]);

            $this->terisi = false;
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Tarif musim disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Mengisi seluruh baris yang TERLIHAT dengan kenaikan persen.
     *
     * Vendor menyebut angka, bukan persen — karena itu hasilnya ditulis ke
     * kotaknya masing-masing dan masih bisa disunting satu per satu, bukan
     * disimpan sebagai aturan. Yang dipakai sebagai titik mulai adalah harga
     * HARI BIASA, dan layar mengatakannya: dihitung dari tarif akhir pekan,
     * kenaikan 100% akan jadi dua kali angka yang berbeda-beda.
     */
    public function isiPersen(int $persen, array $calon): void
    {
        foreach ($calon as $baris) {
            $dasar = (int) ($baris['harga'] ?? 0);

            if ($dasar <= 0) {
                continue;
            }

            // Dibulatkan ke ribuan ke atas: angka seperti Rp 787.500 terbaca
            // sebagai hasil kalkulator, bukan harga yang pernah dinegosiasi.
            $this->nilai[(int) $baris['id']] = $this->rupiah(
                (int) ceil($dasar * (100 + $persen) / 100 / 1000) * 1000,
            );
        }

        $this->terisi = true;
    }

    public function kosongkanSemua(array $calon): void
    {
        foreach ($calon as $baris) {
            $this->nilai[(int) $baris['id']] = '';
        }

        $this->terisi = true;
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterKategori = '';
        $this->filterProvinsi = '';
        $this->halaman = 1;
        $this->terisi = false;
    }

    public function adaSaringan(): bool
    {
        return $this->filterKategori !== '' || $this->filterProvinsi !== '' || $this->cari !== '';
    }

    public function render()
    {
        try {
            $musim = $this->orcha()->ambil("/musim-harga/{$this->musimId}/isi")['data']['musim'] ?? null;
        } catch (OrchaTidakTerjangkau $e) {
            $musim = null;
            $this->galat = $e->getMessage();
        }

        $calon = $this->muat("/musim-harga/{$this->musimId}/calon", array_filter([
            'kategori' => $this->filterKategori,
            'provinsi' => $this->filterProvinsi,
            'cari' => $this->cari,
        ], fn ($n) => $n !== ''))['data'] ?? [];

        /*
         | Angka yang sudah tersimpan dimuat SEKALI, bukan tiap render.
         |
         | Tiap render menimpanya berarti angka yang baru diketik hilang pada
         | render berikutnya — dan render berikutnya datang dari mengetik di
         | kotak lain. Borang yang menghapus isinya sendiri sambil diisi.
         */
        if (! $this->terisi) {
            foreach ($calon as $b) {
                $this->nilai[(int) $b['id']] = $b['harga_musim'] === null
                    ? ''
                    : $this->rupiah($b['harga_musim']);
            }
            $this->terisi = true;
        }

        return view('livewire.pages.admin.orcha.rab.musim-harga-isi', [
            'musim' => $musim,
            'calon' => $calon,
            /*
             | Baris SIAP BACA, berikut rekapnya — dihitung di sini, bukan di
             | blade.
             |
             | Bukan sekadar kerapian: perbandingan angka di dalam blok @php
             | sebuah Blade menaruh tanda ">" di tempat yang membuat penjaga
             | penanda morph Livewire kehilangan hitungan pasangannya. Blok
             | @foreach-nya jadi terbaca timpang, dan akibatnya di peramban
             | adalah bagian halaman yang tidak ikut diperbarui — tanpa satu
             | pun galat. Sudah pernah kena di layar lain.
             */
            'siap' => $this->siapBaca($calon),
            'rekap' => $this->rekap($calon),
            'kategoriAda' => collect($calon)->pluck('kategori_label', 'kategori')->filter()->unique()->all(),
            'provinsiAda' => collect($calon)->pluck('provinsi')->filter()->unique()->sort()->values()->all(),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Tiap baris berikut keadaan kotaknya, tanpa satu pun hitungan di blade.
     *
     * @param  array<int, array<string, mixed>>  $calon
     * @return array<int, array<string, mixed>>
     */
    private function siapBaca(array $calon): array
    {
        $hasil = [];

        foreach ($calon as $b) {
            $angka = $this->nilai[$b['id']] ?? '';
            $ada = $this->adaAngka($angka);
            $dasar = (int) $b['harga'];
            $selisih = $ada ? $this->angkaDari((string) $angka) - $dasar : 0;

            $hasil[] = $b + [
                'ada' => $ada,
                /*
                 | 'naik' / 'turun' / 'sama' / null — satu kata, bukan angka
                 | yang harus dibandingkan lagi di layar.
                 |
                 | Turun hampir selalu salah ketik: nol yang kurang, atau
                 | angka yang tertukar dengan harga biasa.
                 */
                'arah' => match (true) {
                    ! $ada => null,
                    $selisih === 0 => 'sama',
                    $selisih === abs($selisih) => 'naik',
                    default => 'turun',
                },
                'persen_teks' => $ada && $dasar !== 0
                    ? ($selisih === abs($selisih) ? '+' : '').round($selisih / $dasar * 100, 1).'%'
                    : null,
                'akhir_pekan_teks' => $b['harga_akhir_pekan'] === null
                    ? '—'
                    : 'Rp '.number_format((int) $b['harga_akhir_pekan'], 0, ',', '.'),
            ];
        }

        return $hasil;
    }

    /**
     * Berapa yang terisi, dan berapa yang arahnya mencurigakan.
     *
     * @param  array<int, array<string, mixed>>  $calon
     * @return array{terisi: int, naik: int, turun: int}
     */
    private function rekap(array $calon): array
    {
        $r = ['terisi' => 0, 'naik' => 0, 'turun' => 0];

        foreach ($this->siapBaca($calon) as $b) {
            if (! $b['ada']) {
                continue;
            }

            $r['terisi']++;

            if ($b['arah'] === 'naik') {
                $r['naik']++;
            } elseif ($b['arah'] === 'turun') {
                $r['turun']++;
            }
        }

        return $r;
    }
}
