<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab\Concerns;

use App\Exceptions\OrchaTidakTerjangkau;

/**
 * Isian satu baris master harga.
 *
 * Ditarik keluar dari daftar saat formulirnya pindah ke halaman tersendiri.
 * Yang berubah cuma TEMPATNYA — aturannya tidak disalin, karena dua salinan
 * yang menyimpan ke jalur yang sama tetapi bisa berbeda syaratnya adalah
 * selisih yang baru ketahuan setelah salah satunya menyimpan nilai yang tidak
 * diterima yang lain.
 *
 * Kelas yang memakainya wajib menyediakan orcha() dan katalog().
 */
trait MenyuntingMasterHarga
{
    /** Provinsi yang dipakai saat membuat baris baru. */
    public string $provinsiBawaan = '';

    private const SATUAN_BAKU = [
        'transportasi' => 'unit_hari',
        'akomodasi' => 'kamar_malam',
        'konsumsi' => 'orang',
        'tiket' => 'orang',
        'pemandu' => 'hari',
        'asuransi' => 'orang',
        'dokumentasi' => 'rombongan',
        'operasional' => 'rombongan',
        'sopir' => 'hari',
        'lainnya' => 'rombongan',
    ];

    private const PILIHAN_KAPASITAS = [
        /*
         | Satuan 'unit' dan 'unit_hari' memakai pilihan yang SAMA.
         |
         | Bedanya cuma dikali hari atau tidak — tiket masuk kendaraan
         | dibayar sekali di gerbang, sewa armada tiap hari. Yang diisi di
         | kotak kapasitas tetap muatan satu kendaraan, dan kendaraannya
         | kendaraan yang sama.
         */
        /*
         | Urut dari muatan TERKECIL. Daftarnya dibaca sambil mencari yang
         | mendekati, dan urutan yang melompat-lompat memaksa pembacanya
         | membandingkan angka satu per satu.
         |
         | Jeep wisata (Bromo, Ijen, Baluran) baku empat penumpang — itu
         | angka yang dipakai operator saat menghitung berapa unit untuk satu
         | rombongan. Ada yang memuat enam, tetapi mengisi 6 berarti RAB
         | memesan unit lebih SEDIKIT daripada yang benar-benar dibutuhkan,
         | dan kekurangan satu jeep baru ketahuan di hari berangkat.
         */
        'unit' => [
            ['Jeep wisata', 4],
            ['Mobil pribadi', 6],
            ['Hiace', 14],
            ['Elf long', 19],
            ['Bus sedang', 31],
            ['Bus besar', 59],
        ],
        'unit_hari' => [
            ['Jeep wisata', 4],
            ['Mobil pribadi', 6],
            ['Hiace', 14],
            ['Elf long', 19],
            ['Bus sedang', 31],
            ['Bus besar', 59],
        ],
        'kamar_malam' => [
            ['Single', 1],
            ['Twin / Double', 2],
            ['Triple', 3],
            ['Family', 4],
        ],
    ];

    public ?int $sunting = null;

    public bool $tambah = false;

    /*
     | Penolakan "sudah ada harga bernama sama" yang sedang menunggu jawaban.
     |
     | Orcha punya pintu darurat (?paksa=1) untuk dua harga yang memang
     | berbeda — dua vendor, dua musim. Tanpa gagang di layar, admin yang
     | berniat menyimpan keduanya tidak punya jalan selain mengarang nama.
     */
    public string $kembaranPesan = '';

    /** Kiriman berikutnya menerobos penjaga kembaran. */
    public bool $paksaBerikutnya = false;

    public array $kembaranBaris = [];

    /** Tetap di formulir setelah menyimpan, untuk memasukkan baris berikutnya. */
    public bool $lanjutTambah = false;

    /** Panel tempel-dari-spreadsheet. */
    public bool $tempelBuka = false;

    public string $tempelIsi = '';

    public array $tempelGagal = [];

    /**
     * Destinasi LAIN yang tercakup tiket yang sama.
     *
     * Tiket terusan Dieng berlaku untuk Candi Arjuna dan Kawah Sikidang,
     * dibayar sekali. Tanpa daftar ini admin terpaksa membuat dua baris —
     * dan keduanya tertarik ke RAB, lalu tiketnya ditagih dua kali.
     *
     * @var array<int, string>
     */
    public array $destinasiLain = [];

    /** Pilihan yang sedang disorot di kotak "tambah destinasi lain". */
    public string $destinasiLainPilih = '';

    /** Kotak isian destinasi baru sedang terbuka. */
    public bool $destinasiBaru = false;

    public string $namaDestinasiBaru = '';

    /** Kotak isian vendor baru sedang terbuka. */
    public bool $vendorBaru = false;

    public string $namaVendorBaru = '';

    public string $kontakVendorBaru = '';

    public array $isian = [];

    /** Sakelar dipegang properti tersendiri — lihat OrchaPromoList::$aktif. */
    public bool $aktif = true;

    public bool $otomatis = false;

    /**
     * Admin sudah memilih satuannya sendiri.
     *
     * Tanpa penanda ini, mengganti kategori akan menimpa satuan yang baru
     * saja dipilih tangan — perbaikan yang berubah jadi gangguan.
     */
    public bool $satuanDisetel = false;

    /**
     * Kotak harga akhir pekan sedang dibuka.
     *
     * Saklar, bukan kotak yang selalu hadir: sebagian besar harga sama
     * setiap hari, dan kotak kedua yang ada untuk tidak diisi tetap menuntut
     * dibaca dan dipikirkan lebih dulu di setiap formulir.
     */
    public bool $duaHarga = false;

    /** Harga akhir pekan ditampilkan bertitik begitu kotaknya ditinggalkan. */
    public function updatedIsianHargaAkhirPekan(): void
    {
        $this->isian['harga_akhir_pekan'] = $this->keRupiah($this->angkaDari($this->isian['harga_akhir_pekan']));
    }

    /**
     * Tarif musim baris ini, per musim_id, sebagai teks rupiah.
     *
     * Kosong berarti HAPUS tarif musim itu — dan itu bukan sama dengan nol:
     * nol adalah harga yang sah. Satu-satunya cara mengatakan "harga ini
     * tidak naik di Nataru" adalah tidak adanya angkanya.
     *
     * @var array<int, string>
     */
    public array $hargaMusim = [];

    /**
     * Kotak tarif musim sedang dibuka.
     *
     * Saklar, dengan alasan yang sama dengan $duaHarga: sebagian besar harga
     * tidak naik di musim mana pun — tiket Candi Arjuna tetap Rp 10.000 pada
     * 25 Desember — dan deretan kotak yang ada untuk tidak diisi tetap
     * menuntut dibaca lebih dulu di setiap formulir.
     */
    public bool $pakaiMusim = false;

    /**
     * Angka tarif musim ditampilkan bertitik begitu kotaknya ditinggalkan.
     *
     * Satu penangan untuk SELURUH kotak: Livewire memanggil updated() dengan
     * nama medannya, dan menulis satu metode per musim mustahil — musimnya
     * bertambah sendiri.
     */
    public function updatedHargaMusim($nilai, $kunci): void
    {
        if ($kunci === null) {
            return;
        }

        $this->hargaMusim[$kunci] = $nilai === '' || $nilai === null
            ? ''
            : $this->keRupiah($this->angkaDari($nilai));
    }

    /**
     * Mematikan saklarnya MENGOSONGKAN seluruh angkanya.
     *
     * Alasannya sama dengan saklar harga akhir pekan: angka yang tidak
     * terlihat lagi tetap terkirim, dan tarif musim terus berlaku padahal
     * admin yakin sudah mematikannya — selisih yang tidak bisa ditemukan
     * karena kotaknya tidak ada di layar.
     */
    public function updatedPakaiMusim(): void
    {
        if (! $this->pakaiMusim) {
            foreach ($this->hargaMusim as $k => $v) {
                $this->hargaMusim[$k] = '';
            }
        }
    }

    /**
     * Mematikan saklarnya MENGOSONGKAN angkanya.
     *
     * Kalau tidak, angka yang tidak terlihat lagi tetap tersimpan dan harga
     * akhir pekan terus berlaku padahal admin yakin sudah mematikannya —
     * selisih yang tidak bisa ditemukan karena kotaknya tidak ada di layar.
     */
    public function updatedDuaHarga(): void
    {
        if (! $this->duaHarga) {
            $this->isian['harga_akhir_pekan'] = '';
            $this->resetValidation('isian.harga_akhir_pekan');
        }
    }

    private function kosongkan(): void
    {
        $this->isian = [
            'kategori' => 'tiket',
            'vendor_id' => '',
            'nama' => '',
            // Provinsi bawaan datang dari pemanggilnya (daftar mengirim
            // saringannya yang sedang aktif), bukan dibaca dari saringan —
            // halaman borang tidak punya saringan.
            'provinsi' => $this->provinsiBawaan,
            'daerah' => '',
            'destinasi' => '',
            'satuan' => 'orang',
            'harga' => '',
            'harga_akhir_pekan' => '',
            'kapasitas' => '',
            'berlaku_sampai' => '',
            'catatan' => '',
        ];
        $this->aktif = true;
        $this->otomatis = false;
        $this->duaHarga = false;
        $this->pakaiMusim = false;
        $this->hargaMusim = [];
        $this->satuanDisetel = false;
        $this->destinasiLain = [];
        $this->destinasiLainPilih = '';
        $this->destinasiBaru = false;
        $this->namaDestinasiBaru = '';
        $this->vendorBaru = false;
        $this->namaVendorBaru = '';
        $this->kontakVendorBaru = '';
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
            'kategori' => $baris['kategori'],
            'vendor_id' => (string) ($baris['vendor_id'] ?? ''),
            'nama' => $baris['nama'],
            'provinsi' => (string) ($baris['provinsi'] ?? ''),
            'daerah' => (string) ($baris['daerah'] ?? ''),
            'destinasi' => (string) ($baris['destinasi'] ?? ''),
            'satuan' => $baris['satuan'],
            'harga' => $this->keRupiah($baris['harga']),
            'harga_akhir_pekan' => ($baris['harga_akhir_pekan'] ?? null) !== null
                ? $this->keRupiah($baris['harga_akhir_pekan']) : '',
            'kapasitas' => (string) ($baris['kapasitas'] ?? ''),
            'berlaku_sampai' => (string) ($baris['berlaku_sampai'] ?? ''),
            'catatan' => (string) ($baris['catatan'] ?? ''),
        ];
        $this->aktif = (bool) $baris['aktif'];
        $this->otomatis = (bool) $baris['otomatis'];
        // Dari isinya, bukan dari dua_harga: harga akhir pekan yang KEBETULAN
        // sama dengan hari kerja tetap harga yang pernah diisi orang, dan
        // kotaknya tidak boleh menghilang saat barisnya dibuka lagi.
        $this->duaHarga = ($baris['harga_akhir_pekan'] ?? null) !== null;

        /*
         | Tarif musim yang sudah tersimpan dimuat ke kotaknya.
         |
         | Dari isinya, bukan dari ada/tidaknya musim di sistem: baris yang
         | tidak punya tarif musim membuka borang dengan saklarnya mati, dan
         | menyalakannya hanya karena ada musim terdaftar berarti deretan
         | kotak kosong di setiap formulir.
         */
        $this->hargaMusim = [];

        foreach ($baris['harga_musim'] ?? [] as $hm) {
            $this->hargaMusim[(int) $hm['musim_id']] = $this->keRupiah($hm['harga']);
        }

        $this->pakaiMusim = $this->hargaMusim !== [];

        $this->destinasiLain = array_values(array_filter(
            array_map(fn ($d) => trim((string) $d), $baris['destinasi_lain'] ?? [])
        ));
        $this->destinasiLainPilih = '';

        // Baris yang sudah tersimpan satuannya memang pilihan orang, bukan
        // terkaan — mengganti kategorinya tidak boleh menimpanya.
        $this->satuanDisetel = true;
        $this->destinasiBaru = false;
        $this->namaDestinasiBaru = '';
    }

    public function tutup(): void
    {
        $this->sunting = null;
        $this->tambah = false;
        $this->kembaranPesan = '';
        $this->kembaranBaris = [];
        $this->lanjutTambah = false;
        $this->kosongkan();
    }

    /**
     * Kategori menentukan satuannya, dan satuan menentukan kapasitasnya.
     *
     * Dari sembilan kotak, dua di antaranya jawabannya sudah diketahui begitu
     * kategorinya dipilih. Keduanya tetap terlihat dan tetap bisa diubah —
     * yang dikerjakan diam-diam akan mengejutkan saat angkanya masuk RAB.
     */
    public function updatedIsianKategori(string $kategori): void
    {
        if ($this->satuanDisetel) {
            return;
        }

        $this->isian['satuan'] = self::SATUAN_BAKU[$kategori] ?? ($this->isian['satuan'] ?? 'orang');
    }

    public function updatedIsianSatuan(): void
    {
        $this->satuanDisetel = true;

        // Kapasitas milik satuan yang lama tidak berlaku untuk yang baru:
        // "14" benar untuk Hiace dan omong kosong untuk kamar hotel.
        $this->isian['kapasitas'] = '';
    }

    /**
     * Sebutan satu buahnya: unit untuk kendaraan, kamar untuk hotel.
     *
     * Kata inilah yang membedakan "kapasitas" dari "jumlah unit" — dua angka
     * yang selama ini sama-sama disebut kapasitas dan karena itu tertukar.
     */
    public function sebutanUnit(): string
    {
        return ($this->isian['satuan'] ?? '') === 'kamar_malam' ? 'kamar' : 'unit';
    }

    /** Pilihan cepat kapasitas untuk satuan yang sedang dipakai. */
    public function pilihanKapasitas(): array
    {
        return self::PILIHAN_KAPASITAS[$this->isian['satuan'] ?? ''] ?? [];
    }

    public function pakaiKapasitas(int $jumlah): void
    {
        $this->isian['kapasitas'] = (string) max(1, $jumlah);
        $this->resetValidation('isian.kapasitas');
    }

    /**
     * Satuan ini menghitung jumlah unit, jadi kapasitasnya menentukan.
     *
     * Daftarnya datang dari kosakata Orcha, bukan dari tetapan di berkas ini:
     * PILIHAN_KAPASITAS hanya tahu angka yang sering dipakai, dan satuan
     * ber-kapasitas yang kelak ditambahkan di sana tidak akan terbaca kalau
     * aturannya ikut dikunci di sini.
     */
    public function perluKapasitas(): bool
    {
        // Cadangan ikut menyebut 'unit': Orcha yang tak terjangkau tidak boleh
        // membuat kotak kapasitas hilang untuk satuan yang membutuhkannya.
        $daftar = $this->katalog()['kosakata']['satuan_berkapasitas'] ?? ['unit', 'unit_hari', 'kamar_malam'];

        return in_array($this->isian['satuan'] ?? '', $daftar, true);
    }

    /**
     * Nama yang dirakit dari kategori dan tempatnya.
     *
     * Untuk tiket ini bukan sekadar menghemat ketikan: namanya dirakit DARI
     * destinasi yang dipilih, jadi tidak bisa lagi berbeda dari destinasi
     * yang dimaksudnya.
     */
    public function namaOtomatis(): string
    {
        $destinasi = trim((string) ($this->isian['destinasi'] ?? ''));
        $daerah = trim((string) ($this->isian['daerah'] ?? ''));
        $tempat = $daerah !== '' ? $daerah : trim((string) ($this->isian['provinsi'] ?? ''));

        return match ($this->isian['kategori'] ?? '') {
            'tiket' => $destinasi !== '' ? "Tiket Masuk {$destinasi}" : ($tempat !== '' ? "Retribusi {$tempat}" : ''),
            'akomodasi' => $tempat !== '' ? "Hotel di {$tempat}" : '',
            'konsumsi' => $tempat !== '' ? "Makan di {$tempat}" : '',
            'pemandu' => $tempat !== '' ? "Pemandu {$tempat}" : '',
            default => '',
        };
    }

    /**
     * Tiket tanpa destinasi tidak akan pernah ikut tertarik ke RAB.
     *
     * Penariknya mencari harga tiket lewat nama destinasi di itinerary. Tiket
     * yang tersimpan tanpa destinasi memang tersimpan — tetapi tidak pernah
     * terpakai, dan RAB-nya kekurangan modal tanpa satu pun tanda.
     */
    public function tiketTanpaDestinasi(): bool
    {
        return ($this->isian['kategori'] ?? '') === 'tiket'
            && trim((string) ($this->isian['destinasi'] ?? '')) === ''
            && ! $this->otomatis;
    }

    /** Harga ditampilkan bertitik begitu kotaknya ditinggalkan. */
    public function updatedIsianHarga(): void
    {
        $this->isian['harga'] = $this->keRupiah($this->angkaDari($this->isian['harga']));
    }

    /**
     * Destinasi yang dipilih mengisi provinsi & daerahnya sendiri.
     *
     * Tiket Prambanan yang tersimpan tanpa provinsi berlaku di SEMUA provinsi
     * — dan muncul di RAB Bali. Mengisinya otomatis dari katalog menutup
     * kemungkinan itu tanpa menambah satu kotak pun untuk diisi admin.
     */
    /**
     * Menambah destinasi lain yang tercakup tiket yang sama.
     *
     * Tidak boleh sama dengan destinasi utamanya, dan tidak boleh ganda —
     * keduanya hanya akan membingungkan tanpa mengubah apa pun, karena
     * penjodohannya memang menyaring yang kembar.
     */
    public function tambahDestinasiLain(): void
    {
        $pilih = trim($this->destinasiLainPilih);
        $utama = trim((string) ($this->isian['destinasi'] ?? ''));
        $rapi = fn (string $t) => mb_strtolower($t);

        if ($pilih === '') {
            return;
        }

        $sudah = collect($this->destinasiLain)->push($utama)->map($rapi);

        if (! $sudah->contains($rapi($pilih))) {
            $this->destinasiLain[] = $pilih;
        }

        $this->destinasiLainPilih = '';
    }

    public function hapusDestinasiLain(int $i): void
    {
        unset($this->destinasiLain[$i]);
        $this->destinasiLain = array_values($this->destinasiLain);
    }

    /**
     * Menambah destinasi ke katalog tanpa meninggalkan formulir ini.
     *
     * Kolom destinasi kini dipilih, bukan diketik — supaya jodohnya ke harga
     * tiket tidak bisa putus karena salah ketik. Tetapi destinasi baru memang
     * ada, dan menyuruh admin pindah menu lalu kembali berarti kehilangan
     * seluruh isian yang sudah diketiknya.
     *
     * Namanya dirapikan persis seperti yang dilakukan Orcha (spasi ganda jadi
     * satu, ujungnya dipangkas) supaya yang terpilih di sini sama persis
     * dengan yang tersimpan di sana — kalau tidak, jodohnya putus lagi di
     * tempat yang baru saja diperbaiki.
     */
    public function tambahDestinasiKatalog(): void
    {
        $nama = trim(preg_replace('/\s+/', ' ', (string) $this->namaDestinasiBaru));

        $this->resetValidation('namaDestinasiBaru');

        if (mb_strlen($nama) < 3) {
            $this->addError('namaDestinasiBaru', 'Nama destinasi minimal 3 huruf.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/katalog-destinasi', [
                'nama' => $nama,
                'provinsi' => trim((string) ($this->isian['provinsi'] ?? '')) ?: null,
                'daerah' => trim((string) ($this->isian['daerah'] ?? '')) ?: null,
            ]);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        // Katalognya disimpan sebentar; tanpa dilupakan, destinasi yang baru
        // saja dibuat tidak muncul di kotak pilihnya sendiri.
        cache()->forget('orcha.rab.katalog');

        $this->isian['destinasi'] = $nama;
        $this->namaDestinasiBaru = '';
        $this->destinasiBaru = false;

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Destinasi ditambahkan.');
    }

    /**
     * Menambah vendor tanpa meninggalkan formulir ini.
     *
     * Satu-satunya jalan sebelumnya adalah tautan ke daftar vendor — yang
     * MEMINDAHKAN halaman, dan seluruh isian harga yang sudah diketik hilang
     * bersamanya. Persoalan yang sama dengan destinasi baru, dan jawabannya
     * pun sama: kerjakan di tempat.
     */
    public function tambahVendorKatalog(): void
    {
        $nama = trim(preg_replace('/\s+/', ' ', (string) $this->namaVendorBaru));

        $this->resetValidation('namaVendorBaru');

        if (mb_strlen($nama) < 2) {
            $this->addError('namaVendorBaru', 'Nama vendor minimal 2 huruf.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/vendor', [
                'nama' => $nama,
                'kontak' => trim($this->kontakVendorBaru) ?: null,
            ]);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        // Katalognya disimpan sepuluh menit; tanpa dilupakan, vendor yang baru
        // dibuat tidak muncul di kotak pilihnya sendiri.
        cache()->forget('orcha.rab.katalog');

        // Langsung terpilih. Vendor yang dibuat lalu harus dicari lagi di
        // kotak pilih adalah pekerjaan yang dikerjakan dua kali.
        $this->isian['vendor_id'] = (string) ($balasan['data']['id'] ?? '');
        $this->namaVendorBaru = '';
        $this->kontakVendorBaru = '';
        $this->vendorBaru = false;

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Vendor ditambahkan.');
    }

    public function batalVendorBaru(): void
    {
        $this->vendorBaru = false;
        $this->namaVendorBaru = '';
        $this->kontakVendorBaru = '';
        $this->resetValidation('namaVendorBaru');
    }

    public function batalDestinasiBaru(): void
    {
        $this->destinasiBaru = false;
        $this->namaDestinasiBaru = '';
        $this->resetValidation('namaDestinasiBaru');
    }

    public function updatedIsianDestinasi(string $nama): void
    {
        $cocok = collect($this->katalog()['destinasi'] ?? [])->firstWhere('nama', $nama);

        if ($cocok) {
            $this->isian['provinsi'] = (string) ($cocok['provinsi'] ?? $this->isian['provinsi']);
            $this->isian['daerah'] = (string) ($cocok['daerah'] ?? $this->isian['daerah']);
        }
    }

    public function simpan(): void
    {
        /*
         | Nama rakitan dipakai apa adanya bila kotaknya dibiarkan kosong.
         |
         | Nilainya diisikan ke kotaknya LEBIH DULU, bukan disisipkan saat
         | mengirim: yang tersimpan harus sama dengan yang terlihat di layar.
         | Nama yang baru muncul sesudah menyimpan tidak bisa dikoreksi oleh
         | orang yang tidak pernah melihatnya.
         */
        if (trim((string) $this->isian['nama']) === '') {
            $this->isian['nama'] = $this->namaOtomatis();
        }

        $berkapasitas = $this->perluKapasitas();

        $this->validate([
            'isian.kategori' => 'required|string',
            'isian.vendor_id' => 'nullable',
            'isian.nama' => 'required|string|min:2|max:150',
            'isian.satuan' => 'required|string',
            'isian.harga' => 'required',
            'isian.harga_akhir_pekan' => $this->duaHarga ? 'required' : 'nullable',
            'isian.kapasitas' => $berkapasitas ? 'required|integer|min:1|max:1000' : 'nullable|integer|min:1|max:1000',
            'isian.catatan' => 'nullable|string|max:500',
            // Kosong berarti "tidak ada batas", bukan "belum diisi".
            'isian.berlaku_sampai' => 'nullable|date',
        ], [
            'isian.kapasitas.required' => 'Isi kapasitasnya — tanpanya jumlah unit untuk rombongan besar tidak bisa dihitung.',
            'isian.harga_akhir_pekan.required' => 'Isi harga akhir pekannya, atau matikan saklarnya kalau harganya sama setiap hari.',
            'isian.nama.required' => 'Isi namanya — atau pilih destinasinya, dan namanya terisi sendiri.',
        ], [
            'isian.nama' => 'nama', 'isian.harga' => 'harga', 'isian.kapasitas' => 'kapasitas',
            'isian.harga_akhir_pekan' => 'harga akhir pekan',
        ]);

        $data = [
            'kategori' => $this->isian['kategori'],
            // Kosong dikirim sebagai null, bukan "" — kolomnya kunci asing,
            // dan "" bukan id yang ada di tabel vendor.
            'vendor_id' => ($this->isian['vendor_id'] ?? '') !== '' ? (int) $this->isian['vendor_id'] : null,
            'nama' => trim($this->isian['nama']),
            'provinsi' => $this->isian['provinsi'] ?: null,
            'daerah' => trim($this->isian['daerah']) ?: null,
            'destinasi' => trim($this->isian['destinasi']) ?: null,
            // Hanya berarti bila destinasi utamanya terisi: tiket yang tidak
            // terikat destinasi tidak pernah dijodohkan ke jadwal sama sekali.
            'destinasi_lain' => trim($this->isian['destinasi']) !== '' ? $this->destinasiLain : [],
            'satuan' => $this->isian['satuan'],
            'harga' => $this->angkaDari($this->isian['harga']),
            /*
             | null, BUKAN 0, saat saklarnya mati.
             |
             | Nol adalah harga yang sah — ada tempat yang gratis di hari
             | kerja — jadi mengirim 0 untuk "harganya sama setiap hari"
             | akan menggratiskan tiketnya setiap Sabtu.
             */
            'harga_akhir_pekan' => $this->duaHarga
                ? $this->angkaDari($this->isian['harga_akhir_pekan']) : null,
            /*
             | Tarif musim, satu baris per musim yang ADA di layar.
             |
             | Yang kosong ikut dikirim dengan harga null — itulah yang
             | menghapus tarifnya. Mengirim hanya yang terisi berarti tarif
             | yang dikosongkan admin tetap bertahan di peladen, dan
             | penawaran berikutnya masih memakainya.
             */
            'harga_musim' => $this->bentukHargaMusim(),
            // Satuan yang tidak menghitung unit tidak mengirim kapasitas:
            // kotaknya memang disembunyikan untuk satuan itu, dan nilai yang
            // tidak terlihat tidak boleh ikut tersimpan.
            'kapasitas' => $berkapasitas ? ((int) $this->isian['kapasitas'] ?: null) : null,
            'berlaku_sampai' => ($this->isian['berlaku_sampai'] ?? '') ?: null,
            'otomatis' => $this->otomatis,
            'aktif' => $this->aktif,
            'catatan' => trim($this->isian['catatan']) ?: null,
        ];

        $this->kirimSimpan($data, $this->paksaBerikutnya);
    }

    /**
     * Tarif musim dalam bentuk yang diterima Orcha.
     *
     * Hanya musim yang punya KOTAK di layar — kotak itu dibuat dari daftar
     * musim yang dimuat render(). Mengirim musim yang tidak ada kotaknya
     * berarti menghapus tarif yang tidak pernah dilihat admin.
     *
     * @return array<int, array{musim_id: int, harga: int|null}>
     */
    private function bentukHargaMusim(): array
    {
        $baris = [];

        foreach ($this->hargaMusim as $id => $angka) {
            $angka = is_string($angka) ? trim($angka) : $angka;

            $baris[] = [
                'musim_id' => (int) $id,
                // Kosong = hapus tarifnya. Nol = harga nol, yang sah.
                'harga' => ($angka === '' || $angka === null) ? null : $this->angkaDari($angka),
            ];
        }

        return $baris;
    }

    /** Menyimpan lagi, kali ini menerobos penjaga kembaran. */
    public function simpanPaksa(): void
    {
        $this->paksaBerikutnya = true;
        $this->simpan();
        $this->paksaBerikutnya = false;
    }

    /**
     * Satu jalur kirim untuk simpan biasa maupun simpan paksa.
     *
     * Penolakan "sudah ada harga bernama sama" TIDAK ditampilkan sebagai
     * toast yang hilang sendiri: ia menuntut keputusan — ganti namanya,
     * atau memang dua harga yang berbeda — dan keputusan tidak bisa diambil
     * dari kalimat yang sudah lenyap. Jadi ia tinggal di formulirnya,
     * berikut tombol jalan keluarnya.
     */
    private function kirimSimpan(array $data, bool $paksa): void
    {
        $this->kembaranPesan = '';
        $this->kembaranBaris = [];

        $imbuh = $paksa ? '?paksa=1' : '';

        try {
            $this->sunting
                ? $this->orcha()->ubah("/master-harga/{$this->sunting}{$imbuh}", $data)
                : $this->orcha()->kirim("/master-harga{$imbuh}", $data);

            /*
             | "Lanjut tambah" TETAP di halaman ini; selebihnya pulang.
             |
             | Memasukkan dua puluh tiket berarti dua puluh kali simpan, dan
             | dilempar ke daftar tiap kali berarti dua puluh kali mencari
             | tombol Tambah lagi. Yang kedua pulang karena pekerjaannya
             | memang selesai — dan daftar yang tidak ikut berubah sesudah
             | menyimpan terbaca seperti simpan yang gagal.
             */
            $lanjut = $this->lanjutTambah && ! $this->sunting;

            if ($lanjut) {
                $this->siapkanBarisBerikutnya();
                $this->dispatch('order-updated', message: 'Harga disimpan. Lanjut ke baris berikutnya.');

                return;
            }

            $this->dispatch('orcha-sukses-pindah',
                message: 'Harga disimpan.',
                url: $this->tujuanKembali());
        } catch (OrchaTidakTerjangkau $e) {
            if ($kembar = $e->kembaran()) {
                $this->kembaranPesan = $e->getMessage();
                $this->kembaranBaris = $kembar;

                return;
            }

            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    /**
     * Mengosongkan yang khas baris ini, MEMPERTAHANKAN yang khas sesinya.
     *
     * Memasukkan dua puluh tiket Jogja berarti kategori, provinsi, satuan,
     * dan vendornya sama dua puluh kali. Mengosongkan semuanya tiap kali
     * membuat "tambah lagi" sama lelahnya dengan membuka formulir baru.
     */
    private function siapkanBarisBerikutnya(): void
    {
        $this->isian['nama'] = '';
        $this->isian['destinasi'] = '';
        $this->isian['harga'] = '';
        $this->isian['catatan'] = '';
        $this->resetValidation();
    }

    public function bukaTempel(): void
    {
        $this->tempelBuka = true;
        $this->tempelIsi = '';
        $this->tempelGagal = [];
        $this->resetValidation('tempelIsi');
    }

    public function tutupTempel(): void
    {
        $this->tempelBuka = false;
        $this->tempelIsi = '';
        $this->tempelGagal = [];
    }

    /**
     * Memecah tempelan spreadsheet jadi baris nama + harga.
     *
     * Pemisahnya tab ATAU titik koma — menyalin dari Excel memberi tab,
     * menyalin dari sebagian tabel web memberi titik koma, dan admin tidak
     * bisa diminta tahu bedanya. Angkanya dibersihkan dari "Rp", titik, dan
     * spasi; "Rp 50.000" dan "50000" sama-sama diterima.
     *
     * @return array<int, array<string, mixed>>
     */
    public function barisTempel(): array
    {
        $hasil = [];

        foreach (preg_split('/\r\n|\r|\n/', $this->tempelIsi) as $baris) {
            $baris = trim($baris);

            if ($baris === '') {
                continue;
            }

            /*
             | TIGA cara memisah, dicoba berurutan.
             |
             | Dulu hanya TAB dan titik koma — tepat untuk tempelan Excel,
             | tetapi tidak bisa DIKETIK sama sekali: menekan TAB di dalam
             | kotak teks memindahkan fokus, bukan menyisipkan tab. Admin
             | yang mengetik "Tiket Borobudur,100.000" mendapat "belum ada
             | baris yang terbaca" tanpa tahu apa yang kurang.
             |
             | 1. Ada TAB atau titik koma -> pakai itu. Tempelan Excel tetap
             |    terbaca persis, termasuk nama yang memuat koma.
             | 2. Ada koma -> pakai koma.
             | 3. Tidak ada pemisah apa pun -> angka di UJUNG baris diambil
             |    sebagai harganya, sisanya nama. "Tiket Borobudur 100.000"
             |    pun terbaca.
             */
            if (preg_match('/[\t;]/', $baris)) {
                $bagian = preg_split('/[\t;]/', $baris);
            } elseif (str_contains($baris, ',')) {
                $bagian = explode(',', $baris);
            } elseif (preg_match('/^(.+?)[\s]+([\d][\d.\s]*)$/u', $baris, $c)) {
                $bagian = [$c[1], $c[2]];
            } else {
                continue;
            }

            $nama = trim($bagian[0] ?? '');
            $angka = preg_replace('/[^0-9]/', '', $bagian[1] ?? '');

            if ($nama === '' || $angka === '') {
                continue;
            }

            $hasil[] = [
                'nama' => $nama,
                'harga' => (int) $angka,
                'destinasi' => trim($bagian[2] ?? '') ?: null,
            ];
        }

        return $hasil;
    }

    public function simpanTempel(): void
    {
        $this->resetValidation(['tempelIsi', 'isian.kapasitas']);
        $this->tempelGagal = [];

        $baris = $this->barisTempel();

        if ($baris === []) {
            $this->addError('tempelIsi', 'Belum ada baris yang terbaca. Tiap baris: nama, lalu harganya — dipisah koma atau spasi. Contoh: Tiket Borobudur, 100.000');

            return;
        }

        /*
         | Kapasitas diperiksa DI SINI, bukan dibiarkan ditolak Orcha.
         |
         | Orcha memang menolaknya, tetapi penolakan itu pulang sebagai galat
         | pada kotak tempelan — jauh dari kotak kapasitas yang harus diisi.
         | Menempel dua puluh rute carter tanpa kapasitas membuat KEDUA
         | PULUHNYA menghitung satu kendaraan untuk rombongan sebesar apa pun.
         */
        if ($this->perluKapasitas() && (int) ($this->isian['kapasitas'] ?? 0) < 1) {
            $this->addError('isian.kapasitas', 'Isi kapasitasnya dulu — satuan ini menghitung jumlah unit, dan seluruh baris yang ditempel ikut memakainya.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/master-harga/banyak', [
                'baris' => $baris,
                'kategori' => $this->isian['kategori'],
                'satuan' => $this->isian['satuan'],
                'provinsi' => $this->isian['provinsi'] ?: null,
                'daerah' => trim((string) $this->isian['daerah']) ?: null,
                'vendor_id' => ($this->isian['vendor_id'] ?? '') !== '' ? (int) $this->isian['vendor_id'] : null,
                'kapasitas' => $this->perluKapasitas() ? ((int) $this->isian['kapasitas'] ?: null) : null,
            ]);

            cache()->forget('orcha.rab.katalog');

            // Yang gagal TIDAK ikut hilang bersama panelnya: baris yang
            // dilewati adalah pekerjaan yang masih harus diselesaikan.
            $this->tempelGagal = $balasan['gagal'] ?? [];
            $this->tempelIsi = '';

            if ($this->tempelGagal === []) {
                $this->tutupTempel();
            }

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Harga ditambahkan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->addError('tempelIsi', $e->getMessage());
        }
    }

    /**
     * Tanggal berlaku yang sudah lewat, pada baris yang BARU dibuat.
     *
     * Tidak ditolak — harga lama yang memang sudah kedaluwarsa sah dicatat
     * apa adanya. Tetapi mengetik "2020" saat menambah harga baru hampir
     * selalu salah ketik, dan diterima diam-diam adalah yang membuatnya
     * tidak pernah ketahuan.
     */
    public function berlakuSudahLewat(): bool
    {
        $tanggal = trim((string) ($this->isian['berlaku_sampai'] ?? ''));

        if ($tanggal === '') {
            return false;
        }

        try {
            return \Carbon\Carbon::parse($tanggal)->isBefore(now()->startOfDay());
        } catch (\Throwable) {
            return false;
        }
    }
}
