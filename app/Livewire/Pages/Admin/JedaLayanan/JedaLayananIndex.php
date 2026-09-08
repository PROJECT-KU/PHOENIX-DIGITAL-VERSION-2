<?php

namespace App\Livewire\Pages\Admin\JedaLayanan;

use App\Models\Product;
use App\Support\FiturAdmin;
use App\Support\FiturPublik;
use App\Support\JedaLayanan;
use App\Support\KabarFiturPublik;
use App\Support\KabarJedaModul;
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

    /** Perkiraan selesai per modul admin (format datetime-local). */
    public array $sampaiModul = [];

    /** Perkiraan selesai per halaman publik (format datetime-local). */
    public array $sampaiFitur = [];

    public function mount(): void
    {
        $this->muat();
        $this->muatPesanProduk();
        $this->muatPesanFitur();
        $this->muatPesanModul();
    }

    /**
     * Perkiraan selesai dalam bentuk waktu biasa, atau null bila dikosongkan.
     *
     * Nilai dari datetime-local ("2026-09-09T08:00") tidak bisa langsung
     * disimpan sebagai waktu; huruf T di tengahnya membuat pembacaan kembali
     * bergantung pada tebakan penata waktu.
     */
    private function waktuSampai(string $modul): ?string
    {
        $mentah = trim((string) ($this->sampaiModul[$modul] ?? ''));

        if ($mentah === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($mentah)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function muatPesanModul(): void
    {
        $keadaan = collect(FiturAdmin::keadaan());

        $this->pesanModul = $keadaan->map(fn ($info) => $info['pesan'])->all();

        // datetime-local menuntut bentuk "Y-m-dTH:i"; nilainya disimpan sebagai
        // waktu biasa agar tetap terbaca di database.
        $this->sampaiModul = $keadaan
            ->map(fn ($info) => $info['sampai']?->format('Y-m-d\\TH:i') ?? '')
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

        FiturAdmin::setel(
            $modul,
            $jadiDitutup,
            $this->pesanModul[$modul] ?? null,
            $this->waktuSampai($modul),
        );
        $this->muatPesanModul();

        // Dikirim SESUDAH statusnya tersimpan: kabar hanya boleh keluar untuk
        // keadaan yang benar-benar sudah berlaku. Gagal kirim tidak
        // membatalkan penutupan — modulnya ditutup karena ada yang perlu
        // diperbaiki, dan surel yang gagal urusan yang jauh lebih ringan.
        $terkirim = KabarJedaModul::kirim(
            FiturAdmin::label($modul),
            $jadiDitutup,
            FiturAdmin::pesan($modul),
            auth()->user(),
            FiturAdmin::mulai($modul),
            FiturAdmin::sampai($modul),
        );

        $kabar = match (true) {
            $terkirim === 0 => ' Kabar surel tidak terkirim — cek log.',
            KabarJedaModul::modeUji() => ' Kabar surel dikirim ke alamat uji coba.',
            default => ' Kabar surel dikirim ke '.$terkirim.' karyawan.',
        };

        $this->dispatch('swal-success', message: ($jadiDitutup
            ? FiturAdmin::label($modul).' ditutup untuk karyawan.'
            : FiturAdmin::label($modul).' dibuka kembali.').$kabar);
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

        FiturAdmin::setel(
            $modul,
            FiturAdmin::ditutup($modul),
            $this->pesanModul[$modul] ?? '',
            $this->waktuSampai($modul) ?? '',
        );
        $this->muatPesanModul();

        $this->dispatch('swal-success', message: 'Keterangan disimpan.');
    }

    private function muatPesanFitur(): void
    {
        $keadaan = collect(FiturPublik::keadaan());

        $this->pesanFitur = $keadaan->map(fn ($info) => $info['pesan'])->all();
        $this->sampaiFitur = $keadaan
            ->map(fn ($info) => $info['sampai']?->format('Y-m-d\\TH:i') ?? '')
            ->all();
    }

    /** Perkiraan selesai halaman publik dalam bentuk waktu biasa. */
    private function waktuSampaiFitur(string $fitur): ?string
    {
        $mentah = trim((string) ($this->sampaiFitur[$fitur] ?? ''));

        if ($mentah === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($mentah)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
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

        FiturPublik::setel(
            $fitur,
            $jadiDitutup,
            $this->pesanFitur[$fitur] ?? null,
            $this->waktuSampaiFitur($fitur),
        );
        $this->muatPesanFitur();

        // Dikirim SESUDAH statusnya tersimpan, dan lewat BCC — 123 pelanggan
        // tidak boleh saling melihat alamat surelnya.
        $terkirim = KabarFiturPublik::kirim(
            FiturPublik::label($fitur),
            $jadiDitutup,
            FiturPublik::pesan($fitur),
            FiturPublik::mulai($fitur),
            FiturPublik::sampai($fitur),
        );

        $kabar = match (true) {
            $terkirim === 0 => ' Kabar surel tidak terkirim — cek log.',
            KabarFiturPublik::modeUji() => ' Kabar surel dikirim ke alamat uji coba.',
            default => ' Kabar surel dikirim ke '.$terkirim.' pelanggan.',
        };

        $this->dispatch('swal-success', message: ($jadiDitutup
            ? FiturPublik::label($fitur).' ditutup untuk pengunjung.'
            : FiturPublik::label($fitur).' dibuka kembali.').$kabar);
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

        FiturPublik::setel(
            $fitur,
            FiturPublik::ditutup($fitur),
            $this->pesanFitur[$fitur] ?? '',
            $this->waktuSampaiFitur($fitur) ?? '',
        );
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
            'jumlahPelanggan' => KabarFiturPublik::jumlahPenerima(),
            'modeUjiSurel' => KabarFiturPublik::modeUji(),
            'modulTutup' => collect(FiturAdmin::keadaan())->filter(fn ($i) => $i['ditutup'])->all(),
            'modulBuka' => collect(FiturAdmin::keadaan())->reject(fn ($i) => $i['ditutup'])->all(),
            'jumlahModul' => count(FiturAdmin::DAFTAR),
        ])->layout('livewire.layout.templateindex');
    }
}
