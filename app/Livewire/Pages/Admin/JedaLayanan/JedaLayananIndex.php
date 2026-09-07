<?php

namespace App\Livewire\Pages\Admin\JedaLayanan;

use App\Support\JedaLayanan;
use Livewire\Component;

/**
 * Halaman buka-tutup pemesanan layanan jasa.
 *
 * Menutup PEMBELIAN BARU tanpa menyembunyikan produknya: halaman produk tetap
 * terbuka agar calon pembeli tahu layanan itu ada dan peringkat pencariannya
 * tidak hilang. Tiap jenis berdiri sendiri — menjeda Cek Plagiasi tidak
 * menyentuh Cek AI maupun Parafrase.
 */
class JedaLayananIndex extends Component
{
    /** @var array<string, array{dijeda:bool, pesan:string}> */
    public array $jeda = [];

    public function mount(): void
    {
        $this->muat();
    }

    private function muat(): void
    {
        $this->jeda = [];

        foreach (JedaLayanan::daftar() as $jenis => $info) {
            $this->jeda[$jenis] = [
                'dijeda' => $info['dijeda'],
                'pesan' => $info['pesan'],
            ];
        }
    }

    private function bolehKelola(): bool
    {
        return (bool) auth()->user()?->hasPermission('manage_jeda_layanan');
    }

    /** Buka/tutup satu jenis layanan. */
    public function alihkanJeda(string $jenis): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! array_key_exists($jenis, JedaLayanan::JENIS)) {
            return;
        }

        $jadiDijeda = ! ($this->jeda[$jenis]['dijeda'] ?? false);

        JedaLayanan::setel($jenis, $jadiDijeda, $this->jeda[$jenis]['pesan'] ?? null);
        $this->muat();

        $this->dispatch('swal-success', message: $jadiDijeda
            ? JedaLayanan::JENIS[$jenis].' dijeda — pesanan baru ditutup.'
            : JedaLayanan::JENIS[$jenis].' dibuka kembali.');
    }

    /** Simpan keterangan yang dilihat pembeli, tanpa mengubah status jeda. */
    public function simpanPesan(string $jenis): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! array_key_exists($jenis, JedaLayanan::JENIS)) {
            return;
        }

        JedaLayanan::setel(
            $jenis,
            (bool) ($this->jeda[$jenis]['dijeda'] ?? false),
            $this->jeda[$jenis]['pesan'] ?? ''
        );
        $this->muat();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
    }

    public function render()
    {
        return view('livewire.pages.admin.jeda-layanan.jeda-layanan-index', [
            'labelJeda' => JedaLayanan::JENIS,
            'produkPerJenis' => JedaLayanan::produkPerJenis(),
            'bolehKelola' => $this->bolehKelola(),
        ])->layout('livewire.layout.templateindex');
    }
}
