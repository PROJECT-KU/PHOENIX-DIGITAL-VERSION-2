<?php

namespace App\Livewire\Pages\Admin\Orcha\JejakAudit;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Illuminate\Support\Str;
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
        '7-hari' => ['7 hari terakhir', 6],
        '30-hari' => ['30 hari terakhir', 29],
    ];

    /**
     * Pilihan "Waktu" — SATU kotak yang menggantikan dua medan tanggal plus
     * sederet tombol pintasan.
     *
     * Sebelumnya admin dihadapkan pada "Dari", "Sampai", dan tiga tombol
     * rentang cepat sekaligus: lima benda untuk satu pertanyaan, dan dua di
     * antaranya menuntut ia tahu tanggal berapa tujuh hari lalu. Kini
     * tanggalnya hanya muncul kalau ia memang memilih menentukannya sendiri.
     *
     * TIDAK ikut di alamat: yang dibagikan tetap dari/sampai, dan nilai kotak
     * ini dirakit ulang dari keduanya saat halaman dibuka. Dua sumber
     * kebenaran untuk satu rentang hanya akan berselisih.
     */
    public string $rentang = '';

    public const PILIH_SENDIRI = 'pilih';

    /**
     * Warna label rincian — satu jenis medan, satu warna.
     *
     * Semuanya dipilih cukup gelap untuk dibaca di atas putih, dan sengaja
     * TIDAK memuat ungu kata penghubung (#7c3aed): dua peran yang berbeda
     * tidak boleh memakai warna yang sama di baris yang sama.
     */
    public const WARNA_LABEL = [
        '#0e7490', '#4338ca', '#b45309', '#047857', '#be185d', '#0369a1', '#7e22ce',
    ];

    /**
     * Panel saringan selebihnya: tanggal persis, jenis perubahan, alamat IP.
     *
     * Dipegang komponen, bukan Alpine di tampilan. Yang membukanya bukan cuma
     * tombolnya: memilih "Pilih tanggal sendiri" di kotak Waktu juga harus
     * membukanya, dan keadaan yang bisa diubah dari dua sisi lebih baik
     * dipegang satu pihak daripada dititipkan ke penonton.
     */
    public bool $lainnya = false;

    /**
     * Merakit ulang keadaan kotak "Waktu" dari tanggal di alamat.
     *
     * Telusuran yang dikirim ke admin lain harus terbuka dengan kotak yang
     * menyebut hal yang sama — bukan "Kapan saja" sementara daftarnya jelas
     * tersaring, yang membuat penerimanya mengira datanya hilang.
     */
    public function mount(): void
    {
        foreach (self::RENTANG_CEPAT as $kunci => [, $mundur]) {
            if ($this->dari === now()->subDays($mundur)->toDateString()
                && $this->sampai === now()->toDateString()) {
                $this->rentang = $kunci;
                break;
            }
        }

        if ($this->rentang === '' && ($this->dari !== '' || $this->sampai !== '')) {
            $this->rentang = self::PILIH_SENDIRI;
        }

        // Saringan yang sedang aktif WAJIB ikut terlihat: daftar yang pendek
        // tanpa sebab yang terlihat adalah data yang hilang, bagi yang
        // membukanya lewat tautan dari orang lain.
        $this->lainnya = $this->jumlahSaringanLanjut() > 0 || $this->rentang === self::PILIH_SENDIRI;
    }

    /** Kotak "Waktu" dipilih: isi, kosongkan, atau serahkan ke dua medan tanggal. */
    public function updatedRentang(string $nilai): void
    {
        $this->halaman = 1;

        if ($nilai === self::PILIH_SENDIRI) {
            // Medannya ada di dalam panel; memilihnya tanpa membuka panel
            // berarti menyuruh admin mencari isian yang baru saja ia minta.
            $this->lainnya = true;

            return;
        }

        if ($nilai === '') {
            $this->dari = '';
            $this->sampai = '';

            return;
        }

        $this->rentangCepat($nilai);
    }

    public function updatedFilterAdmin(): void
    {
        $this->halaman = 1;
    }

    public function updatedDari(): void
    {
        $this->rentang = self::PILIH_SENDIRI;
        $this->halaman = 1;
    }

    public function updatedSampai(): void
    {
        $this->rentang = self::PILIH_SENDIRI;
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
        $this->rentang = $kunci;
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->reset(['filterAdmin', 'filterAksi', 'filterIp', 'dari', 'sampai', 'cari', 'rentang', 'lainnya']);
        $this->halaman = 1;
    }

    /**
     * Jumlah saringan yang TERSEMBUNYI di balik tombolnya.
     *
     * Hanya yang tersembunyi: lencana yang ikut menghitung saringan yang sudah
     * terpampang di layar menyuruh admin mencari sesuatu yang sedang
     * dilihatnya. Penamaannya menyamai daftar pesanan, yang panel lanjutannya
     * bekerja persis seperti ini.
     */
    public function jumlahSaringanLanjut(): int
    {
        return collect([$this->filterAksi, $this->filterIp])
            ->filter(fn ($n) => $n !== '')
            ->count();
    }

    /** Ada saringan apa pun yang aktif — penentu munculnya tombol pengosong. */
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
     * Nama manusiawi untuk nilai mesin yang dicatat jejaknya.
     *
     * Yang disimpan Orcha nilai mesinnya — `dp_masuk`, `private_trip` — dan
     * itulah yang dulu ikut tercetak apa adanya. Admin membaca "dp_masuk" di
     * layar sementara di layar sebelah hal yang sama bernama "DP Masuk"; garis
     * bawah dan huruf kecil itu bocoran isi basis data, bukan bahasa orang.
     *
     * Petanya diambil dari rujukan Orcha, bukan daftar salin-tempel di sini:
     * nilai baru akan ikut bernama benar tanpa ada yang perlu ingat
     * memperbaruinya di dua tempat.
     *
     * @return array<string, string>
     */
    private function petaNilai(): array
    {
        $peta = [];

        $grup = [
            'status_pendaftaran', 'status_penyewaan', 'status_pembatalan', 'status_pembayaran',
            'jenis_pembayaran', 'kategori_paket', 'jenis_kendaraan',
        ];

        foreach ($grup as $nama) {
            foreach ($this->rujukan($nama) as $kunci => $label) {
                // Sebagian rujukan isinya bersarang (katalog kendaraan);
                // yang bukan pasangan teks-ke-teks dilewati, bukan dipaksakan.
                if (is_string($kunci) && is_string($label) && $label !== '') {
                    $peta[$kunci] = $label;
                }
            }
        }

        return $peta;
    }

    /**
     * Keterangan yang benar-benar baru, tanpa mengulang nama aksinya.
     *
     * Ringkasan dari Orcha hampir selalu diawali kalimat yang SAMA dengan
     * aksinya, lalu mengulang perpindahan status di dalam kurung — sehingga
     * satu baris terbaca dua kali ("ubah status pembayaran" lalu "ubah status
     * pembayaran (menunggu → diterima)").
     */
    private function keteranganBaru(string $ringkasan, string $aksi): string
    {
        $sisa = trim($ringkasan);

        if ($aksi !== '' && Str::startsWith(mb_strtolower($sisa), mb_strtolower($aksi))) {
            $sisa = trim(mb_substr($sisa, mb_strlen($aksi)));
            $sisa = trim(preg_replace('/^\([^)]*\)/u', '', $sisa));
            $sisa = trim($sisa, "—- \t");
        }

        return $sisa;
    }

    /**
     * Memecah keterangan "kunci: nilai, kunci: nilai" jadi rincian yang dibaca.
     *
     * Apa adanya, isinya terbaca seperti potongan berkas log yang tak sengaja
     * ikut tercetak: "paket: Private Trip Premium, kategori: private_trip,
     * jumlah_peserta: 3". Yang dibaca admin seharusnya kalimat, bukan nama
     * kolom dengan garis bawahnya.
     *
     * @return array<int, array{label: string, nilai: string}>
     */
    public function rincian(?string $keterangan): array
    {
        $teks = trim((string) $keterangan);

        if ($teks === '') {
            return [];
        }

        $peta = $this->petaNilai();
        $rincian = [];

        foreach (explode(',', $teks) as $potong) {
            $potong = trim($potong);

            if ($potong === '') {
                continue;
            }

            /*
             | Koma yang ada DI DALAM nilai bukan pemisah rincian. Tanpa
             | penanganan ini "paket: Jogja, Solo" pecah jadi dua rincian, yang
             | kedua tanpa label — dan pembacanya tidak akan tahu bahwa yang
             | dilihatnya potongan dari nilai sebelumnya.
             */
            if (! str_contains($potong, ':')) {
                if ($rincian !== []) {
                    $rincian[array_key_last($rincian)]['nilai'] .= ', '.$potong;

                    continue;
                }

                $rincian[] = ['label' => '', 'nilai' => $potong];

                continue;
            }

            [$kunci, $nilai] = array_map('trim', explode(':', $potong, 2));

            $rincian[] = [
                'label' => $this->terbaca($kunci),
                'nilai' => $this->nilaiTerbaca($kunci, $nilai, $peta),
            ];
        }

        return $this->berwarna($rincian);
    }

    /**
     * Memberi tiap jenis medan warnanya sendiri.
     *
     * Satu warna untuk seluruh label membuat "Paket", "Kategori", "Jumlah
     * peserta" dan isinya mengalir jadi satu untaian; mata harus mengeja
     * untuk tahu di mana satu medan berakhir dan medan berikutnya mulai.
     *
     * Warnanya dari nama medannya — jadi "Paket" berwarna sama di seluruh
     * daftar, dan matanya belajar.
     *
     * Awalannya ("ja:") BUKAN hiasan: ia dipilih karena dengannya seluruh
     * nama medan yang benar-benar dipakai Orcha hari ini jatuh ke warna yang
     * berbeda-beda di tiap baris. Tanpa itu "Paket" dan "Pendamping gratis"
     * bersisa sama, yang belakangan tergeser, dan geseran itu merambat —
     * sehingga "Nama terdata" berwarna lain di baris yang kebetulan memuat
     * pendamping. Dua baris bersebelahan dengan medan sama berbeda warna
     * justru menghapus gunanya.
     *
     * Geseran tetap ada sebagai jaring pengaman, untuk nama medan yang baru
     * muncul nanti: di dalam satu baris, dapat dibedakan lebih penting
     * daripada tetap sama antarbaris. (Pelajaran dari penyebar warna avatar,
     * yang memakai crc32 % 6 dan membuat ketiga admin yang benar-benar ada
     * bersisa sama — penandanya tidak menandai apa pun.)
     *
     * @param  array<int, array{label: string, nilai: string}>  $rincian
     * @return array<int, array{label: string, nilai: string, warna: string}>
     */
    private function berwarna(array $rincian): array
    {
        $jumlah = count(self::WARNA_LABEL);
        $dipakai = [];

        foreach ($rincian as $i => $butir) {
            $mulai = $butir['label'] === ''
                ? 0
                : intval(substr(md5('ja:'.$butir['label']), 0, 8), 16) % $jumlah;

            $pilih = $mulai;

            for ($geser = 0; $geser < $jumlah; $geser++) {
                $calon = ($mulai + $geser) % $jumlah;

                if (! in_array($calon, $dipakai, true)) {
                    $pilih = $calon;
                    break;
                }
            }

            $dipakai[] = $pilih;
            $rincian[$i]['warna'] = self::WARNA_LABEL[$pilih];
        }

        return $rincian;
    }

    /** Nama kolom jadi kata: garis bawahnya spasi, huruf pertamanya besar. */
    private function terbaca(string $kata): string
    {
        return Str::ucfirst(trim(str_replace('_', ' ', $kata)));
    }

    private function nilaiTerbaca(string $kunci, string $nilai, array $peta): string
    {
        if ($nilai === '') {
            return '';
        }

        /*
         | Uang ditulis sebagai uang. "nominal: 1716000" menuntut pembacanya
         | menghitung sendiri ada berapa nol — dan satu nol terlewat pada
         | catatan audit pembayaran bukan salah baca yang kecil.
         |
         | Hanya bila KUNCInya memang menyebut uang dan nilainya memang angka
         | bulat; selebihnya dibiarkan apa adanya.
         */
        if (ctype_digit($nilai) && preg_match('/nominal|harga|biaya|total|denda|bayar/i', $kunci)) {
            return 'Rp '.number_format((int) $nilai, 0, ',', '.');
        }

        return $peta[$nilai] ?? $this->terbaca($nilai);
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

        /*
         | Isinya dirapikan sama seperti di layar.
         |
         | Yang diunduh dilampirkan ke laporan dan dibaca orang yang tidak
         | membuka lemon sama sekali — dan "kategori: private_trip" di lembar
         | Excel lebih buruk lagi daripada di layar, karena di sana tidak ada
         | tempat bertanya.
         */
        $peta = $this->petaNilai();

        $rapikan = fn (string $nilai) => $nilai === ''
            ? ''
            : ($peta[$nilai] ?? $this->terbaca($nilai));

        $keterangan = fn (array $b) => collect($this->rincian($this->keteranganBaru(
            (string) ($b['ringkasan'] ?? ''),
            trim((string) ($b['aksi'] ?? '')),
        )))->map(fn ($r) => trim($r['label'].' '.$r['nilai']))->implode(' · ');

        return response()->streamDownload(function () use ($baris, $rapikan, $keterangan) {
            $keluar = fopen('php://output', 'w');

            // BOM supaya Excel membaca é/— sebagai huruf, bukan sampah.
            fwrite($keluar, "\xEF\xBB\xBF");
            // escape disebut terang-terangan: PHP 8.4 memperingatkan nilai
            // bawaannya akan berubah, dan diam-diam ikut berubah berarti
            // berkas audit yang isinya berbeda tanpa ada yang meminta.
            fputcsv($keluar, ['Waktu', 'Admin', 'Aksi', 'Dari', 'Ke', 'Keterangan', 'Kode', 'IP'], escape: '\\');

            foreach ($baris as $b) {
                fputcsv($keluar, [
                    $b['waktu_teks'] ?? '',
                    $b['admin'] ?? '',
                    $b['aksi'] ?? '',
                    $rapikan((string) ($b['sebelum'] ?? '')),
                    $rapikan((string) ($b['sesudah'] ?? '')),
                    $keterangan($b),
                    $b['kode'] ?? '',
                    $b['ip'] ?? '',
                ], escape: '\\');
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

        /*
         | Rincian dirakit di sini, bukan di lembar tampilan.
         |
         | Memotong kalimat, memecah "kunci: nilai", dan menamai ulang nilainya
         | adalah pekerjaan mengolah data. Ditaruh di tampilan, ia jadi logika
         | yang tidak bisa diuji sendiri dan hanya ketahuan salah lewat
         | potongan HTML.
         */
        $daftar = collect($hasil['data'] ?? [])
            ->map(function ($jejak) {
                $jejak['rincian'] = $this->rincian($this->keteranganBaru(
                    (string) ($jejak['ringkasan'] ?? ''),
                    trim((string) ($jejak['aksi'] ?? '')),
                ));

                return $jejak;
            })
            ->all();

        return view('livewire.pages.admin.orcha.jejak-audit.index', [
            'daftar' => $daftar,
            'meta' => $hasil['meta'] ?? [],
            'pilihanAdmin' => $pilihanAdmin,
            'pilihanAksi' => $this->pilihanAksi(),
            'petaNilai' => $this->petaNilai(),
        ])->layout('livewire.layout.templateindex');
    }
}
