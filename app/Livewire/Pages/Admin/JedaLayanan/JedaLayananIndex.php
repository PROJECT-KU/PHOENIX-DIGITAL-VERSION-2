<?php

namespace App\Livewire\Pages\Admin\JedaLayanan;

use App\Models\Product;
use App\Support\FiturAdmin;
use App\Support\FiturPublik;
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

    /** Keterangan per produk akun yang sedang dijeda, dikunci id produk. */
    public array $pesanProduk = [];

    /** Keterangan per fitur publik, dikunci nama fitur. */
    public array $pesanFitur = [];

    /** Keterangan per modul admin, dikunci nama modul. */
    public array $pesanModul = [];

    public function mount(): void
    {
        $this->muat();
        $this->muatPesanProduk();
        $this->muatPesanFitur();
        $this->muatPesanModul();
    }

    private function muatPesanModul(): void
    {
        $this->pesanModul = collect(FiturAdmin::keadaan())
            ->map(fn ($info) => $info['pesan'])
            ->all();
    }

    /**
     * Buka/tutup satu modul admin.
     *
     * Berbeda dari izin: izin menentukan siapa yang boleh, ini menentukan
     * apakah modulnya sedang bisa dipakai sama sekali. Pemegang izin kelola
     * tetap bisa menembus, supaya perbaikannya bisa diperiksa dan dibuka lagi.
     */
    public function alihkanModul(string $modul): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! FiturAdmin::ada($modul)) {
            return;
        }

        $jadiDitutup = ! FiturAdmin::ditutup($modul);

        FiturAdmin::setel($modul, $jadiDitutup, $this->pesanModul[$modul] ?? null);
        $this->muatPesanModul();

        $this->dispatch('swal-success', message: $jadiDitutup
            ? FiturAdmin::label($modul).' ditutup untuk karyawan.'
            : FiturAdmin::label($modul).' dibuka kembali.');
    }

    /** Simpan keterangan satu modul tanpa mengubah status tutupnya. */
    public function simpanPesanModul(string $modul): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! FiturAdmin::ada($modul)) {
            return;
        }

        FiturAdmin::setel($modul, FiturAdmin::ditutup($modul), $this->pesanModul[$modul] ?? '');
        $this->muatPesanModul();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
    }

    private function muatPesanFitur(): void
    {
        $this->pesanFitur = collect(FiturPublik::keadaan())
            ->map(fn ($info) => $info['pesan'])
            ->all();
    }

    /**
     * Buka/tutup satu halaman publik.
     *
     * Berbeda dari jeda produk yang hanya menutup tombol belinya, di sini
     * halamannya sendiri diganti pemberitahuan — dipakai saat halamannya yang
     * sedang dikerjakan, bukan barangnya yang habis.
     */
    public function alihkanFitur(string $fitur): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! FiturPublik::ada($fitur)) {
            return;
        }

        $jadiDitutup = ! FiturPublik::ditutup($fitur);

        FiturPublik::setel($fitur, $jadiDitutup, $this->pesanFitur[$fitur] ?? null);
        $this->muatPesanFitur();

        $this->dispatch('swal-success', message: $jadiDitutup
            ? FiturPublik::label($fitur).' ditutup untuk pengunjung.'
            : FiturPublik::label($fitur).' dibuka kembali.');
    }

    /** Simpan keterangan satu fitur tanpa mengubah status tutupnya. */
    public function simpanPesanFitur(string $fitur): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah status layanan.');

            return;
        }

        if (! FiturPublik::ada($fitur)) {
            return;
        }

        FiturPublik::setel($fitur, FiturPublik::ditutup($fitur), $this->pesanFitur[$fitur] ?? '');
        $this->muatPesanFitur();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
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
        // Yang menerima pesanan ditampilkan sebagai daftar RINGKAS satu baris —
        // dua puluhan produk dengan kartu penuh akan mengubur kartu jasa di
        // atasnya, padahal yang butuh perhatian hanya yang sedang dijeda.
        $akunAktif = Product::where('butuh_file', false)
            ->where('dijeda', false)
            ->orderBy('nama_akun')
            ->get();

        return view('livewire.pages.admin.jeda-layanan.jeda-layanan-index', [
            'labelJeda' => JedaLayanan::JENIS,
            'produkPerJenis' => JedaLayanan::produkPerJenis(),
            'bolehKelola' => $this->bolehKelola(),
            'akunDijeda' => JedaLayanan::produkAkunDijeda(),
            'akunAktif' => $akunAktif,
            // Dipisah di sini, bukan di tampilan: yang ditutup tampil sebagai
            // kartu penuh di atas, sisanya baris ringkas — pola yang sama
            // dengan bagian Produk Akun.
            'fiturTutup' => collect(FiturPublik::keadaan())->filter(fn ($i) => $i['ditutup'])->all(),
            'fiturBuka' => collect(FiturPublik::keadaan())->reject(fn ($i) => $i['ditutup'])->all(),
            'jumlahFitur' => count(FiturPublik::DAFTAR),
            'modulTutup' => collect(FiturAdmin::keadaan())->filter(fn ($i) => $i['ditutup'])->all(),
            'modulBuka' => collect(FiturAdmin::keadaan())->reject(fn ($i) => $i['ditutup'])->all(),
            'jumlahModul' => count(FiturAdmin::DAFTAR),
        ])->layout('livewire.layout.templateindex');
    }
}
