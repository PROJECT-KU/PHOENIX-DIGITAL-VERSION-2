<?php

namespace App\Livewire\Pages\Admin\Orcha\Rujukan;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Kode rujukan alumni, dan komisi yang menyertainya.
 *
 * Bedanya dengan promo rombongan tegas. Promo rombongan berlaku dalam SATU
 * pendaftaran — ramai orang berangkat bersama di tanggal yang sama. Kode
 * rujukan berlaku LINTAS pendaftaran — orang yang sudah pulang mengajak
 * temannya ikut trip berikutnya, di tanggal yang berbeda.
 *
 * Layar ini melayani dua pekerjaan yang berbeda, dan yang kedua yang paling
 * sering dibuka:
 *
 *   1. Membuatkan kode untuk mitra atau alumni yang memintanya. Alumni biasa
 *      mendapatkannya sendiri lewat surat ajakan testimoni; yang diketik di
 *      sini biasanya reseller atau kenalan yang belum pernah ikut trip.
 *
 *   2. Membayarkan komisi. Tanpa layar ini, satu-satunya cara mengetahui
 *      komisi mana yang sudah dibayar adalah mengingatnya — dan yang menagih
 *      nanti orang yang merasa haknya belum diberikan, sambil kita tidak punya
 *      cara membuktikan sebaliknya.
 */
class OrchaRujukanList extends Component
{
    use MemanggilOrcha;

    public bool $tambah = false;

    public ?int $sunting = null;

    public array $isian = [];

    /**
     * Keadaan aktif dipegang properti TERSENDIRI, bukan anggota $isian.
     *
     * Livewire tidak menandai atribut "checked" untuk wire:model yang menunjuk
     * anggota array — penandaannya baru diselaraskan skrip setelah halaman
     * hidup, sehingga sakelarnya tergambar mati padahal keadaannya nyala. Pola
     * ini sudah dipakai halaman Promo dan Bagian Pemeriksaan dengan alasan
     * yang sama.
     */
    public bool $aktif = true;

    /** Kode yang sedang dibuka rincian pemakaiannya; null berarti tertutup. */
    public ?int $lihatPemakaian = null;

    public array $pemakaian = [];

    private function kosongkan(): void
    {
        $this->isian = ['nama' => '', 'whatsapp' => '', 'email' => '', 'catatan' => ''];
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
            'whatsapp' => (string) $baris['whatsapp'],
            'email' => (string) ($baris['email'] ?? ''),
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

    public function simpan(): void
    {
        $this->validate([
            'isian.nama' => 'required|string|min:2|max:120',
            'isian.whatsapp' => 'required|string|min:8|max:32',
            'isian.email' => 'nullable|email|max:150',
            'isian.catatan' => 'nullable|string|max:1000',
        ], [], [
            'isian.nama' => 'nama pemilik',
            'isian.whatsapp' => 'nomor WhatsApp',
            'isian.email' => 'email',
            'isian.catatan' => 'catatan',
        ]);

        $data = [
            'nama' => $this->isian['nama'],
            'whatsapp' => $this->isian['whatsapp'],
            'email' => $this->isian['email'] ?: null,
            'catatan' => $this->isian['catatan'] ?: null,
            'aktif' => $this->aktif,
        ];

        try {
            if ($this->sunting) {
                $this->orcha()->ubah("/kode-rujukan/{$this->sunting}", $data);
            } else {
                $this->orcha()->kirim('/kode-rujukan', $data);
            }

            $this->tutup();
            $this->dispatch('order-updated', message: 'Kode rujukan disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            /*
             | Penolakan "nomor ini sudah punya kode" ditempelkan ke kotaknya.
             |
             | Sebagai toast melayang, pesannya menyebut kode yang sudah ada
             | tetapi tidak menunjuk kotak mana yang harus diubah — dan admin
             | menekan Simpan berulang kali sambil menduga sambungannya
             | bermasalah.
             */
            if (str_contains(mb_strtolower($e->getMessage()), 'sudah punya kode')) {
                $this->addError('isian.whatsapp', $e->getMessage());

                return;
            }

            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bukaPemakaian(int $id): void
    {
        try {
            $this->pemakaian = $this->muat("/kode-rujukan/{$id}/pemakaian")['data'] ?? [];
            $this->lihatPemakaian = $id;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupPemakaian(): void
    {
        $this->lihatPemakaian = null;
        $this->pemakaian = [];
    }

    /**
     * Menandai satu imbalan sudah dibayarkan.
     *
     * Menandai, bukan membayar: uangnya berpindah lewat transfer di luar
     * sistem. Yang dicatat di sini pengakuan bahwa itu sudah terjadi — dan
     * pengakuan itulah yang selama ini hanya ada di ingatan seseorang.
     */
    public function bayar(int $pendaftaranId): void
    {
        try {
            $this->orcha()->kirim("/kode-rujukan/bayar/{$pendaftaranId}", []);

            if ($this->lihatPemakaian) {
                $this->bukaPemakaian($this->lihatPemakaian);
            }

            $this->dispatch('order-updated', message: 'Imbalan ditandai sudah dibayar.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();

        // Layar ini menyaring per kata, bukan per status pesanan.
        unset($parameter['status']);

        $hasil = $this->muat('/kode-rujukan', $parameter);

        return view('livewire.pages.admin.orcha.rujukan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
