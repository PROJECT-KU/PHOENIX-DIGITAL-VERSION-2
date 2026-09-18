<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Master harga: daftar harga yang dirawat admin dan dipakai setiap RAB.
 *
 * Harga di sini TIDAK mengubah RAB yang sudah ada — RAB membekukan harganya
 * saat baris biaya ditambahkan. Menaikkan tiket Prambanan hari ini hanya
 * berlaku untuk RAB berikutnya; RAB lama cukup diberi tanda "harga master
 * sudah berubah" supaya admin bisa memutuskan sendiri.
 */
class OrchaMasterHargaList extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    #[Url(as: 'kategori', except: '')]
    public string $filterKategori = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    public ?int $sunting = null;

    public bool $tambah = false;

    public array $isian = [];

    /** Sakelar dipegang properti tersendiri — lihat OrchaPromoList::$aktif. */
    public bool $aktif = true;

    public bool $otomatis = false;

    private function kosongkan(): void
    {
        $this->isian = [
            'kategori' => 'tiket',
            'nama' => '',
            'provinsi' => $this->filterProvinsi !== '-' ? $this->filterProvinsi : '',
            'daerah' => '',
            'destinasi' => '',
            'satuan' => 'orang',
            'harga' => '',
            'kapasitas' => '',
            'catatan' => '',
        ];
        $this->aktif = true;
        $this->otomatis = false;
    }

    public function mount(): void
    {
        $this->kosongkan();
    }

    public function updatedFilterKategori(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterProvinsi(): void
    {
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterKategori = '';
        $this->filterProvinsi = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterKategori !== '' || $this->filterProvinsi !== '';
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
        $this->resetValidation();

        $this->isian = [
            'kategori' => $baris['kategori'],
            'nama' => $baris['nama'],
            'provinsi' => (string) ($baris['provinsi'] ?? ''),
            'daerah' => (string) ($baris['daerah'] ?? ''),
            'destinasi' => (string) ($baris['destinasi'] ?? ''),
            'satuan' => $baris['satuan'],
            'harga' => $this->keRupiah($baris['harga']),
            'kapasitas' => (string) ($baris['kapasitas'] ?? ''),
            'catatan' => (string) ($baris['catatan'] ?? ''),
        ];
        $this->aktif = (bool) $baris['aktif'];
        $this->otomatis = (bool) $baris['otomatis'];
    }

    public function tutup(): void
    {
        $this->sunting = null;
        $this->tambah = false;
        $this->kosongkan();
    }

    /** Harga ditampilkan bertitik begitu kotaknya ditinggalkan. */
    public function updatedIsianHarga(): void
    {
        $this->isian['harga'] = $this->keRupiah($this->angkaDari($this->isian['harga']));
    }

    /**
     * Destinasi yang dipilih mengisi provinsi & daerahnya sendiri.
     *
     * Tiket Prambanan yang tersimpan tanpa provinsi berlaku di SEMUA provinsi
     * — dan muncul di RAB Bali. Mengisinya otomatis dari katalog menutup
     * kemungkinan itu tanpa menambah satu kotak pun untuk diisi admin.
     */
    public function updatedIsianDestinasi(string $nama): void
    {
        $cocok = collect($this->katalog()['destinasi'] ?? [])->firstWhere('nama', $nama);

        if ($cocok) {
            $this->isian['provinsi'] = (string) ($cocok['provinsi'] ?? $this->isian['provinsi']);
            $this->isian['daerah'] = (string) ($cocok['daerah'] ?? $this->isian['daerah']);
        }
    }

    public function simpan(): void
    {
        $berkapasitas = in_array($this->isian['satuan'], ['unit_hari', 'kamar_malam'], true);

        $this->validate([
            'isian.kategori' => 'required|string',
            'isian.nama' => 'required|string|min:2|max:150',
            'isian.satuan' => 'required|string',
            'isian.harga' => 'required',
            'isian.kapasitas' => $berkapasitas ? 'required|integer|min:1|max:1000' : 'nullable|integer|min:1|max:1000',
            'isian.catatan' => 'nullable|string|max:500',
        ], [
            'isian.kapasitas.required' => 'Isi kapasitasnya — tanpanya jumlah unit untuk rombongan besar tidak bisa dihitung.',
        ], [
            'isian.nama' => 'nama', 'isian.harga' => 'harga', 'isian.kapasitas' => 'kapasitas',
        ]);

        $data = [
            'kategori' => $this->isian['kategori'],
            'nama' => trim($this->isian['nama']),
            'provinsi' => $this->isian['provinsi'] ?: null,
            'daerah' => trim($this->isian['daerah']) ?: null,
            'destinasi' => trim($this->isian['destinasi']) ?: null,
            'satuan' => $this->isian['satuan'],
            'harga' => $this->angkaDari($this->isian['harga']),
            'kapasitas' => $berkapasitas || $this->isian['kapasitas'] !== '' ? ((int) $this->isian['kapasitas'] ?: null) : null,
            'otomatis' => $this->otomatis,
            'aktif' => $this->aktif,
            'catatan' => trim($this->isian['catatan']) ?: null,
        ];

        try {
            $this->sunting
                ? $this->orcha()->ubah("/master-harga/{$this->sunting}", $data)
                : $this->orcha()->kirim('/master-harga', $data);

            $this->tutup();
            $this->dispatch('order-updated', message: 'Harga disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/master-harga/{$id}", 'Harga dihapus. RAB yang sudah memakainya tidak berubah.');
    }

    /** Provinsi & destinasi untuk pilihan; disimpan sebentar karena jarang berubah. */
    private function katalog(): array
    {
        $simpan = cache()->get('orcha.rab.katalog');

        if (is_array($simpan) && $simpan !== []) {
            return $simpan;
        }

        try {
            $data = $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }

        if ($data !== []) {
            cache()->put('orcha.rab.katalog', $data, now()->addMinutes(10));
        }

        return $data;
    }

    public function render()
    {
        $hasil = $this->muat('/master-harga', $this->parameterDaftar([
            'kategori' => $this->filterKategori,
            'provinsi' => $this->filterProvinsi,
        ]));

        $katalog = $this->katalog();
        $kosakata = $hasil['meta']['kosakata'] ?? $katalog['kosakata'] ?? [];

        // Destinasi yang ditawarkan di formulir mengikuti provinsi terpilih —
        // daftar 70 destinasi seluruh Indonesia untuk tiket Jogja hanya
        // menambah gulungan.
        $destinasi = collect($katalog['destinasi'] ?? [])
            ->when($this->isian['provinsi'] ?? '', fn ($c, $p) => $c->where('provinsi', $p))
            ->pluck('nama')->values()->all();

        return view('livewire.pages.admin.orcha.rab.master-harga', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'kategori' => $kosakata['kategori'] ?? [],
            'satuan' => $kosakata['satuan'] ?? [],
            'berkapasitas' => $kosakata['satuan_berkapasitas'] ?? ['unit_hari', 'kamar_malam'],
            'provinsi' => $katalog['provinsi'] ?? [],
            'destinasi' => $destinasi,
        ])->layout('livewire.layout.templateindex');
    }
}
