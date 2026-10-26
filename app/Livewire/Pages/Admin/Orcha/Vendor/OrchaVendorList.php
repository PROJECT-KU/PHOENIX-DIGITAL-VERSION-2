<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
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

    public bool $tambah = false;

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
        $this->isian = ['nama' => '', 'kontak' => '', 'jenis' => '', 'catatan' => ''];
        $this->aktif = true;
    }

    public function mount(): void
    {
        $this->kosongkan();
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

    public function hapus(int $id): void
    {
        if ($this->hapusData("/vendor/{$id}", 'Vendor dihapus. Harga yang memakainya tetap ada.')) {
            cache()->forget('orcha.rab.katalog');
        }
    }

    public function render()
    {
        $hasil = $this->muat('/vendor', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.vendor.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanJenis' => self::JENIS,
        ])->layout('livewire.layout.templateindex');
    }
}
