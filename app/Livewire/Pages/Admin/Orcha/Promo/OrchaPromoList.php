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

    private function kosongkan(): void
    {
        $this->isian = [
            'min_peserta' => '',
            'potongan_persen' => '',
            'gratis_orang' => '',
            'label' => '',
            'ajakan' => '',
        ];
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
            'min_peserta' => (string) $baris['min_peserta'],
            // (string) supaya nol tersimpan tetap tergambar "0", bukan kosong.
            'potongan_persen' => (string) ($baris['potongan_persen'] ?? 0),
            'gratis_orang' => (string) ($baris['gratis_orang'] ?? 0),
            'label' => (string) $baris['label'],
            'ajakan' => (string) ($baris['ajakan'] ?? ''),
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
        $this->validate([
            'isian.min_peserta' => 'required|integer|min:2|max:100',
            'isian.potongan_persen' => 'nullable|integer|min:0|max:100',
            'isian.gratis_orang' => 'nullable|integer|min:0|max:20',
            'isian.label' => 'required|string|max:120',
            'isian.ajakan' => 'nullable|string|max:160',
        ], [], [
            'isian.min_peserta' => 'minimal peserta',
            'isian.potongan_persen' => 'potongan persen',
            'isian.gratis_orang' => 'orang gratis',
            'isian.label' => 'tulisan promo',
            'isian.ajakan' => 'kalimat ajakan',
        ]);

        /*
         | Tingkat tanpa keuntungan apa pun ditahan DI SINI juga, bukan hanya
         | di Orcha.
         |
         | Orcha memang menolaknya, tetapi penolakan yang datang dari server
         | lain muncul sebagai pesan galat merah tanpa menunjuk isian mana yang
         | salah. Ditahan di sini, admin melihatnya menempel pada kotak yang
         | memang perlu diisinya.
         */
        if ((int) $this->isian['potongan_persen'] === 0 && (int) $this->isian['gratis_orang'] === 0) {
            $this->addError('isian.potongan_persen',
                'Isi potongan persen atau jumlah orang gratis — tingkat tanpa keduanya tidak mengubah harga apa pun.');

            return;
        }

        $data = [
            'min_peserta' => (int) $this->isian['min_peserta'],
            'potongan_persen' => (int) $this->isian['potongan_persen'],
            'gratis_orang' => (int) $this->isian['gratis_orang'],
            'label' => $this->isian['label'],
            'ajakan' => $this->isian['ajakan'] ?: null,
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
