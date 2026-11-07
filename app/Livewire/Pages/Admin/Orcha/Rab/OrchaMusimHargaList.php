<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Musim harga: Nataru, Lebaran, long weekend.
 *
 * BUKAN hal yang sama dengan Hari Libur, dan ini satu-satunya yang perlu
 * dipahami sebelum memakai keduanya:
 *
 * - Hari Libur hanya berkata "perlakukan tanggal ini seperti akhir pekan",
 *   lalu kolom harga akhir pekan yang dipakai.
 * - Musim punya ANGKANYA SENDIRI per baris harga.
 *
 * Jadi Nataru tidak boleh diisi lewat Hari Libur. Hotel yang Rp 550.000 di
 * Sabtu bisa Rp 900.000 di Nataru, dan memasukkannya sebagai tanggal merah
 * membuat seluruh rombongan Nataru dibayari tarif hari Sabtu — selisih yang
 * baru terbaca di meja resepsionis.
 *
 * Layar ini karena itu menyebut bedanya di muka, bukan menganggap admin
 * sudah tahu.
 */
class OrchaMusimHargaList extends Component
{
    use MemanggilOrcha;

    #[Url(as: 'tahun', except: '')]
    public string $filterTahun = '';

    public bool $tambah = false;

    public ?int $sunting = null;

    public array $isian = [
        'nama' => '',
        'tanggal_mulai' => '',
        'tanggal_sampai' => '',
        'prioritas' => 0,
        'aktif' => true,
    ];

    /**
     * Musim yang sering dipakai, sebagai pengisi cepat NAMANYA saja.
     *
     * Tanggalnya TIDAK ditebak: Lebaran bergeser sebelas hari tiap tahun, dan
     * menebakkannya berarti menuliskan rentang yang salah dengan yakin —
     * lalu seluruh tarif tinggi berlaku di pekan yang keliru.
     */
    public const NAMA_SERING = [
        'Nataru', 'Lebaran', 'Long weekend', 'Libur sekolah', 'Tahun Baru',
    ];

    public function mount(): void
    {
        $this->kosongkan();
    }

    private function kosongkan(): void
    {
        $this->isian = [
            'nama' => '',
            'tanggal_mulai' => '',
            'tanggal_sampai' => '',
            'prioritas' => 0,
            'aktif' => true,
        ];
    }

    public function updatedFilterTahun(): void
    {
        $this->halaman = 1;
    }

    public function bukaTambah(): void
    {
        $this->sunting = null;
        $this->tambah = true;
        $this->kosongkan();
        $this->resetValidation();
    }

    public function bukaSunting(int $id, array $baris): void
    {
        $this->tambah = false;
        $this->sunting = $id;
        $this->isian = [
            'nama' => $baris['nama'],
            'tanggal_mulai' => $baris['tanggal_mulai'],
            'tanggal_sampai' => $baris['tanggal_sampai'],
            'prioritas' => (int) $baris['prioritas'],
            'aktif' => (bool) $baris['aktif'],
        ];
        $this->resetValidation();
    }

    public function tutup(): void
    {
        $this->tambah = false;
        $this->sunting = null;
        $this->kosongkan();
        $this->resetValidation();
    }

    public function pakaiNama(string $nama): void
    {
        $this->isian['nama'] = $nama;
        $this->resetValidation('isian.nama');
    }

    public function simpan(): void
    {
        $this->validate([
            'isian.nama' => 'required|string|min:2|max:120',
            'isian.tanggal_mulai' => 'required|date',
            /*
             | after_or_equal, bukan after: musim sehari — 1 Januari — ditulis
             | dengan kedua tanggal yang sama. Mewajibkan "sesudah" membuatnya
             | mustahil ditulis, dan admin akan menuliskannya sebagai 1–2
             | Januari: satu hari tarif tinggi yang tidak diminta siapa pun.
             */
            'isian.tanggal_sampai' => 'required|date|after_or_equal:isian.tanggal_mulai',
            'isian.prioritas' => 'nullable|integer|min:0|max:1000',
        ], [
            'isian.nama.required' => 'Tulis nama musimnya — "Nataru 2026/2027" sudah cukup.',
            'isian.tanggal_mulai.required' => 'Pilih tanggal mulainya.',
            'isian.tanggal_sampai.required' => 'Pilih tanggal selesainya.',
            'isian.tanggal_sampai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ], [
            'isian.nama' => 'nama',
            'isian.tanggal_mulai' => 'tanggal mulai',
            'isian.tanggal_sampai' => 'tanggal selesai',
        ]);

        $data = [
            'nama' => trim($this->isian['nama']),
            'tanggal_mulai' => $this->isian['tanggal_mulai'],
            'tanggal_sampai' => $this->isian['tanggal_sampai'],
            'prioritas' => (int) ($this->isian['prioritas'] ?: 0),
            'aktif' => (bool) $this->isian['aktif'],
        ];

        try {
            $balasan = $this->sunting
                ? $this->orcha()->ubah("/musim-harga/{$this->sunting}", $data)
                : $this->orcha()->kirim('/musim-harga', $data);

            $this->tutup();
            $this->segarkanKatalog();
            // Pesan dari Orcha, bukan kalimat sendiri: yang menghitung berapa
            // harga terpasang hanya peladen, dan dua kalimat untuk satu
            // kejadian akan berselisih begitu salah satunya diubah.
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Musim disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/musim-harga/{$id}", 'Musim dihapus.');
        $this->segarkanKatalog();
    }

    /**
     * Membuang katalog yang tersimpan supaya kotak tarifnya ikut berubah.
     *
     * Borang Master Harga membaca daftar musim dari katalog, dan katalog itu
     * disimpan sepuluh menit. Tanpa ini, admin yang baru menambahkan Nataru
     * lalu membuka borang harga TIDAK menemukan kotaknya — lalu menyimpulkan
     * fiturnya tidak jalan, dan tarifnya tidak pernah diisi.
     */
    private function segarkanKatalog(): void
    {
        cache()->forget('orcha.rab.katalog');
    }

    /**
     * Mematikan/menyalakan musim tanpa membuka borangnya.
     *
     * Jalan keluar yang ditawarkan saat penghapusan ditolak, jadi ia harus
     * ada dalam satu klik — kalau tidak, admin akan memaksa menghapusnya dan
     * membawa seluruh tarif musimnya.
     */
    public function ubahAktif(int $id, bool $aktif): void
    {
        try {
            $balasan = $this->orcha()->ubah("/musim-harga/{$id}", ['aktif' => $aktif]);

            $this->segarkanKatalog();
            $this->dispatch('order-updated', message: $aktif
                ? 'Musim dinyalakan — RAB baru memakai tarifnya lagi.'
                : 'Musim dimatikan. Tarifnya tetap tersimpan, tetapi RAB baru berhenti memakainya.');
            unset($balasan);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterTahun = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->filterTahun !== '';
    }

    public function render()
    {
        $balasan = $this->muat('/musim-harga', array_filter([
            'tahun' => $this->filterTahun,
        ], fn ($n) => $n !== ''));

        $meta = $balasan['meta'] ?? [];

        return view('livewire.pages.admin.orcha.rab.musim-harga', [
            'daftar' => $balasan['data'] ?? [],
            'tahunAda' => $meta['tahun'] ?? [],
            'hargaTerpasang' => (int) ($meta['harga_terpasang'] ?? 0),
            'namaSering' => self::NAMA_SERING,
        ])->layout('livewire.layout.templateindex');
    }
}
