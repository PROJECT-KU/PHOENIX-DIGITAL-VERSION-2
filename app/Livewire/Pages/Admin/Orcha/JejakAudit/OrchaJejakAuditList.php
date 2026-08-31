<?php

namespace App\Livewire\Pages\Admin\Orcha\JejakAudit;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Siapa mengubah apa, kapan — terbaca tanpa akses server.
 *
 * Jejaknya sudah lama dicatat di sisi Orcha, tetapi hanya ke berkas log:
 * tidak terbaca siapa pun tanpa SSH, berputar lalu terhapus, dan tidak bisa
 * disaring. Yang bertanya "siapa yang mengubah nominal pengembalian ini?"
 * adalah orang keuangan, bukan pemegang kunci server.
 *
 * Halaman ini HANYA membaca. Tidak ada tombol hapus, dan itu disengaja:
 * catatan audit yang bisa dihapus dari layar bukan catatan audit.
 */
class OrchaJejakAuditList extends Component
{
    use MemanggilOrcha;

    /**
     * Penyaring per admin, ikut di alamat.
     *
     * Ikut di alamat supaya hasil telusuran bisa dikirim apa adanya ke orang
     * lain — pertanyaan audit hampir selalu melibatkan lebih dari satu orang.
     */
    #[Url(as: 'admin', except: '')]
    public string $filterAdmin = '';

    #[Url(as: 'dari', except: '')]
    public string $dari = '';

    #[Url(as: 'sampai', except: '')]
    public string $sampai = '';

    public function updatedFilterAdmin(): void
    {
        $this->halaman = 1;
    }

    public function updatedDari(): void
    {
        $this->halaman = 1;
    }

    public function updatedSampai(): void
    {
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->reset(['filterAdmin', 'dari', 'sampai', 'cari']);
        $this->halaman = 1;
    }

    public function render()
    {
        $parameter = $this->parameterDaftar([
            'admin' => $this->filterAdmin ?: null,
            'dari' => $this->dari ?: null,
            'sampai' => $this->sampai ?: null,
        ]);

        // Halaman ini tidak punya status; penyaringnya admin dan tanggal.
        unset($parameter['status']);

        $hasil = $this->muat('/jejak-audit', $parameter);

        /*
         | Daftar nama admin diambil dari jejaknya sendiri, bukan dari daftar
         | pengguna lemon. Yang berguna di penyaring ini cuma nama yang
         | BENAR-BENAR pernah mengubah sesuatu; menampilkan seluruh pengguna
         | membuat sebagian besar pilihannya selalu berujung daftar kosong.
         |
         | Gagalnya diam: penyaring yang tidak terisi jauh lebih ringan
         | akibatnya daripada halaman jejak yang tidak bisa dibuka.
         */
        $pilihanAdmin = [];

        try {
            $pilihanAdmin = $this->orcha()->ambil('/jejak-audit/admin')['data'] ?? [];
        } catch (\Throwable) {
            $pilihanAdmin = [];
        }

        return view('livewire.pages.admin.orcha.jejak-audit.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanAdmin' => $pilihanAdmin,
        ])->layout('livewire.layout.templateindex');
    }
}
