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

    /**
     * Penyaring per JENIS aksi.
     *
     * Pertanyaan audit yang paling sering diajukan bukan "apa yang dilakukan
     * admin X", melainkan "siapa saja yang pernah menyentuh pembayaran".
     * Tanpa penyaring ini jawabannya hanya bisa dikira-kira lewat kata di
     * kotak cari.
     */
    #[Url(as: 'aksi', except: '')]
    public string $filterAksi = '';

    /** Alamat IP — untuk menjawab "adakah akses dari tempat tak dikenal". */
    #[Url(as: 'ip', except: '')]
    public string $filterIp = '';

    /** Baris per halaman; angka yang boleh dipilih ada di PILIHAN_BARIS. */
    #[Url(as: 'baris', except: 25)]
    public int $baris = 25;

    public const PILIHAN_BARIS = [10, 25, 50, 100];

    /** Rentang cepat: pertanyaan audit hampir selalu "pekan lalu", bukan tanggal persis. */
    public const RENTANG_CEPAT = [
        'hari-ini' => ['Hari ini', 0],
        '7-hari' => ['7 hari', 6],
        '30-hari' => ['30 hari', 29],
    ];

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

    public function updatedFilterAksi(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterIp(): void
    {
        $this->halaman = 1;
    }

    public function updatedBaris(): void
    {
        $this->halaman = 1;
    }

    /**
     * Isi rentang tanggal dari pintasan.
     *
     * Yang diisi tetap kedua medan tanggalnya — bukan keadaan tersembunyi —
     * supaya yang terpilih kelihatan, bisa disunting, dan ikut terbawa di
     * alamat saat telusurannya dikirim ke orang lain.
     */
    public function rentangCepat(string $kunci): void
    {
        if (! isset(self::RENTANG_CEPAT[$kunci])) {
            return;
        }

        [, $mundur] = self::RENTANG_CEPAT[$kunci];

        $this->dari = now()->subDays($mundur)->toDateString();
        $this->sampai = now()->toDateString();
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->reset(['filterAdmin', 'filterAksi', 'filterIp', 'dari', 'sampai', 'cari']);
        $this->halaman = 1;
    }

    /** Jumlah saringan yang sedang aktif — dipakai lencana di tombolnya. */
    public function jumlahSaringan(): int
    {
        return collect([$this->cari, $this->filterAdmin, $this->filterAksi, $this->filterIp, $this->dari, $this->sampai])
            ->filter(fn ($n) => $n !== '')
            ->count();
    }

    protected function perHalaman(): int
    {
        return in_array($this->baris, self::PILIHAN_BARIS, true) ? $this->baris : 25;
    }

    /**
     * Daftar jenis aksi untuk penyaringnya.
     *
     * Gagalnya DIAM, sama seperti daftar admin: penyaring yang tidak terisi
     * jauh lebih ringan akibatnya daripada halaman jejak yang tidak terbuka.
     */
    private function pilihanAksi(): array
    {
        try {
            return $this->hanyaTeks($this->orcha()->ambil('/jejak-audit/aksi')['data'] ?? []);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Sisakan yang benar-benar teks.
     *
     * Balasan API yang bentuknya lain — mis. daftar baris, bukan daftar nama —
     * dulu merobohkan halaman dengan TypeError di htmlspecialchars(), jadi
     * penyaring yang isinya aneh menjatuhkan SELURUH jejaknya. Lebih baik
     * penyaringnya kosong daripada halamannya tidak bisa dibuka.
     *
     * @param  mixed  $daftar
     */
    private function hanyaTeks($daftar): array
    {
        return collect(is_array($daftar) ? $daftar : [])
            ->filter(fn ($n) => is_string($n) && $n !== '')
            ->values()
            ->all();
    }

    /**
     * Unduh hasil telusuran sebagai CSV.
     *
     * Yang diunduh SELURUH hasil saringan, bukan halaman yang sedang terlihat:
     * audit yang dilampirkan ke laporan tidak boleh terpotong di baris ke-25
     * tanpa ada yang menyadarinya. Batasnya 5.000 baris — cukup untuk
     * pertanyaan audit mana pun, dan tetap aman untuk satu permintaan HTTP.
     */
    public function unduhCsv()
    {
        $parameter = $this->parameterDaftar([
            'admin' => $this->filterAdmin ?: null,
            'aksi' => $this->filterAksi ?: null,
            'ip' => $this->filterIp ?: null,
            'dari' => $this->dari ?: null,
            'sampai' => $this->sampai ?: null,
        ]);

        unset($parameter['status']);
        $parameter['page'] = 1;
        $parameter['per_halaman'] = 5000;

        try {
            $baris = $this->orcha()->ambil('/jejak-audit', $parameter)['data'] ?? [];
        } catch (\Throwable $e) {
            $this->galat = $e->getMessage();

            return null;
        }

        $nama = 'jejak-audit-orcha-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($baris) {
            $keluar = fopen('php://output', 'w');

            // BOM supaya Excel membaca é/— sebagai huruf, bukan sampah.
            fwrite($keluar, "\xEF\xBB\xBF");
            fputcsv($keluar, ['Waktu', 'Admin', 'Aksi', 'Dari', 'Ke', 'Keterangan', 'Kode', 'IP']);

            foreach ($baris as $b) {
                fputcsv($keluar, [
                    $b['waktu_teks'] ?? '',
                    $b['admin'] ?? '',
                    $b['aksi'] ?? '',
                    $b['sebelum'] ?? '',
                    $b['sesudah'] ?? '',
                    $b['ringkasan'] ?? '',
                    $b['kode'] ?? '',
                    $b['ip'] ?? '',
                ]);
            }

            fclose($keluar);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        $parameter = $this->parameterDaftar([
            'admin' => $this->filterAdmin ?: null,
            'aksi' => $this->filterAksi ?: null,
            'ip' => $this->filterIp ?: null,
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
            $pilihanAdmin = $this->hanyaTeks($this->orcha()->ambil('/jejak-audit/admin')['data'] ?? []);
        } catch (\Throwable) {
            $pilihanAdmin = [];
        }

        return view('livewire.pages.admin.orcha.jejak-audit.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanAdmin' => $pilihanAdmin,
            'pilihanAksi' => $this->pilihanAksi(),
        ])->layout('livewire.layout.templateindex');
    }
}
