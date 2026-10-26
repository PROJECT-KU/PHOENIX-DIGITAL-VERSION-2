<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Vendor\Concerns\MenyuntingVendor;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Vendor: dari siapa sebuah harga dibeli, dan ke nomor mana meneleponnya.
 *
 * Dulu kontaknya cuma muat di kolom catatan master harga — teks bebas, tidak
 * bisa dicari, dan tidak ikut tersalin ke RAB. Admin yang sedang menyusun
 * penawaran lalu perlu menelepon vendor busnya harus meninggalkan layar itu,
 * membuka master harga, dan mencari barisnya.
 *
 * Satu vendor memasok beberapa harga sekaligus — Hiace, Elf, dan bus besar
 * dari pemilik armada yang sama — jadi nomornya hidup di satu tempat. Nomor
 * yang berganti cukup diubah di sini dan seluruh harga yang memakainya,
 * termasuk yang sudah terpakai di RAB lama, ikut menunjuk nomor yang benar.
 */
class OrchaVendorList extends Component
{
    use MemanggilOrcha;
    use MenyuntingVendor;

    #[Url(as: 'jenis', except: '')]
    public string $filterJenis = '';

    #[Url(as: 'status', except: '')]
    public string $filterStatus2 = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    /** Vendor yang sedang ditawarkan untuk digabungkan; null berarti tertutup. */
    public ?int $gabungDari = null;

    public string $gabungKe = '';

    /** Daftar master harga yang nomornya masih tertinggal di kolom catatan. */
    public bool $bukaTercecer = false;

    public array $tercecer = [];

    /** Baris tercecer yang dicentang untuk dijadikan SATU vendor. */
    public array $pilihTercecer = [];

    /** Id master harga yang akan dipasangkan begitu vendornya tersimpan. */
    public array $pasangHarga = [];

    /** Sakelar dipegang properti tersendiri — lihat OrchaRujukanList::$aktif. */
    /**
     * Jenis vendor yang paling sering dipakai.
     *
     * Ditawarkan sebagai pilihan cepat, bukan dikunci: vendor nyata tidak
     * selalu muat di lima kotak, dan menolak yang keenam hanya membuat admin
     * mengetiknya di kolom lain.
     */
    public function mount(): void
    {
        $this->kosongkanVendor();
    }

    public function updatedFilterJenis(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterStatus2(): void
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
        $this->filterJenis = '';
        $this->filterStatus2 = '';
        $this->filterProvinsi = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterJenis !== ''
            || $this->filterStatus2 !== '' || $this->filterProvinsi !== '';
    }

    /**
     * Nomor yang bisa ditelepon, diambil dari kontak yang ditulis bebas.
     *
     * Kontaknya memang teks bebas — "081234000111 (Pak Budi)" justru bentuk
     * yang paling berguna. Yang diambil deretan angka pertama yang cukup
     * panjang untuk sebuah nomor; kalau tidak ada, tombolnya tidak muncul,
     * bukan muncul lalu menelepon entah ke mana.
     */
    /** Bentuk 62… untuk WhatsApp; 0 di depan DIGANTI, bukan ditempeli. */
    /**
     * Nomor yang panjangnya di luar wajar.
     *
     * Bukan penolakan — kontaknya memang teks bebas, dan nomor kantor
     * berbeda panjang dari nomor seluler. Tetapi salah ketik satu digit
     * tetap tersimpan dengan yakin, dan tombol WhatsApp-nya mengarah ke
     * nomor orang lain tanpa satu pun tanda. Jadi ditandai, bukan ditolak.
     */
    public function bukaGabung(int $id): void
    {
        $this->gabungDari = $id;
        $this->gabungKe = '';
        $this->resetValidation('gabungKe');
    }

    public function tutupGabung(): void
    {
        $this->gabungDari = null;
        $this->gabungKe = '';
        $this->resetValidation('gabungKe');
    }

    /**
     * Menyatukan dua vendor yang ternyata orang yang sama.
     *
     * Seluruh harga DAN baris biaya RAB yang menunjuk vendor asal dipindahkan
     * ke tujuannya, lalu vendor asalnya dihapus. Yang berpindah rujukannya
     * saja — harganya tidak disentuh.
     */
    public function gabungkan(): void
    {
        $this->resetValidation('gabungKe');

        if ($this->gabungDari === null || $this->gabungKe === '' || (int) $this->gabungKe === $this->gabungDari) {
            $this->addError('gabungKe', 'Pilih vendor tujuan yang berbeda.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/vendor/{$this->gabungDari}/gabung", ['ke' => (int) $this->gabungKe]);
            cache()->forget('orcha.rab.katalog');
            $this->tutupGabung();
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Vendor digabungkan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Master harga yang nomornya masih tertinggal di kolom catatan.
     *
     * Sebelum ada daftar vendor, nomor ditulis di catatan — dan catatan itu
     * tidak pernah ikut ke RAB. Daftar ini yang membuat sisa data lama
     * terlihat, bukan menunggu diingat.
     */
    public function lihatTercecer(): void
    {
        $this->bukaTercecer = true;
        $this->pilihTercecer = [];

        try {
            $this->tercecer = $this->orcha()->ambil('/vendor/kontak-tercecer')['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->tercecer = [];
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Beberapa baris tercecer sekaligus jadi SATU vendor.
     *
     * Satu vendor biasanya menempel pada beberapa baris harga — Hiace, Elf,
     * dan bus besar dari pemilik armada yang sama. Menjadikannya satu per
     * satu berarti mengulang keputusan yang sama berkali-kali, lalu
     * menggabungkannya belakangan.
     */
    public function dariBeberapaCatatan(): void
    {
        $dipilih = collect($this->tercecer)
            ->filter(fn ($t) => in_array((int) $t['id'], array_map('intval', $this->pilihTercecer), true))
            ->values();

        if ($dipilih->isEmpty()) {
            return;
        }

        // Catatan pertama jadi bahan namanya; sisanya cuma ikut dipasangkan.
        $this->dariCatatan((string) $dipilih->first()['catatan']);
        $this->pasangHarga = $dipilih->pluck('id')->map(fn ($i) => (int) $i)->all();
    }

    /** Catatan lama dipakai sebagai bahan vendor baru, bukan diketik ulang. */
    public function dariCatatan(string $catatan): void
    {
        $this->bukaTambah();
        $this->bukaTercecer = false;
        $this->pasangHarga = [];

        $nomor = $this->nomorDari($catatan);

        // Yang bukan nomor dianggap namanya: "PO Sumber Jaya 081234000111"
        // jadi nama "PO Sumber Jaya", kontaknya catatan apa adanya.
        $this->isian['nama'] = $nomor !== null
            ? trim(preg_replace('/\d[\d\s().+-]{6,}/', '', $catatan), " \t\n\r-,.:;|")
            : trim($catatan);
        $this->isian['kontak'] = trim($catatan);
    }

    public function simpan(): void
    {
        $data = $this->periksaVendor();

        try {
            $balasan = $this->sunting
                ? $this->orcha()->ubah("/vendor/{$this->sunting}", $data)
                : $this->orcha()->kirim('/vendor', $data);

            // Daftar vendor ikut disimpan di katalog RAB; tanpa dilupakan,
            // vendor yang baru dibuat tidak muncul di kotak pilih master
            // harga dan tampak seperti gagal tersimpan.
            cache()->forget('orcha.rab.katalog');

            $pesan = $balasan['pesan'] ?? 'Vendor disimpan.';

            // Baris tercecer yang dipilih ikut dipasangkan sekaligus: satu
            // vendor biasanya menempel pada beberapa harga, dan memasangnya
            // satu per satu berarti mengulang keputusan yang sama.
            if ($this->pasangHarga !== [] && ! empty($balasan['data']['id'])) {
                $pesan = $this->pasangkan((int) $balasan['data']['id'], $pesan);
            }

            $this->tutup();
            $this->dispatch('order-updated', message: $pesan);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    private function pasangkan(int $vendorId, string $pesan): string
    {
        try {
            $balasan = $this->orcha()->kirim("/vendor/{$vendorId}/pasang", ['harga' => $this->pasangHarga]);
            $pesan = $balasan['pesan'] ?? $pesan;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }

        $this->pasangHarga = [];
        $this->pilihTercecer = [];

        return $pesan;
    }

    /**
     * Menghapus vendor — ditahan Orcha bila masih dipakai.
     *
     * Balasan 422 dari sana bukan kegagalan, melainkan tawaran: matikan saja
     * supaya tidak ditawarkan lagi. Yang hampir selalu dimaksud admin memang
     * itu, dan menghapusnya menghilangkan kontak dari seluruh RAB yang
     * memakainya tanpa bisa dibatalkan.
     */
    public function hapus(int $id): void
    {
        if ($this->hapusData("/vendor/{$id}", 'Vendor dihapus. Harga yang memakainya tetap ada.')) {
            cache()->forget('orcha.rab.katalog');
        }
    }

    /** Dipaksa, karena baris salah ketik memang pantas dibuang. */
    public function hapusPaksa(int $id): void
    {
        if ($this->hapusData("/vendor/{$id}?paksa=1", 'Vendor dihapus. Harga yang memakainya tetap ada, hanya kehilangan kontaknya.')) {
            cache()->forget('orcha.rab.katalog');
        }
    }

    /**
     * Mematikan tanpa membuka formulirnya — jalan yang disarankan Orcha.
     *
     * Barisnya dibaca ulang dari Orcha, bukan dikirim lewat markah: dialog
     * konfirmasi bersama hanya membawa SATU argumen, dan merakit payload dari
     * markah berarti nilai apa pun di sana bisa ikut tersimpan.
     */
    /**
     * Mengunduh daftar vendor sebagai Excel.
     *
     * Yang diunduh MENGIKUTI saringan yang sedang aktif — berkas yang isinya
     * berbeda dari yang terlihat di layar adalah berkas yang menyesatkan.
     * Diambil tanpa halaman supaya yang terunduh seluruhnya, bukan lima
     * belas baris pertama.
     */
    public function unduhExcel()
    {
        try {
            $hasil = $this->orcha()->ambil('/vendor', [
                'cari' => $this->cari,
                'jenis' => $this->filterJenis,
                'status' => $this->filterStatus2,
                'provinsi' => $this->filterProvinsi,
                'per_halaman' => 500,
            ]);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return null;
        }

        $baris = $hasil['data'] ?? [];

        if ($baris === []) {
            $this->dispatch('toast-error', message: 'Tidak ada vendor yang cocok dengan saringannya.');

            return null;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\OrchaVendorExport($baris),
            'vendor-orcha-'.now()->format('Ymd-Hi').'.xlsx',
        );
    }

    public function render()
    {
        $hasil = $this->muat('/vendor', $this->parameterDaftar([
            'jenis' => $this->filterJenis,
            'status' => $this->filterStatus2,
            'provinsi' => $this->filterProvinsi,
        ]));

        return view('livewire.pages.admin.orcha.vendor.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanJenis' => self::JENIS,
            'provinsiAda' => $hasil['meta']['ringkas']['provinsi'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
