<?php

namespace App\Livewire\Pages\Admin\Orcha\Promo;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Services\OrchaTidakTerjangkau;
use Livewire\Component;

/**
 * Tingkat potongan menurut jumlah peserta satu pendaftaran.
 *
 * Angka-angka ini yang paling sering diutak-atik — mengikuti musim liburan,
 * sisa kursi, dan tawaran pesaing. Selama masih di berkas config Orcha, tiap
 * perubahan kecil berarti menunggu ada yang menyunting kode dan menaikkannya
 * ke server.
 *
 * Yang dikelola di sini SYARAT dan KEUNTUNGANNYA. Cara memilih tingkat —
 * yang paling tinggi syaratnya menang, tidak bertumpuk — tetap dikunci di
 * Orcha: itu aturan hitung, bukan angka yang boleh berbeda-beda per promo,
 * dan membuatnya bisa diatur berarti dua tingkat bisa saling menimpa tanpa
 * ada yang bisa menjelaskan hasilnya kepada pelanggan.
 */
class OrchaPromoList extends Component
{
    use MemanggilOrcha;

    /** Baris yang sedang disunting; null berarti formulirnya tertutup. */
    public ?int $sunting = null;

    public bool $tambah = false;

    public array $isian = [];

    /**
     * Keadaan aktif dipegang properti TERSENDIRI, bukan anggota $isian.
     *
     * Livewire tidak menandai atribut "checked" untuk wire:model yang menunjuk
     * anggota array — penandaannya baru diselaraskan skrip setelah halaman
     * hidup. Akibatnya sakelarnya tergambar mati padahal keadaannya nyala.
     * Pola ini sudah dipakai halaman Bagian Pemeriksaan dengan alasan sama.
     */
    public bool $aktif = true;

    /**
     * Bentuk keuntungan yang dipilih: 'persen' atau 'gratis'.
     *
     * Dipilih LEBIH DULU, sebelum angkanya. Dulu kedua kotak angka tampil
     * berdampingan dan admin harus menyimpulkan sendiri bahwa hanya salah satu
     * yang perlu diisi — sebagian mengisi keduanya, sebagian tidak mengisi
     * satu pun lalu ditolak setelah menekan simpan.
     *
     * Dengan memilih dulu, isian yang tidak relevan tidak pernah muncul, dan
     * pertanyaan "ini diisi berapa" hanya datang sekali.
     */
    public string $jenis = 'persen';

    private function kosongkan(): void
    {
        $this->isian = [
            'min_peserta' => '',
            'potongan_persen' => '',
            'gratis_orang' => '',
        ];
        $this->jenis = 'persen';
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

        // Jenisnya disimpulkan dari isi barisnya: yang punya orang gratis
        // adalah tingkat gratis, sisanya tingkat potongan.
        $this->jenis = ($baris['gratis_orang'] ?? 0) > 0 ? 'gratis' : 'persen';

        $this->isian = [
            'min_peserta' => (string) $baris['min_peserta'],
            'potongan_persen' => (string) ($baris['potongan_persen'] ?? ''),
            'gratis_orang' => (string) ($baris['gratis_orang'] ?? ''),
        ];

        $this->aktif = (bool) $baris['aktif'];
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
         | Yang divalidasi hanya isian yang RELEVAN dengan jenis terpilih.
         |
         | Menuntut keduanya membuat tingkat potongan ditolak karena "orang
         | gratis kosong" — padahal kotaknya memang tidak ditampilkan.
         */
        $aturan = ['isian.min_peserta' => 'required|integer|min:2|max:100'];
        $nama = ['isian.min_peserta' => 'minimal peserta'];

        if ($this->jenis === 'gratis') {
            $aturan['isian.gratis_orang'] = 'required|integer|min:1|max:20';
            $nama['isian.gratis_orang'] = 'jumlah orang gratis';
        } else {
            $aturan['isian.potongan_persen'] = 'required|integer|min:1|max:100';
            $nama['isian.potongan_persen'] = 'potongan persen';
        }

        $this->validate($aturan, [], $nama);

        /*
         | Isian jenis yang TIDAK dipilih dikirim nol, bukan dibiarkan apa
         | adanya.
         |
         | Admin yang mengubah tingkat dari "gratis 1 orang" jadi "potongan 5%"
         | meninggalkan angka 1 di kotak yang sudah tersembunyi. Tanpa dinolkan,
         | tingkat itu tersimpan dengan KEDUANYA — dan Orcha memilih yang gratis
         | lebih dulu, sehingga perubahannya seolah tidak berpengaruh.
         */
        $data = [
            'min_peserta' => (int) $this->isian['min_peserta'],
            'potongan_persen' => $this->jenis === 'persen' ? (int) $this->isian['potongan_persen'] : 0,
            'gratis_orang' => $this->jenis === 'gratis' ? (int) $this->isian['gratis_orang'] : 0,
            'aktif' => $this->aktif,
        ];

        try {
            if ($this->sunting) {
                $this->orcha()->ubah("/promo-rombongan/{$this->sunting}", $data);
            } else {
                $this->orcha()->kirim('/promo-rombongan', $data);
            }

            $this->tutup();
            $this->dispatch('order-updated', message: 'Tingkat promo disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function hapus(int $id): void
    {
        try {
            $this->orcha()->hapus("/promo-rombongan/{$id}");
            $this->dispatch('order-updated', message: 'Tingkat promo dihapus.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $hasil = $this->muat('/promo-rombongan');

        return view('livewire.pages.admin.orcha.promo.index', [
            'daftar' => $hasil['data'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
