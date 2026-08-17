<?php

namespace App\Livewire\Pages\Admin\Orcha\Armada;

use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tambah / ubah unit armada Orcha dari lemon.
 *
 * Tarif per jam dan per 12 jam boleh kosong — bus memang tidak dilepas per jam,
 * dan Orcha memperlakukan kosong sebagai "tidak dijual per satuan itu".
 */
class OrchaArmadaForm extends Component
{
    use IsianRupiah, MemanggilOrcha, WithFileUploads;

    public ?int $kendaraanId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $merek = '';

    public string $jenis = 'mobil';

    public string $nopol = '';

    public int $kapasitas = 7;

    public array $transmisi = ['Manual'];

    public $tarifHari = 0;

    public $tarifJam = '';

    public $tarif12Jam = '';

    public $tarifSopir = '';

    /** Bentuk bertitik yang tampil di layar. */
    public string $tarifHariTeks = '';

    public string $tarifJamTeks = '';

    public string $tarif12JamTeks = '';

    public string $tarifSopirTeks = '';

    public bool $tersedia = true;

    public $gambar;

    public ?string $gambarLama = null;

    /**
     * Kondisi fisik dan jadwal pakai unit, hanya untuk dibaca.
     *
     * Ditampilkan saat mengubah unit karena keduanya menentukan apakah
     * perubahan yang sedang dikerjakan aman: menonaktifkan unit yang sedang
     * disewa, atau menaikkan tarif unit yang kacanya masih retak, adalah dua
     * hal yang lebih baik disadari sebelum tombol simpan ditekan.
     *
     * Tidak bisa disunting di sini — kondisi hanya berubah lewat serah terima.
     */
    public ?array $kondisi = null;

    public array $jadwal = [];

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'merek' => 'required|string|max:100',
            'jenis' => 'required|string',
            'nopol' => 'nullable|string|max:20',
            'kapasitas' => 'required|integer|min:1|max:80',
            'transmisi' => 'required|array|min:1',
            'tarifHari' => 'required|numeric|min:0',
            'tarifJam' => 'nullable|numeric|min:0',
            'tarif12Jam' => 'nullable|numeric|min:0',
            'tarifSopir' => 'nullable|numeric|min:0',
            'gambar' => 'nullable|image|max:4096',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama unit',
            'transmisi' => 'transmisi',
            'tarifHari' => 'tarif per hari',
            'tarifJam' => 'tarif per jam',
            'tarif12Jam' => 'tarif paket 12 jam',
            'tarifSopir' => 'tarif sopir',
        ];
    }

    public function updatedTarifHariTeks(): void
    {
        $this->tarifHari = $this->angkaDari($this->tarifHariTeks);
        $this->tarifHariTeks = $this->keRupiah($this->tarifHari);
    }

    public function updatedTarifJamTeks(): void
    {
        $this->tarifJam = $this->angkaDari($this->tarifJamTeks) ?: '';
        $this->tarifJamTeks = $this->keRupiah($this->tarifJam);
    }

    public function updatedTarif12JamTeks(): void
    {
        $this->tarif12Jam = $this->angkaDari($this->tarif12JamTeks) ?: '';
        $this->tarif12JamTeks = $this->keRupiah($this->tarif12Jam);
    }

    public function updatedTarifSopirTeks(): void
    {
        $this->tarifSopir = $this->angkaDari($this->tarifSopirTeks) ?: '';
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
    }

    public function mount(?int $kendaraan = null): void
    {
        if (! $kendaraan) {
            return;
        }

        $this->kendaraanId = $kendaraan;
        $this->ubah = true;

        $isi = $this->muat("/kendaraan/{$kendaraan}")['data'] ?? [];

        if ($isi === []) {
            return;
        }

        $this->kondisi = $isi['kondisi'] ?? null;
        $this->jadwal = $isi['jadwal'] ?? [];

        $this->nama = $isi['nama'] ?? '';
        $this->merek = $isi['merek'] ?? '';
        $this->jenis = $isi['jenis'] ?? 'mobil';
        $this->nopol = (string) ($isi['nopol'] ?? '');
        $this->kapasitas = (int) ($isi['kapasitas'] ?? 7);
        $this->transmisi = $isi['transmisi_tersedia'] ?: ['Manual'];
        $this->tarifHari = $isi['tarif']['hari'] ?? 0;
        $this->tarifJam = $isi['tarif']['jam'] ?? '';
        $this->tarif12Jam = $isi['tarif']['12jam'] ?? '';
        $this->tarifSopir = $isi['tarif']['sopir_per_hari'] ?? '';
        $this->tersedia = (bool) ($isi['tersedia'] ?? true);
        $this->tarifHariTeks = $this->keRupiah($this->tarifHari);
        $this->tarifJamTeks = $this->keRupiah($this->tarifJam);
        $this->tarif12JamTeks = $this->keRupiah($this->tarif12Jam);
        $this->tarifSopirTeks = $this->keRupiah($this->tarifSopir);
        $this->gambarLama = $isi['gambar'] ?? null;
    }

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'merek' => $this->merek,
            'jenis' => $this->jenis,
            'nopol' => $this->nopol,
            'kapasitas' => $this->kapasitas,
            'transmisi_tersedia' => array_values($this->transmisi),
            'tarif_hari' => $this->tarifHari,
            'tarif_jam' => $this->tarifJam ?: null,
            'tarif_12jam' => $this->tarif12Jam ?: null,
            'tarif_sopir' => $this->tarifSopir ?: null,
            'tersedia' => $this->tersedia,
        ];

        // Tujuan diteruskan supaya pemberitahuan sukses tampil utuh dulu di
        // halaman ini, baru berpindah ke daftar setelah popupnya menutup.
        $this->ubah
            ? $this->kirimData("/kendaraan/{$this->kendaraanId}", $data, 'Kendaraan diperbarui.', $this->gambar, route('admin.orcha.armada'))
            : $this->kirimData('/kendaraan', $data, 'Kendaraan ditambahkan.', $this->gambar, route('admin.orcha.armada'));
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.armada.form', [
            'pilihanJenis' => $this->rujukan('jenis_kendaraan'),
        ])->layout('livewire.layout.templateindex');
    }
}
