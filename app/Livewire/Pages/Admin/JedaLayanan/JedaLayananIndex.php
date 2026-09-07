<?php

namespace App\Livewire\Pages\Admin\JedaLayanan;

use App\Models\Product;
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

    /** Pencarian produk akun yang hendak dijeda. */
    public string $cariProduk = '';

    /** Keterangan per produk akun yang sedang dijeda, dikunci id produk. */
    public array $pesanProduk = [];

    public function mount(): void
    {
        $this->muat();
        $this->muatPesanProduk();
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

    /**
     * Buka/tutup satu produk akun.
     *
     * Produk akun dijeda satu per satu karena tidak punya pengelompokan alami:
     * yang bermasalah biasanya satu produk saja, sementara puluhan lainnya
     * baik-baik saja.
     */
    public function alihkanProduk(string $id): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        $produk = Product::where('butuh_file', false)->find($id);

        if (! $produk) {
            $this->dispatch('swal-error', message: 'Produk tidak ditemukan.');

            return;
        }

        $jadiDijeda = ! $produk->dijeda;

        JedaLayanan::setelProduk($produk, $jadiDijeda, $this->pesanProduk[$id] ?? null);

        $this->cariProduk = '';
        $this->muatPesanProduk();

        $this->dispatch('swal-success', message: $jadiDijeda
            ? $produk->nama_akun.' dijeda — pesanan baru ditutup.'
            : $produk->nama_akun.' dibuka kembali.');
    }

    /** Simpan keterangan satu produk tanpa mengubah status jedanya. */
    public function simpanPesanProduk(string $id): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        $produk = Product::where('butuh_file', false)->find($id);

        if (! $produk) {
            return;
        }

        JedaLayanan::setelProduk($produk, (bool) $produk->dijeda, $this->pesanProduk[$id] ?? '');
        $this->muatPesanProduk();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
    }

    private function muatPesanProduk(): void
    {
        $this->pesanProduk = JedaLayanan::produkAkunDijeda()
            ->mapWithKeys(fn (Product $p) => [$p->id => (string) $p->pesan_jeda])
            ->all();
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
        // Hasil pencarian hanya muncul saat admin mengetik, dan tidak pernah
        // menampilkan produk yang sudah dijeda — itu sudah terdaftar di atasnya.
        $hasilCari = collect();

        if (trim($this->cariProduk) !== '') {
            $hasilCari = Product::where('butuh_file', false)
                ->where('dijeda', false)
                ->where('nama_akun', 'like', '%'.trim($this->cariProduk).'%')
                ->orderBy('nama_akun')
                ->limit(8)
                ->get();
        }

        return view('livewire.pages.admin.jeda-layanan.jeda-layanan-index', [
            'labelJeda' => JedaLayanan::JENIS,
            'produkPerJenis' => JedaLayanan::produkPerJenis(),
            'bolehKelola' => $this->bolehKelola(),
            'akunDijeda' => JedaLayanan::produkAkunDijeda(),
            'hasilCari' => $hasilCari,
        ])->layout('livewire.layout.templateindex');
    }
}
