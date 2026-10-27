<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Penyusun RAB: itinerary di kiri, angka di kanan, keduanya bergerak bersama.
 *
 * Setiap klik langsung tersimpan ke Orcha dan layar digambar ulang dari
 * jawabannya — bukan dari hitungan di sini. Hitungan RAB hanya ada di satu
 * tempat (HitungRab di Orcha); menghitung ulang di lemon berarti suatu hari
 * layar dan PDF menyebut harga yang berbeda.
 */
class OrchaRabSusun extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    public int $rabId;

    /**
     * Bentuk RAB terakhir dari Orcha — satu-satunya sumber angka di layar.
     *
     * Bukan $rab: nama itu milik parameter rute, dan Livewire mengisi
     * properti bernama sama dengan angka id-nya.
     */
    public array $dataRab = [];

    /** Destinasi, master harga, dan paket untuk provinsi RAB ini. */
    public array $katalog = [];

    /** Salinan itinerary yang bisa disunting; disimpan utuh tiap berubah. */
    public array $itinerary = [];

    public int $hariAktif = 1;

    public string $cariDestinasi = '';

    public string $kegiatanBaru = '';

    public string $marginJenis = 'persen';

    public string $marginNilai = '';

    public int $pembulatan = 1000;

    /** Harga & jumlah per baris biaya, dikunci id barisnya. */
    public array $hargaBaris = [];

    public array $jumlahBaris = [];

    public string $pilihMaster = '';

    public bool $bukaManual = false;

    public array $manual = [];

    public bool $bukaKepala = false;

    public array $kepala = [];

    /*
     | Salinan kepala seperti saat dimuat.
     |
     | Panel ini hanya tersimpan saat tombol Simpan ditekan — menutupnya
     | atau berpindah halaman membuang perubahannya tanpa sepatah kata.
     | Pembandingnya dipegang di sini supaya layar bisa mengatakan "belum
     | disimpan" alih-alih membiarkannya hilang diam-diam.
     */
    public array $kepalaAwal = [];

    /*
     | Tiket yang baru dihapus sementara tempatnya MASIH ada di itinerary.
     |
     | Tanpa tanda, ia akan tertarik lagi pada "Tarik ulang" berikutnya —
     | penghapusan yang hidup lagi tanpa ada yang menyadarinya.
     */
    public string $tiketTertinggal = '';

    /*
     | Kegiatan yang baru saja dihapus, disimpan untuk dikembalikan.
     |
     | Satu kegiatan bisa berisi jam, keterangan, dan destinasi yang
     | diketik tangan. Dialog konfirmasi menahan kesalahan yang disadari;
     | yang tidak disadari — klik meleset di baris sebelahnya — hanya bisa
     | ditolong oleh urungkan.
     */
    public array $kegiatanTerhapus = [];

    /** Semua hari ditampilkan sekaligus, bukan satu tab per hari. */
    public bool $semuaHari = false;

    /** Destinasi di itinerary yang belum punya harga tiket di master. */
    public array $tanpaTiket = [];

    public string $paketId = '';

    /**
     * Persetujuan sadar untuk tetap berangkat di bawah titik impas.
     *
     * Tidak pernah menyala sendiri: yang menekan "Jadikan Pendaftaran"
     * biasanya sedang mengejar tenggat, dan centang yang sudah tercentang
     * lebih dulu bukan persetujuan — ia sekadar penghalang yang sudah
     * disingkirkan untuknya.
     */
    public bool $abaikanImpas = false;

    public function mount(int $rab): void
    {
        $this->rabId = $rab;
        $this->kosongkanManual();

        try {
            $this->terapkan($this->orcha()->ambil("/rab/{$rab}")['data'] ?? []);
            $this->katalog = $this->orcha()->ambil('/rab/katalog', ['provinsi' => $this->dataRab['provinsi'] ?? ''])['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
        }
    }

    /** Menyalin jawaban Orcha ke seluruh isian layar. */
    private function terapkan(array $data): void
    {
        if ($data === []) {
            return;
        }

        $this->dataRab = $data;
        $this->itinerary = collect($data['itinerary'] ?? [])->map(fn ($k) => [
            'hari_ke' => (int) $k['hari_ke'],
            'jam' => (string) ($k['jam'] ?? ''),
            'jam_selesai' => (string) ($k['jam_selesai'] ?? ''),
            // Lamanya datang dari Orcha, tidak dihitung lagi di sini:
            // hitungan yang diulang di dua tempat akan berbeda suatu hari.
            'durasi_teks' => (string) ($k['durasi_teks'] ?? ''),
            /*
             | Jenis kegiatan datang dari Orcha, tidak ditebak di sini.
             | Penggolongnya satu untuk layar dan kedua PDF — dua tebakan
             | untuk perjalanan yang sama suatu hari akan berselisih.
             */
            'jenis' => (string) ($k['jenis'] ?? 'lain'),
            'nama' => (string) $k['nama'],
            'keterangan' => (string) ($k['keterangan'] ?? ''),
            'destinasi' => (string) ($k['destinasi'] ?? ''),
        ])->values()->all();

        $this->marginJenis = (string) $data['margin_jenis'];
        $this->marginNilai = $data['margin_jenis'] === 'persen'
            ? (string) $data['margin_nilai']
            : $this->keRupiah($data['margin_nilai']);
        $this->pembulatan = (int) $data['pembulatan'];
        $this->hariAktif = min(max(1, $this->hariAktif), max(1, (int) $data['jumlah_hari']));

        $this->hargaBaris = [];
        $this->jumlahBaris = [];
        foreach ($data['ringkasan']['baris'] ?? [] as $b) {
            $this->hargaBaris[$b['id']] = $this->keRupiah($b['harga_satuan']) ?: '0';
            $this->jumlahBaris[$b['id']] = (string) $b['jumlah'];
        }

        $this->kepala = [
            'judul' => (string) $data['judul'],
            'nama_pelanggan' => (string) $data['nama_pelanggan'],
            'whatsapp' => (string) ($data['whatsapp'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'daerah' => (string) ($data['daerah'] ?? ''),
            'tanggal_mulai' => (string) ($data['tanggal_mulai'] ?? ''),
            'jumlah_hari' => (int) $data['jumlah_hari'],
            'jumlah_malam' => (int) $data['jumlah_malam'],
            'jumlah_peserta' => (int) $data['jumlah_peserta'],
            'berlaku_sampai' => (string) ($data['berlaku_sampai'] ?? ''),
            'catatan_penawaran' => (string) ($data['catatan_penawaran'] ?? ''),
            'catatan' => (string) ($data['catatan'] ?? ''),
        ];

        $this->kepalaAwal = $this->kepala;
    }

    /** Ada perubahan di panel data perjalanan yang belum dikirim. */
    public function kepalaBerubah(): bool
    {
        return $this->kepalaAwal !== [] && $this->kepala !== $this->kepalaAwal;
    }

    /** Menutup panel kepala, membuang perubahan yang belum disimpan. */
    public function batalKepala(): void
    {
        $this->kepala = $this->kepalaAwal;
        $this->bukaKepala = false;
        $this->resetValidation();
    }

    /**
     * Satu pintu untuk setiap perubahan: kirim, lalu gambar ulang dari
     * jawabannya. Gagal berarti layar dimuat ulang dari Orcha, supaya yang
     * terlihat tidak pernah angka yang sebenarnya tidak tersimpan.
     */
    private function kirimKe(string $cara, string $jalur, array $data = [], ?string $pesan = null): ?array
    {
        try {
            $balasan = match ($cara) {
                'ubah' => $this->orcha()->ubah($jalur, $data),
                'kirim' => $this->orcha()->kirim($jalur, $data),
                'hapus' => $this->orcha()->hapus($jalur),
            };
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
            $this->muatUlang();

            return null;
        }

        $this->terapkan($balasan['data'] ?? []);

        if ($pesan) {
            $this->dispatch('order-updated', message: $pesan);
        }

        return $balasan;
    }

    private function muatUlang(): void
    {
        try {
            $this->terapkan($this->orcha()->ambil("/rab/{$this->rabId}")['data'] ?? []);
        } catch (OrchaTidakTerjangkau) {
            // Pesan galatnya sudah tampil lewat toast.
        }
    }

    /* ------------------------------ ITINERARY ------------------------------ */

    public function pilihHari(int $hari): void
    {
        $this->hariAktif = max(1, min($hari, (int) ($this->dataRab['jumlah_hari'] ?? 1)));
    }

    /**
     * Kegiatan yang muncul di hampir setiap itinerary.
     *
     * Bukan destinasi — tidak ada tiketnya, tidak ada di master harga — tetapi
     * selalu ada di jadwal, dan selama ini diketik ulang kata demi kata pada
     * tiap RAB. Satu klik mengganti sekitar lima belas ketukan, dan kalimatnya
     * jadi sama di semua penawaran.
     */
    public const KEGIATAN_BAKU = [
        'Penjemputan peserta',
        'Sarapan',
        'Makan siang',
        'Check-in hotel',
        'Makan malam',
        'Acara bebas',
        'Check-out hotel',
        'Perjalanan pulang',
    ];

    public function tambahDestinasi(string $nama): void
    {
        $mulai = $this->jamBerikutnya($this->hariAktif);

        $this->sisipkan([
            'hari_ke' => $this->hariAktif,
            'jam' => $mulai,
            'jam_selesai' => $this->tambahJam($mulai, 120),
            'nama' => $nama,
            'keterangan' => '',
            'destinasi' => $nama,
            // Sementara, sampai jawaban penyimpanan membawa jenis dari Orcha.
            'jenis' => 'destinasi',
        ]);

        /*
         | Biayanya ditarik sekalian.
         |
         | Menambahkan destinasi dan menarik biayanya adalah satu maksud, bukan
         | dua: tidak ada yang memasukkan Candi Prambanan ke itinerary lalu
         | bermaksud TIDAK menghitung tiketnya. Dulu dua tekanan tombol, dan
         | yang lupa menekan tombol kedua mengirim penawaran dengan modal yang
         | kurang — kesalahan yang baru ketahuan saat uangnya kurang.
         |
         | Aman diulang: penarikan melewati master harga yang sudah ada di RAB.
         */
        $this->tarikBiaya(diam: true);

        $this->isiDaerahDari($nama);
    }

    /**
     * Kota RAB terisi dari destinasi pertama yang dipilih.
     *
     * Kotanya sudah diketahui — ia melekat pada destinasi di katalog. Dibiarkan
     * kosong, ia muncul sebagai provinsi di daftar RAB dan di kepala penawaran
     * ("DI Yogyakarta" alih-alih "Sleman"), lalu diisi tangan belakangan oleh
     * orang yang menyadarinya. Yang sudah diisi TIDAK ditimpa: pilihan admin
     * lebih tahu daripada tebakan ini.
     */
    private function isiDaerahDari(string $nama): void
    {
        if (trim((string) ($this->dataRab['daerah'] ?? '')) !== '') {
            return;
        }

        $daerah = collect($this->katalog['destinasi'] ?? [])
            ->firstWhere('nama', $nama)['daerah'] ?? null;

        if (! is_string($daerah) || trim($daerah) === '') {
            return;
        }

        $this->kirimKe('ubah', "/rab/{$this->rabId}", ['daerah' => trim($daerah)]);
    }

    /** Menggeser "08:00" sekian menit; kosong tetap kosong. */
    private function tambahJam(string $jam, int $menit): string
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $jam, $c) !== 1) {
            return '';
        }

        $total = ((int) $c[1] * 60 + (int) $c[2] + $menit) % (24 * 60);

        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }

    /**
     * Jam berikutnya untuk hari itu: 08.00, lalu +2 jam tiap kegiatan.
     *
     * Tebakan, bukan keputusan — admin menimpanya kapan saja. Tetapi tebakan
     * yang hampir benar jauh lebih cepat diperbaiki daripada kotak kosong yang
     * harus diisi dari nol delapan belas kali per RAB.
     */
    private function jamBerikutnya(int $hari): string
    {
        /*
         | Kegiatan yang SUDAH punya jam selesai menyambung di situ — ia
         | memberi tahu kapan rombongan bebas, dan jeda yang dikarang di
         | atasnya cuma menggeser seluruh jadwal.
         |
         | Yang baru punya jam mulai belum memberi tahu apa pun tentang
         | lamanya, jadi dipakai tebakan dua jam. Tanpa pembedaan ini,
         | kegiatan berikutnya mulai pada jam yang SAMA dengan yang sedang
         | berlangsung — dua kegiatan bertabrakan di jadwal yang sama.
         */
        $jam = fn ($j) => preg_match('/^(\d{1,2}):(\d{2})$/', (string) $j, $c) === 1
            ? (int) $c[1] * 60 + (int) $c[2]
            : null;

        $menit = collect($this->itinerary)
            ->where('hari_ke', $hari)
            ->map(function ($k) use ($jam) {
                $selesai = $jam($k['jam_selesai'] ?? '');

                if ($selesai !== null) {
                    return $selesai;
                }

                $mulai = $jam($k['jam'] ?? '');

                return $mulai === null ? null : $mulai + 120;
            })
            ->filter(fn ($m) => $m !== null)
            ->max();

        if ($menit === null) {
            return '08:00';
        }

        // Lewat tengah malam berarti tebakannya sudah tidak masuk akal;
        // lebih baik kosong daripada menuliskan jam yang salah.
        return $menit >= 24 * 60 ? '' : sprintf('%02d:%02d', intdiv($menit, 60), $menit % 60);
    }

    public function tambahKegiatan(): void
    {
        $nama = trim($this->kegiatanBaru);

        if ($nama === '') {
            return;
        }

        $this->kegiatanBaru = '';
        $this->tambahKegiatanBernama($nama);
    }

    /**
     * Menambahkan kata yang dicari sebagai kegiatan biasa.
     *
     * Yang diketik di kotak cari sering memang bukan destinasi katalog —
     * nama di rincian biaya, misalnya, datang dari master harga. Tanpa jalan
     * keluar ini admin harus menghapus ketikannya, pindah ke kotak lain, dan
     * mengetik ulang kata yang sama.
     */
    public function tambahDariCari(): void
    {
        $nama = trim($this->cariDestinasi);

        if ($nama === '') {
            return;
        }

        $this->cariDestinasi = '';
        $this->tambahKegiatanBernama($nama);
    }

    /** Kegiatan baku yang diklik dari deretnya. */
    public function tambahKegiatanBaku(string $nama): void
    {
        if (! in_array($nama, self::KEGIATAN_BAKU, true)) {
            return;
        }

        $this->tambahKegiatanBernama($nama);
    }

    private function tambahKegiatanBernama(string $nama): void
    {
        $mulai = $this->jamBerikutnya($this->hariAktif);

        $this->sisipkan([
            'hari_ke' => $this->hariAktif,
            'jam' => $mulai,
            'jam_selesai' => $this->tambahJam($mulai, 120),
            'nama' => mb_substr($nama, 0, 150),
            'keterangan' => '',
            'destinasi' => '',
            'jenis' => 'lain',
        ]);
    }

    /** Disisipkan di akhir harinya, bukan di akhir seluruh daftar. */
    private function sisipkan(array $kegiatan): void
    {
        $posisi = count($this->itinerary);
        foreach ($this->itinerary as $i => $k) {
            if ($k['hari_ke'] <= $kegiatan['hari_ke']) {
                $posisi = $i + 1;
            }
        }

        array_splice($this->itinerary, $posisi, 0, [$kegiatan]);
        $this->simpanItinerary();
    }

    public function hapusKegiatan(int $i): void
    {
        if (! isset($this->itinerary[$i])) {
            return;
        }

        $this->kegiatanTerhapus = $this->itinerary[$i];

        unset($this->itinerary[$i]);
        $this->itinerary = array_values($this->itinerary);
        $this->simpanItinerary();
    }

    /**
     * Tautan WhatsApp berisi pesan yang sudah terketik.
     *
     * Ada di daftar sejak kemarin, tetapi penawaran justru SELESAI
     * disiapkan di layar ini — dan di sinilah orang menekan kirim.
     */
    public function tautanWa(): ?string
    {
        $angka = preg_replace('/\D/', '', (string) ($this->dataRab['whatsapp'] ?? ''));

        if (strlen($angka) < 8) {
            return null;
        }

        if (str_starts_with($angka, '0')) {
            $angka = '62'.ltrim($angka, '0');
        }

        $r = $this->dataRab['ringkasan'] ?? [];

        $pesan = 'Halo'.($this->dataRab['nama_pelanggan'] ? ' '.$this->dataRab['nama_pelanggan'] : '')
            .', berikut penawaran '.($this->dataRab['judul'] ?: 'perjalanan').' ('.$this->dataRab['kode'].'). '
            .'Harga '.($r['harga_per_orang_teks'] ?? '-').' per orang untuk '.$this->dataRab['jumlah_peserta'].' peserta. '
            .'Berkas rinciannya menyusul ya.';

        return 'https://wa.me/'.$angka.'?text='.rawurlencode($pesan);
    }

    /** Mengembalikan kegiatan yang baru saja dihapus. */
    public function urungkanHapus(): void
    {
        if ($this->kegiatanTerhapus === []) {
            return;
        }

        $this->sisipkan($this->kegiatanTerhapus);
        $this->kegiatanTerhapus = [];
    }

    public function lupakanUrungkan(): void
    {
        $this->kegiatanTerhapus = [];
    }

    /**
     * Menyalin seluruh kegiatan satu hari ke hari lain.
     *
     * Trip tiga hari yang polanya mirip — sarapan, destinasi, makan siang,
     * hotel — diketik tiga kali tanpa ini. Yang disalin susunannya; jam dan
     * destinasinya ikut, karena justru itulah yang membuat menyalin lebih
     * cepat daripada mengetik ulang.
     */
    public function salinHari(int $ke): void
    {
        $sumber = collect($this->itinerary)->where('hari_ke', $this->hariAktif)->values();

        if ($sumber->isEmpty() || $ke === $this->hariAktif) {
            return;
        }

        foreach ($sumber as $kegiatan) {
            $kegiatan['hari_ke'] = $ke;
            $this->itinerary[] = $kegiatan;
        }

        $this->simpanItinerary($sumber->count().' kegiatan disalin ke hari '.$ke.'.');
        $this->hariAktif = $ke;
    }

    /** Mengosongkan satu hari sekaligus. */
    public function kosongkanHari(): void
    {
        $jumlah = collect($this->itinerary)->where('hari_ke', $this->hariAktif)->count();

        if ($jumlah === 0) {
            return;
        }

        $this->itinerary = collect($this->itinerary)
            ->reject(fn ($k) => (int) $k['hari_ke'] === $this->hariAktif)
            ->values()->all();

        // Satu hari penuh tidak muat di penyangga urungkan satu kegiatan —
        // dikatakan apa adanya supaya tidak ada yang mengira bisa dibatalkan.
        $this->kegiatanTerhapus = [];
        $this->simpanItinerary("Hari {$this->hariAktif} dikosongkan ({$jumlah} kegiatan). Tidak bisa diurungkan.");
    }

    /**
     * Menyusun ulang kegiatan satu hari menurut urutan yang diseret.
     *
     * Memindahkan kegiatan dari urutan delapan ke satu berarti tujuh kali
     * menekan panah. Yang dikirim dari layar urutan indeksnya; yang
     * disusun di sini barisnya.
     */
    public function susunUlang(array $indeks): void
    {
        $baru = [];

        foreach ($indeks as $i) {
            if (isset($this->itinerary[(int) $i])) {
                $baru[] = $this->itinerary[(int) $i];
            }
        }

        // Kegiatan hari LAIN tidak ikut dikirim — ditambahkan kembali apa
        // adanya, kalau tidak seluruh hari di luar yang sedang dibuka
        // hilang begitu satu baris digeser.
        foreach ($this->itinerary as $i => $kegiatan) {
            if (! in_array((string) $i, array_map('strval', $indeks), true)) {
                $baru[] = $kegiatan;
            }
        }

        $this->itinerary = $baru;
        $this->simpanItinerary();
    }

    /** Menggeser kegiatan naik (-1) atau turun (+1) di dalam harinya. */
    public function geser(int $i, int $arah): void
    {
        $j = $i + ($arah < 0 ? -1 : 1);

        if (! isset($this->itinerary[$i], $this->itinerary[$j])
            || $this->itinerary[$i]['hari_ke'] !== $this->itinerary[$j]['hari_ke']) {
            return;
        }

        [$this->itinerary[$i], $this->itinerary[$j]] = [$this->itinerary[$j], $this->itinerary[$i]];
        $this->simpanItinerary();
    }

    public function pindahHari(int $i, int $hari): void
    {
        if (! isset($this->itinerary[$i])) {
            return;
        }

        $kegiatan = $this->itinerary[$i];
        unset($this->itinerary[$i]);
        $this->itinerary = array_values($this->itinerary);
        $kegiatan['hari_ke'] = $hari;
        $this->sisipkan($kegiatan);
    }

    /** Jam, nama, atau keterangan disunting lalu ditinggalkan. */
    public function updatedItinerary(): void
    {
        $this->simpanItinerary();
    }

    private function simpanItinerary(?string $pesan = null): void
    {
        $data = collect($this->itinerary)
            ->filter(fn ($k) => trim((string) $k['nama']) !== '')
            ->sortBy('hari_ke', SORT_NUMERIC)   // stabil: urutan di dalam hari tetap
            ->map(fn ($k) => [
                'hari_ke' => (int) $k['hari_ke'],
                'jam' => $k['jam'] !== '' ? substr($k['jam'], 0, 5) : null,
                'jam_selesai' => ($k['jam_selesai'] ?? '') !== '' ? substr($k['jam_selesai'], 0, 5) : null,
                'nama' => trim($k['nama']),
                'keterangan' => trim($k['keterangan']) ?: null,
                'destinasi' => $k['destinasi'] ?: null,
            ])->values()->all();

        $balasan = $this->kirimKe('ubah', "/rab/{$this->rabId}/itinerary", ['itinerary' => $data], $pesan);

        /*
         | Tiket yang ikut terbuang DIKATAKAN.
         |
         | Baris biaya yang hilang sendiri tanpa sepatah kata membuat total
         | berubah tanpa sebab yang terlihat — dan yang pertama dicurigai
         | justru hitungannya.
         */
        $dibuang = $balasan['tiket_dibuang'] ?? [];

        if ($dibuang !== [] && $pesan === null) {
            $this->dispatch('order-updated', message: count($dibuang) === 1
                ? "Tiket {$dibuang[0]} ikut dibuang — tempatnya tidak lagi dikunjungi."
                : count($dibuang).' tiket ikut dibuang karena tempatnya tidak lagi dikunjungi.');
        }
    }

    /* -------------------------------- BIAYA -------------------------------- */

    /**
     * @param  bool  $diam  true saat dipanggil sebagai ikutan klik lain:
     *                      kabar "0 biaya ditarik" pada tiap destinasi yang
     *                      tiketnya sudah ada cuma kebisingan.
     */
    public function tarikBiaya(bool $diam = false): void
    {
        $balasan = $this->kirimKe('kirim', "/rab/{$this->rabId}/tarik-biaya", [], null);

        if (! $balasan) {
            return;
        }

        $this->tanpaTiket = $balasan['tanpa_tiket'] ?? [];

        if ($diam) {
            return;
        }

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Biaya ditarik.');
    }

    /**
     * Kendaraan mana yang muat untuk jumlah peserta RAB ini.
     *
     * Master harga menyimpan "kendaraan ini muat berapa"; yang belum ada
     * adalah jembatan ke jumlah peserta. Tanpa itu admin menghitung sendiri
     * di kepala — 42 peserta dibagi 14 kursi — setiap kali menyusun RAB, dan
     * yang paling sering terjadi bukan salah hitung melainkan tidak dihitung
     * sama sekali: dipilih Hiace karena itu yang biasa, lalu satu unit, lalu
     * dua belas orang tidak kebagian kursi.
     *
     * Rumusnya SAMA PERSIS dengan HitungRab::faktor() di Orcha — unit =
     * ceil(peserta / muat), biaya = harga × unit × hari. Kalau berbeda,
     * angka di panel ini akan berselisih dengan angka di rincian biaya tepat
     * di bawahnya, dan yang dipercaya admin jadi yang mana saja.
     *
     * Dihitung di Phoenix, bukan diminta ke Orcha: seluruh bahannya sudah
     * ikut di /rab/katalog yang memang sudah ditarik layar ini.
     */
    public function saranKendaraan(): array
    {
        $peserta = max(1, (int) ($this->dataRab['jumlah_peserta'] ?? 1));
        $hari = max(1, (int) ($this->dataRab['jumlah_hari'] ?? 1));

        $dipakai = collect($this->dataRab['ringkasan']['baris'] ?? [])
            ->pluck('master_harga_id')->filter()->flip();

        return collect($this->katalog['master'] ?? [])
            ->filter(fn ($m) => ($m['satuan'] ?? '') === 'unit_hari' && (int) ($m['kapasitas'] ?? 0) > 0)
            ->map(function (array $m) use ($peserta, $hari, $dipakai) {
                $muat = (int) $m['kapasitas'];
                $unit = (int) ceil($peserta / $muat);
                $kursi = $unit * $muat;

                return [
                    'id' => (int) $m['id'],
                    'nama' => $m['nama'],
                    'muat' => $muat,
                    'unit' => $unit,
                    'sisa' => $kursi - $peserta,
                    'total' => (int) $m['harga'] * $unit * $hari,
                    'sudah' => $dipakai->has((int) $m['id']),
                ];
            })
            // Termurah dulu; bila seri, yang kursinya paling sedikit
            // menganggur. Urutannya memang urutan rekomendasinya.
            ->sortBy([['total', 'asc'], ['sisa', 'asc']])
            ->values()->all();
    }

    public function tambahDariMaster(): void
    {
        if ($this->pilihMaster === '') {
            return;
        }

        $this->kirimKe('kirim', "/rab/{$this->rabId}/biaya", ['master_harga_id' => (int) $this->pilihMaster], 'Biaya ditambahkan.');
        $this->pilihMaster = '';
    }

    /** Memakai kendaraan yang disarankan, tanpa mencarinya lagi di kotak pilih. */
    public function pakaiKendaraan(int $id): void
    {
        $this->kirimKe('kirim', "/rab/{$this->rabId}/biaya", ['master_harga_id' => $id], 'Kendaraan ditambahkan ke biaya.');
    }

    private function kosongkanManual(): void
    {
        $this->manual = [
            'kategori' => 'lainnya', 'nama' => '', 'satuan' => 'rombongan',
            'harga_satuan' => '', 'kapasitas' => '', 'vendor_id' => '',
        ];
    }

    public function tambahManual(): void
    {
        $berkapasitas = in_array($this->manual['satuan'], ['unit_hari', 'kamar_malam'], true);

        $this->validate([
            'manual.nama' => 'required|string|max:150',
            'manual.harga_satuan' => 'required',
            'manual.kapasitas' => $berkapasitas ? 'required|integer|min:1' : 'nullable',
        ], [], ['manual.nama' => 'nama biaya', 'manual.harga_satuan' => 'harga', 'manual.kapasitas' => 'kapasitas']);

        $hasil = $this->kirimKe('kirim', "/rab/{$this->rabId}/biaya", [
            'kategori' => $this->manual['kategori'],
            'nama' => trim($this->manual['nama']),
            'satuan' => $this->manual['satuan'],
            'harga_satuan' => $this->angkaDari($this->manual['harga_satuan']),
            'kapasitas' => $berkapasitas ? (int) $this->manual['kapasitas'] : null,
            // Justru di biaya manual vendor dadakan muncul: sewa perahu, jip,
            // pemandu lokal. Kosong dikirim sebagai null — kolomnya kunci
            // asing, dan "" bukan id yang ada di tabel vendor.
            'vendor_id' => ($this->manual['vendor_id'] ?? '') !== '' ? (int) $this->manual['vendor_id'] : null,
        ], 'Biaya ditambahkan.');

        if ($hasil) {
            $this->kosongkanManual();
            $this->bukaManual = false;
        }
    }

    public function updatedManualHargaSatuan(): void
    {
        $this->manual['harga_satuan'] = $this->keRupiah($this->angkaDari($this->manual['harga_satuan']));
    }

    public function updatedHargaBaris($nilai, $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['harga_satuan' => $this->angkaDari((string) $nilai)]);
    }

    public function updatedJumlahBaris($nilai, $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['jumlah' => max(1, min(100, (int) $nilai))]);
    }

    public function pakaiHargaMaster(int $id): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}/biaya/{$id}", ['pakai_harga_master' => true], 'Harga diperbarui ke harga master.');
    }

    public function hapusBiaya(int $id): void
    {
        $balasan = $this->kirimKe('hapus', "/rab/{$this->rabId}/biaya/{$id}", [], 'Biaya dihapus.');

        // Itinerary TIDAK diubah diam-diam: ia rencana yang dibaca
        // pelanggan. Yang ditawarkan pilihannya, bukan akibatnya.
        $this->tiketTertinggal = (string) ($balasan['destinasi_tertinggal'] ?? '');
    }

    /** Membuang kunjungan yang tiketnya baru saja dihapus. */
    public function buangKunjungan(): void
    {
        if ($this->tiketTertinggal === '') {
            return;
        }

        $kunci = mb_strtolower(trim($this->tiketTertinggal));

        $this->itinerary = collect($this->itinerary)
            ->reject(fn ($k) => mb_strtolower(trim((string) ($k['destinasi'] ?? ''))) === $kunci)
            ->values()->all();

        $this->tiketTertinggal = '';
        $this->simpanItinerary('Kunjungannya ikut dibuang dari itinerary.');
    }

    public function lupakanTertinggal(): void
    {
        $this->tiketTertinggal = '';
    }

    /* -------------------------------- MARGIN ------------------------------- */

    public function updatedMarginJenis(): void
    {
        /*
         | Angka lama tidak dibawa pindah jenis: "20" persen yang berubah jadi
         | Rp 20 per orang bukan yang dimaksud siapa pun.
         |
         | Belum disimpan di sini. Menyimpan jenis baru dengan nilai kosong
         | membuat harga sesaat jatuh ke modal — dan PDF yang diunduh pada
         | saat itu menawarkan perjalanan tanpa untung. Disimpan begitu
         | angkanya diisi.
         */
        $this->marginNilai = '';
    }

    public function updatedMarginNilai(): void
    {
        $this->simpanMargin();
    }

    public function updatedPembulatan(): void
    {
        $this->simpanMargin();
    }

    private function simpanMargin(): void
    {
        $nilai = $this->marginJenis === 'persen'
            ? (int) round((float) str_replace(',', '.', $this->marginNilai))
            : $this->angkaDari($this->marginNilai);

        $this->kirimKe('ubah', "/rab/{$this->rabId}", [
            'margin_jenis' => $this->marginJenis,
            'margin_nilai' => $nilai,
            'pembulatan' => $this->pembulatan,
        ]);
    }

    /* ------------------------------ KEPALA RAB ----------------------------- */

    public function simpanKepala(): void
    {
        $this->validate([
            'kepala.judul' => 'required|string|min:3|max:150',
            'kepala.nama_pelanggan' => 'required|string|min:2|max:120',
            'kepala.email' => 'nullable|email|max:150',
            'kepala.tanggal_mulai' => 'nullable|date',
            'kepala.jumlah_hari' => 'required|integer|min:1|max:30',
            'kepala.jumlah_malam' => 'required|integer|min:0|max:30|lte:kepala.jumlah_hari',
            'kepala.jumlah_peserta' => 'required|integer|min:1|max:500',
            'kepala.berlaku_sampai' => 'nullable|date',
        ], [
            'kepala.jumlah_malam.lte' => 'Jumlah malam tidak boleh melebihi jumlah hari.',
        ], [
            'kepala.judul' => 'judul', 'kepala.nama_pelanggan' => 'nama pelanggan', 'kepala.email' => 'email',
            'kepala.jumlah_hari' => 'jumlah hari', 'kepala.jumlah_peserta' => 'jumlah peserta',
        ]);

        /*
         | Memperpendek perjalanan ditolak bila masih ada kegiatan di hari yang
         | akan hilang. Membuangnya diam-diam berarti admin kehilangan susunan
         | hari keempat hanya karena salah ketik 3.
         */
        $hariTerjauh = (int) collect($this->itinerary)->max('hari_ke');
        if ($hariTerjauh > (int) $this->kepala['jumlah_hari']) {
            $this->addError('kepala.jumlah_hari', "Masih ada kegiatan di hari ke-{$hariTerjauh}. Pindahkan atau hapus dulu.");

            return;
        }

        $data = collect($this->kepala)->map(fn ($v) => is_string($v) ? (trim($v) ?: null) : $v)->all();

        if ($this->kirimKe('ubah', "/rab/{$this->rabId}", $data, 'Data perjalanan disimpan.')) {
            $this->bukaKepala = false;
            $this->kepalaAwal = $this->kepala;
        }
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimKe('ubah', "/rab/{$this->rabId}", ['status' => $status], 'Status diubah.');
    }

    public function jadikanPendaftaran(): void
    {
        $this->validate(['paketId' => 'required'], ['paketId.required' => 'Pilih paket induknya dulu.']);

        try {
            $baru = $this->orcha()->kirim("/rab/{$this->rabId}/jadikan-pendaftaran", [
                'travel_package_id' => (int) $this->paketId,
                'abaikan_impas' => $this->abaikanImpas,
            ])['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        \App\Support\HitunganOrcha::lupakanSemua();
        $this->dispatch('orcha-sukses-pindah',
            message: 'Pendaftaran '.($baru['kode'] ?? '').' dibuat dari RAB ini.',
            url: route('admin.orcha.pendaftaran.detail', $baru['id']));
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.rab.susun', ['rab' => $this->dataRab])
            ->layout('livewire.layout.templateindex');
    }
}
