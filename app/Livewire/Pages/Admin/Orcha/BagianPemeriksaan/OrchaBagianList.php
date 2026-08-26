<?php

namespace App\Livewire\Pages\Admin\Orcha\BagianPemeriksaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Services\OrchaTidakTerjangkau;
use Livewire\Component;

/**
 * Bagian kendaraan yang diperiksa saat serah terima.
 *
 * Dua belas bagian bawaan dulu dipatok di berkas config Orcha: menambah satu
 * bagian berarti mengubah kode dan men-deploy, dan pemilik armada yang mulai
 * menyewakan bus tidak punya cara menambahkan "pintu bagasi" sendiri.
 *
 * Yang dikelola di sini hanya BAGIANNYA. Tingkat kondisinya — baik, lecet,
 * rusak, hilang — tetap dikunci: itu skala berurutan, bukan daftar, dan
 * urutannya dipakai membandingkan keadaan sebelum dan sesudah pada seluruh
 * serah terima yang sudah tersimpan.
 */
class OrchaBagianList extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    /** Baris yang sedang disunting; null berarti formulirnya tertutup. */
    public ?int $sunting = null;

    public bool $tambah = false;

    public array $isian = [];

    /** @var array<int, string> */
    public array $jenisPilihan = [];

    /**
     * Keadaan aktif dipegang properti TERSENDIRI, bukan sebagai anggota $isian.
     *
     * Livewire tidak menandai atribut "checked" untuk wire:model yang menunjuk
     * anggota array — penandaan itu hanya diselaraskan oleh skripnya setelah
     * halaman hidup. Akibatnya sakelarnya tergambar mati padahal keadaannya
     * nyala, dan yang membacanya percaya pada gambarnya, bukan pada tulisan di
     * sebelahnya.
     */
    public bool $aktif = true;

    public function mount(): void
    {
        $this->kosongkan();
    }

    /**
     * Isian uang dipegang sebagai TEKS bertitik — "1.500.000", bukan 1500000.
     *
     * Formatnya dikerjakan di server saat isian ditinggalkan, mengikuti pola
     * yang sudah dipakai formulir armada: tidak perlu pustaka masking di
     * peramban, dan yang tampil sama persis dengan yang tersimpan. Angka
     * mentahnya dirakit lagi saat menyimpan, jadi titik pemisahnya tidak
     * pernah ikut terkirim ke Orcha.
     *
     * @var array<string, string>
     */
    public array $uang = ['biaya_lecet' => '', 'biaya_rusak' => '', 'biaya_hilang' => ''];

    private function kosongkan(): void
    {
        $this->isian = ['label' => ''];
        $this->aktif = true;
        $this->uang = ['biaya_lecet' => '', 'biaya_rusak' => '', 'biaya_hilang' => ''];
        $this->jenisPilihan = [];
    }

    /** Dirapikan begitu isiannya ditinggalkan: "1500000" → "1.500.000". */
    public function updatedUang(): void
    {
        foreach ($this->uang as $medan => $teks) {
            $this->uang[$medan] = $this->teksUang($teks);
        }
    }

    /**
     * Format uang untuk layar ini — BUKAN keRupiah() milik trait bersama.
     *
     * keRupiah() sengaja mengosongkan nol, karena di formulir armada nol
     * berarti "tarif ini tidak dijual" dan menampilkan "0" di sana hanya
     * mengganggu. Di sini nol justru nilai yang berarti: tarif perbaikan
     * memang bisa nol, dan ketikan itu WAJIB ada supaya bagian tanpa tarif
     * tidak lolos diam-diam. Kalau nolnya ikut dikosongkan, ketikan admin
     * lenyap sendiri lalu ditolak sebagai kosong pada tekan berikutnya.
     *
     * Kotak yang benar-benar dikosongkan tetap dibiarkan kosong, supaya
     * penjagaan "required" masih punya yang ditangkap.
     */
    private function teksUang(?string $teks): string
    {
        return trim((string) $teks) === ''
            ? ''
            : number_format($this->angkaDari($teks), 0, ',', '.');
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

        $this->isian = ['label' => $baris['label']];
        $this->aktif = (bool) $baris['aktif'];

        foreach (array_keys($this->uang) as $medan) {
            // (string) agar nol tersimpan tetap tergambar "0", bukan kosong —
            // lalu ditolak sebagai kosong saat admin menekan simpan.
            $this->uang[$medan] = $this->teksUang((string) ($baris[$medan] ?? 0));
        }

        $this->jenisPilihan = $baris['jenis'];
    }

    public function tutup(): void
    {
        $this->sunting = null;
        $this->tambah = false;
        $this->kosongkan();
    }

    public function simpan(): void
    {
        /*
         | Tarif WAJIB, walau boleh nol.
         |
         | Bagian tanpa tarif membuat usulan denda kerusakan diam-diam
         | melewatinya: perhitungannya tetap jalan, angkanya kurang, dan tidak
         | ada yang memberi tahu. Nol yang ditulis sadar berbeda dari nol yang
         | muncul karena lupa — karena itu yang dituntut "required", bukan
         | sekadar "boleh nol".
         |
         | Yang diperiksa teksnya, bukan angkanya: kotak kosong dan kotak
         | berisi "0" sama-sama menghasilkan angka 0 setelah dirakit, dan
         | keduanya harus dibedakan.
         */
        $this->validate([
            'isian.label' => 'required|string|max:120',
            'uang.biaya_lecet' => 'required|string',
            'uang.biaya_rusak' => 'required|string',
            'uang.biaya_hilang' => 'required|string',
            // Bagian tanpa jenis tidak akan pernah muncul di formulir siapa
            // pun, dan yang menyimpannya mengira ia sudah terpasang.
            'jenisPilihan' => 'required|array|min:1',
        ], [], [
            'isian.label' => 'nama bagian',
            'uang.biaya_lecet' => 'biaya lecet',
            'uang.biaya_rusak' => 'biaya rusak',
            'uang.biaya_hilang' => 'biaya hilang',
            'jenisPilihan' => 'jenis kendaraan',
        ]);

        $data = $this->isian + [
            'jenis' => array_values($this->jenisPilihan),
            'aktif' => $this->aktif,
        ];

        // Titik pemisahnya dilepas di sini, jadi tidak pernah ikut ke Orcha.
        foreach ($this->uang as $medan => $teks) {
            $data[$medan] = $this->angkaDari($teks);
        }
        $menambah = ! $this->sunting;

        try {
            if ($this->sunting) {
                $this->orcha()->ubah("/bagian-pemeriksaan/{$this->sunting}", $data);
            } else {
                $this->orcha()->kirim('/bagian-pemeriksaan', $data);
            }

            // Daftar rujukan memuat bagian pemeriksaan; tanpa dilupakan,
            // formulir armada dan lembar serah terima masih memakai daftar lama
            // sampai simpanannya kedaluwarsa sendiri.
            cache()->forget('orcha.rujukan');

            /*
             | Daftarnya urut dari yang terbaru, jadi bagian baru mendarat di
             | HALAMAN SATU — bukan di halaman terakhir seperti dulu, ketika
             | urutannya masih mengikuti kolom `urutan`.
             |
             | Saringan yang sedang aktif ikut dilepas: bagian bus yang baru
             | ditambahkan sementara daftarnya sedang disaring "Mobil" tidak
             | akan terlihat, dan yang menambahkannya mengira simpanannya
             | gagal.
             */
            if ($menambah) {
                $this->cari = '';
                $this->filterStatus = '';
                $this->halaman = 1;
            }

            $this->tutup();
            $this->dispatch('order-updated', message: 'Bagian pemeriksaan disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapus(int $id): void
    {
        try {
            $this->orcha()->hapus("/bagian-pemeriksaan/{$id}");
            cache()->forget('orcha.rujukan');
            $this->dispatch('order-updated', message: 'Bagian pemeriksaan dihapus.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $parameter = $this->parameterDaftar(['jenis' => $this->filterStatus]);

        // Halaman ini menyaring per jenis unit, bukan status.
        unset($parameter['status']);

        $hasil = $this->muat('/bagian-pemeriksaan', $parameter);

        return view('livewire.pages.admin.orcha.bagian-pemeriksaan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'jenisKendaraan' => $hasil['meta']['jenis_kendaraan'] ?? [],
            'tingkatKondisi' => $hasil['meta']['kondisi_pemeriksaan'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
