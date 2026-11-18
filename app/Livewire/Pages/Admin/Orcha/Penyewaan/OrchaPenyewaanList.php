<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Daftar pemesanan sewa kendaraan.
 *
 * Kerangkanya mengikuti Pesanan Toko, sama seperti Pendaftaran Trip: tab
 * berbentuk kartu berangka, lalu satu bilah saringan, lalu tabelnya. Tiga
 * daftar yang dikerjakan orang yang sama tidak perlu dipelajari tiga kali.
 */
class OrchaPenyewaanList extends Component
{
    use MemanggilOrcha;

    /**
     * Saringan "belum kembali": unit yang sudah lewat tenggat dan belum
     * dicatat pulang.
     *
     * Inilah yang dicek admin tiap pagi, dan satu-satunya daftar di layar ini
     * yang uangnya bisa hilang kalau didiamkan — dendanya terus berjalan tanpa
     * pernah ditetapkan, dan unitnya tidak bisa disewakan lagi karena di
     * sistem masih dianggap ada di luar.
     */
    public bool $belumKembali = false;

    public function updatedBelumKembali(): void
    {
        $this->halaman = 1;
    }

    /**
     * Tab yang sedang dibuka.
     *
     * Status dan "belum kembali" tetap jadi keadaan sebenarnya di balik
     * layar — tab hanya cara memilihnya. Disimpan terpisah supaya tab yang
     * menyala tetap terlihat menyala walau keadaannya sama ("Semua" dan status
     * kosong adalah hal yang sama bagi peladen, tetapi bukan bagi mata).
     */
    public string $tabAktif = 'semua';

    /**
     | Tab yang tersedia: label, ikon, warna.
     |
     | Dikelompokkan dua, sama seperti Pendaftaran Trip: yang pertama CARA
     | MELIHAT daftar, yang kedua DAFTAR PEKERJAAN. Keduanya tidak boleh
     | berjajar begitu saja — "Belum kembali" yang duduk di antara status
     | tenggelam jadi salah satu pilihan menonton, padahal ia satu-satunya yang
     | menuntut tindakan hari ini.
     */
    public const TAB = [
        'semua' => ['Semua', 'bi-grid-fill', '#7c3aed'],
        'baru' => ['Baru masuk', 'bi-bell-fill', '#16a34a'],
        'dikonfirmasi' => ['Sudah dijawab', 'bi-chat-dots-fill', '#0284c7'],
        'dp_masuk' => ['Baru bayar DP', 'bi-cash-coin', '#d97706'],
        'berjalan' => ['Sedang dipakai', 'bi-truck', '#4f46e5'],
        'selesai' => ['Sudah selesai', 'bi-check-circle-fill', '#15803d'],
        'batal' => ['Batal', 'bi-x-circle-fill', '#e11d48'],
        'telat' => ['Belum kembali', 'bi-exclamation-triangle-fill', '#dc2626'],
    ];

    public const GRUP_TAB = [
        ['Keadaan pemesanan', 'Sudah sampai mana urusannya',
            ['semua', 'baru', 'dikonfirmasi', 'dp_masuk', 'berjalan', 'selesai', 'batal']],
        ['Perlu dikerjakan hari ini', 'Jangan didiamkan', ['telat']],
    ];

    /**
     * Pindah tab.
     *
     * Menerjemahkan satu pilihan jadi dua keadaan di balik layar. Admin tidak
     * perlu tahu bahwa "Belum kembali" sebenarnya saringan lain jenisnya
     * daripada "Sudah selesai".
     */
    public function setTab(string $tab): void
    {
        $this->tabAktif = array_key_exists($tab, self::TAB) ? $tab : 'semua';

        $this->belumKembali = $this->tabAktif === 'telat';
        $this->filterStatus = in_array($this->tabAktif, ['semua', 'telat'], true)
            ? ''
            : $this->tabAktif;

        $this->halaman = 1;
    }

    /**
     * Saringan ikut dikosongkan, termasuk tabnya.
     *
     * Menimpa milik trait: layar ini punya saringan di luar cari & status, dan
     * tombol yang menyisakan satu saringan hidup justru membingungkan —
     * daftarnya tetap terpotong padahal admin merasa sudah membersihkan
     * semuanya.
     */
    public function bersihkanSaringan(): void
    {
        $this->reset(['cari', 'filterStatus', 'belumKembali']);
        $this->tabAktif = 'semua';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->belumKembali;
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/penyewaan/{$id}/status", ['status' => $status], 'Status pemesanan sewa diperbarui di Orcha.');
    }

    public function render()
    {
        $hasil = $this->muat('/penyewaan', $this->parameterDaftar(array_filter([
            // Dikirim hanya saat menyala. Mengirim 0 membuat Orcha membaca
            // parameternya ada, dan daftar biasa ikut tersaring.
            'telat' => $this->belumKembali ? 1 : null,
        ], fn ($nilai) => $nilai !== null)));

        $meta = $hasil['meta'] ?? [];

        return view('livewire.pages.admin.orcha.penyewaan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $meta,
            'pilihanStatus' => $this->rujukan('status_penyewaan'),
            'tabs' => self::TAB,
            'grupTab' => self::GRUP_TAB,
            /*
             | Angka di tab. Dibaca dengan ?? : Orcha yang belum dideploy tidak
             | mengirim kunci ini sama sekali, dan tab tanpa angka masih
             | berguna — sedangkan halaman yang mati karenanya tidak.
             */
            'jumlahTab' => $meta['jumlah_tab'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
