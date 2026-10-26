<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
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

    #[Url(as: 'jenis', except: '')]
    public string $filterJenis = '';

    #[Url(as: 'status', except: '')]
    public string $filterStatus2 = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    public bool $tambah = false;

    /** Vendor yang sedang ditawarkan untuk digabungkan; null berarti tertutup. */
    public ?int $gabungDari = null;

    public string $gabungKe = '';

    /** Daftar master harga yang nomornya masih tertinggal di kolom catatan. */
    public bool $bukaTercecer = false;

    public array $tercecer = [];

    public ?int $sunting = null;

    public array $isian = [];

    /** Sakelar dipegang properti tersendiri — lihat OrchaRujukanList::$aktif. */
    public bool $aktif = true;

    /**
     * Jenis vendor yang paling sering dipakai.
     *
     * Ditawarkan sebagai pilihan cepat, bukan dikunci: vendor nyata tidak
     * selalu muat di lima kotak, dan menolak yang keenam hanya membuat admin
     * mengetiknya di kolom lain.
     */
    public const JENIS = ['Armada', 'Akomodasi', 'Konsumsi', 'Tiket', 'Pemandu'];

    private function kosongkan(): void
    {
        $this->isian = ['nama' => '', 'kontak' => '', 'jenis' => '', 'provinsi' => '', 'catatan' => ''];
        $this->aktif = true;
    }

    public function mount(): void
    {
        $this->kosongkan();
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
    public function nomorDari(?string $kontak): ?string
    {
        if (! preg_match('/\d[\d\s().+-]{6,}/', (string) $kontak, $cocok)) {
            return null;
        }

        $angka = preg_replace('/\D/', '', $cocok[0]);

        return strlen($angka) >= 7 ? $angka : null;
    }

    /** Bentuk 62… untuk WhatsApp; 0 di depan DIGANTI, bukan ditempeli. */
    public function nomorWa(?string $kontak): ?string
    {
        $angka = $this->nomorDari($kontak);

        if ($angka === null) {
            return null;
        }

        return str_starts_with($angka, '0') ? '62'.substr($angka, 1) : $angka;
    }

    /**
     * Nomor yang panjangnya di luar wajar.
     *
     * Bukan penolakan — kontaknya memang teks bebas, dan nomor kantor
     * berbeda panjang dari nomor seluler. Tetapi salah ketik satu digit
     * tetap tersimpan dengan yakin, dan tombol WhatsApp-nya mengarah ke
     * nomor orang lain tanpa satu pun tanda. Jadi ditandai, bukan ditolak.
     */
    public function nomorJanggal(?string $kontak): bool
    {
        $angka = $this->nomorDari($kontak);

        if ($angka === null) {
            return false;
        }

        $panjang = strlen($angka);

        return $panjang < 9 || $panjang > 15;
    }

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

        try {
            $this->tercecer = $this->orcha()->ambil('/vendor/kontak-tercecer')['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->tercecer = [];
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /** Catatan lama dipakai sebagai bahan vendor baru, bukan diketik ulang. */
    public function dariCatatan(string $catatan): void
    {
        $this->bukaTambah();
        $this->bukaTercecer = false;

        $nomor = $this->nomorDari($catatan);

        // Yang bukan nomor dianggap namanya: "PO Sumber Jaya 081234000111"
        // jadi nama "PO Sumber Jaya", kontaknya catatan apa adanya.
        $this->isian['nama'] = $nomor !== null
            ? trim(preg_replace('/\d[\d\s().+-]{6,}/', '', $catatan), " \t\n\r-,.:;|")
            : trim($catatan);
        $this->isian['kontak'] = trim($catatan);
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
            'nama' => (string) $baris['nama'],
            'kontak' => (string) ($baris['kontak'] ?? ''),
            'jenis' => (string) ($baris['jenis'] ?? ''),
            'provinsi' => (string) ($baris['provinsi'] ?? ''),
            'catatan' => (string) ($baris['catatan'] ?? ''),
        ];
        $this->aktif = (bool) $baris['aktif'];
    }

    public function tutup(): void
    {
        $this->sunting = null;
        $this->tambah = false;
        $this->kosongkan();
    }

    public function pakaiJenis(string $jenis): void
    {
        // Menekan jenis yang sedang terpilih melepaskannya — tanpa itu
        // jenis yang salah tekan tidak punya cara dibatalkan.
        $this->isian['jenis'] = trim((string) ($this->isian['jenis'] ?? '')) === $jenis ? '' : $jenis;
    }

    public function simpan(): void
    {
        $this->validate([
            'isian.nama' => 'required|string|min:2|max:120',
            'isian.kontak' => 'nullable|string|max:120',
            'isian.jenis' => 'nullable|string|max:30',
            'isian.provinsi' => 'nullable|string|max:80',
            'isian.catatan' => 'nullable|string|max:300',
        ], [
            'isian.nama.required' => 'Isi nama vendornya.',
        ], [
            'isian.nama' => 'nama', 'isian.kontak' => 'kontak',
        ]);

        $data = [
            'nama' => trim($this->isian['nama']),
            // Nomor TIDAK dipaksa berformat angka: "0812xxx (Pak Budi)"
            // justru bentuk yang paling berguna saat menelepon.
            'kontak' => trim($this->isian['kontak']) ?: null,
            'jenis' => trim($this->isian['jenis']) ?: null,
            // Kosong berarti BERLAKU DI MANA SAJA, bukan belum diisi.
            'provinsi' => trim($this->isian['provinsi']) ?: null,
            'catatan' => trim($this->isian['catatan']) ?: null,
            'aktif' => $this->aktif,
        ];

        try {
            $balasan = $this->sunting
                ? $this->orcha()->ubah("/vendor/{$this->sunting}", $data)
                : $this->orcha()->kirim('/vendor', $data);

            // Daftar vendor ikut disimpan di katalog RAB; tanpa dilupakan,
            // vendor yang baru dibuat tidak muncul di kotak pilih master
            // harga dan tampak seperti gagal tersimpan.
            cache()->forget('orcha.rab.katalog');

            $this->tutup();
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Vendor disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
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
    public function matikan(int $id): void
    {
        try {
            $v = $this->orcha()->ambil("/vendor/{$id}/rincian")['data']['vendor'] ?? null;

            if ($v === null) {
                $this->dispatch('toast-error', message: 'Vendornya tidak ditemukan.');

                return;
            }

            $this->orcha()->ubah("/vendor/{$id}", [
                'nama' => $v['nama'],
                'kontak' => $v['kontak'],
                'jenis' => $v['jenis'],
                'provinsi' => $v['provinsi'] ?? null,
                'catatan' => $v['catatan'],
                'aktif' => false,
            ]);

            cache()->forget('orcha.rab.katalog');
            $this->dispatch('order-updated', message: "{$v['nama']} dimatikan. Kontaknya tetap terbaca di RAB yang sudah memakainya.");
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

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
