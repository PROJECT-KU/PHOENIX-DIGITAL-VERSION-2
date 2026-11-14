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
    /**
     * Nama sedang DITULIS SENDIRI oleh admin, bukan dirakit.
     *
     * Kotak namanya terkunci dan terisi sendiri; penanda ini dinyalakan
     * tombol "Ubah nama". Dinyalakan pula saat membuka baris lama yang
     * namanya memang tidak sama dengan rakitannya — kalau tidak, membuka
     * lalu menyimpan baris lama akan MENGGANTI namanya diam-diam, dan nama
     * yang sudah dipakai di penawaran yang terkirim ikut berubah.
     */
    public bool $namaSendiri = false;

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
            // HiAce, Elf, Jeep, Twin, Deluxe. Boleh kosong.
            'tipe' => '',
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
        $this->namaSendiri = false;
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
            'tipe' => (string) ($baris['tipe'] ?? ''),
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

        /*
         | Nama baris lama yang TIDAK sama dengan rakitannya dianggap ditulis
         | sendiri, dan kotaknya dibuka apa adanya.
         |
         | Tanpa ini, membuka lalu menyimpan baris lama akan mengganti
         | namanya diam-diam — termasuk nama yang sudah tercetak di penawaran
         | yang terlanjur dikirim ke pelanggan. Dihitung PALING AKHIR, sesudah
         | seluruh isian & destinasiLain terpasang: rakitannya membaca
         | semuanya.
         */
        $this->namaSendiri = trim((string) $this->isian['nama']) !== $this->namaOtomatis();
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
     * Awalan nama menurut kategorinya.
     *
     * Dipisah dari perakitnya supaya kategori baru cukup ditambahkan satu
     * baris di sini. Kategori yang TIDAK terdaftar sengaja tidak punya
     * awalan: "Lain-lain Sleman" bukan nama apa pun, dan awalan yang
     * dikarang ikut terbaca pelanggan di rincian biaya RAB.
     *
     * Transportasi juga tanpa awalan saat intinya vendor: yang dicari orang
     * di daftar adalah "HiAce Graha Trans", bukan "Sewa HiAce Graha Trans".
     */
    private function awalanKategori(bool $adaDestinasi, bool $intinyaTempat): string
    {
        return match ($this->isian['kategori'] ?? '') {
            'tiket' => $adaDestinasi ? 'Tiket Masuk' : 'Retribusi',
            // "Hotel di Sleman" bila intinya tempat, "Hotel Graha Makmur"
            // bila intinya nama vendor. Kata "di" di depan nama hotel
            // membuat namanya terbaca sebagai alamat.
            'akomodasi' => $intinyaTempat ? 'Hotel di' : 'Hotel',
            'konsumsi' => 'Makan di',
            'pemandu' => 'Pemandu',
            'transportasi' => $intinyaTempat ? 'Sewa' : '',
            'asuransi' => 'Asuransi Perjalanan',
            'dokumentasi' => 'Dokumentasi',
            'operasional' => 'Operasional Tim',
            'sopir' => 'Konsumsi & Rokok Sopir',
            default => '',
        };
    }

    /** Kategori yang barangnya memang MILIK vendor, bukan sebuah tempat. */
    private function intinyaBolehVendor(): bool
    {
        return in_array($this->isian['kategori'] ?? '', ['akomodasi', 'konsumsi', 'transportasi', 'pemandu'], true);
    }

    /**
     * Ukuran barangnya, untuk ditulis dalam kurung.
     *
     *   kamar per malam  →  "(Twin, 2 orang)"  /  "(2 orang)"
     *   unit             →  "(13 kursi)"
     *
     * Hanya satuan berkapasitas yang punya ukuran. "per orang" tidak
     * berukuran — satu orang adalah satu orang.
     */
    private function ukuranNama(string $tipe): string
    {
        if (! $this->perluKapasitas()) {
            return '';
        }

        $muat = (int) ($this->isian['kapasitas'] ?? 0);

        if ($muat < 1) {
            // Tipe tanpa angka tetap berarti: "(Twin)" lebih baik daripada
            // tidak menyebut apa-apa, dan kapasitasnya memang wajib diisi
            // sebelum bisa disimpan.
            return $tipe !== '' ? "({$tipe})" : '';
        }

        $satuan = $this->isian['satuan'] ?? '';
        $isi = $satuan === 'kamar_malam' ? "{$muat} orang" : "{$muat} kursi";

        return $tipe !== '' ? "({$tipe}, {$isi})" : "({$isi})";
    }

    /**
     * Nama yang dirakit dari seluruh isian barisnya.
     *
     *   Hotel Graha Makmur (Twin, 2 orang) Weekday & Weekend | Yogyakarta
     *   HiAce Graha Trans (13 kursi) | Dieng - Jogja
     *   Tiket Masuk Pantai Baron dll 23 tempat | Gunung Kidul
     *   Makan di Loji Breakfast | Banyuwangi
     *   Retribusi Gunung Kidul
     *
     * Urutannya: tipe & inti, ukuran, tempat lain, penanda dua harga, lalu
     * — sesudah garis tegak — tujuan dan DAERAH. Daerah selalu paling
     * belakang, dan itu aturan keras: tidak ada bagian lain yang boleh
     * ditulis sesudahnya.
     *
     * Intinya memakai nama VENDOR bila tidak ada destinasi dan kategorinya
     * memang berbarang milik vendor (hotel, rumah makan, kendaraan,
     * pemandu). Yang dicari orang di daftar adalah nama hotelnya, bukan
     * kotanya.
     *
     * Jumlah tempat menghitung destinasi UTAMA juga: satu tiket untuk 1 + 22
     * pantai memang berlaku di 23 pantai, dan menulis 22 membuat nama
     * barisnya berselisih dengan isinya sendiri.
     */
    public function namaOtomatis(): string
    {
        $tipe = trim((string) ($this->isian['tipe'] ?? ''));
        $destinasi = trim((string) ($this->isian['destinasi'] ?? ''));
        $daerah = trim((string) ($this->isian['daerah'] ?? ''));
        $provinsi = trim((string) ($this->isian['provinsi'] ?? ''));
        $wilayah = $daerah !== '' ? $daerah : $provinsi;
        $vendor = $this->namaVendorTerpilih();

        /*
         | Vendor jadi intinya begitu kategorinya berbarang miliknya —
         | WALAU destinasinya terisi. Carter Jogja–Dieng dinamai dari
         | armadanya ("HiAce Graha Trans"), bukan dari Dieng; Dieng-nya
         | jadi tujuan, ditulis sesudah ukurannya.
         */
        $intiVendor = $vendor !== '' && $this->intinyaBolehVendor();
        $inti = $intiVendor ? $vendor : ($destinasi !== '' ? $destinasi : $wilayah);

        if ($inti === '') {
            return '';
        }

        // Tipe mendahului inti, KECUALI pada akomodasi: di sana ia bagian
        // dari ukurannya ("Twin, 2 orang"), bukan jenis bendanya.
        $tipeDiDepan = $tipe !== '' && ($this->isian['kategori'] ?? '') !== 'akomodasi';

        $bagian = trim(implode(' ', array_filter([
            $this->awalanKategori($destinasi !== '', ! $intiVendor),
            $tipeDiDepan ? $tipe : '',
            $inti,
            $this->ukuranNama($tipeDiDepan ? '' : $tipe),
        ])));

        if ($destinasi !== '' && count($this->destinasiLain) > 0) {
            $bagian .= ' dll '.(count($this->destinasiLain) + 1).' tempat';
        }

        // Dua harga memang perlu disebut di namanya: baris yang tarifnya
        // berbeda akhir pekan sering berdampingan dengan baris tarif tunggal
        // untuk tempat yang sama.
        if ($this->duaHarga) {
            $bagian .= ' Weekday & Weekend';
        }

        /*
         | Blok tempat, dipisah dari nama barangnya dengan garis tegak.
         |
         |   Hotel PO Sumber Jaya (Twin, 2 orang) Weekday & Weekend | Yogyakarta
         |   HiAce Graha Trans (13 kursi) | Dieng - Jogja
         |
         | Satu tanda pemisah untuk dua hal yang berbeda membuat nama
         | panjang tidak terbaca: "HiAce Graha Trans - Dieng - Jogja"
         | menyajikan tiga potongan setara, padahal yang pertama nama
         | barangnya dan dua sisanya tempat. Garis tegak memisahkan APA dari
         | DI MANA; tanda hubung memisahkan tempat dari tempat.
         |
         | Isinya: tujuan lebih dulu (hanya bila intinya vendor — kalau
         | tidak, destinasinya SUDAH jadi intinya), lalu DAERAH, selalu
         | paling belakang. Tidak ada bagian lain yang boleh ditulis
         | sesudah daerah.
         */
        $tempat = [];

        if ($intiVendor && $destinasi !== '') {
            $tempat[] = $destinasi;
        }

        // Dilewati bila namanya sudah memuatnya — "Hotel di Sleman | Sleman"
        // menyebut satu hal dua kali.
        if ($wilayah !== '' && ! str_contains(mb_strtolower($bagian), mb_strtolower($wilayah))) {
            $tempat[] = $wilayah;
        }

        if ($tempat !== []) {
            $bagian .= ' | '.implode(' - ', $tempat);
        }

        // Kolomnya 150 huruf. Dipotong di sini, bukan dibiarkan ditolak
        // validator sesudah admin menekan Simpan.
        return mb_strimwidth($bagian, 0, 150, '…');
    }

    /** Nama vendor yang sedang terpilih, bila ada. */
    private function namaVendorTerpilih(): string
    {
        $id = (int) ($this->isian['vendor_id'] ?? 0);

        if ($id === 0) {
            return '';
        }

        return trim((string) (collect($this->katalog()['vendor'] ?? [])
            ->firstWhere('id', $id)['nama'] ?? ''));
    }

    /**
     * Mengetik di kotak nama menandainya sebagai ditulis sendiri.
     *
     * Kotaknya memang terkunci sampai tombolnya ditekan, jadi di layar
     * jalan ini cuma lewat tombol itu. Tetapi penanda yang hanya dipasang
     * tombol berarti nilai yang sampai lewat jalan lain — tempelan,
     * pengisi otomatis peramban, atau uji — akan ditimpa rakitannya saat
     * Simpan ditekan, tanpa satu pun tanda bahwa yang diketik dibuang.
     *
     * Dikosongkan kembali berarti kembali otomatis: kotak kosong bukan
     * nama yang ditulis sendiri.
     */
    public function updatedIsianNama(): void
    {
        $this->namaSendiri = trim((string) ($this->isian['nama'] ?? '')) !== '';
    }

    /**
     * Membuka kunci kotak nama supaya bisa ditulis sendiri.
     *
     * Isinya TIDAK dikosongkan: yang dimulai dari rakitannya lalu disunting
     * seperlunya jauh lebih sering daripada yang ditulis dari nol, dan
     * kotak yang mendadak kosong berarti mengetik ulang seluruhnya.
     */
    public function ubahNamaSendiri(): void
    {
        $this->namaSendiri = true;

        if (trim((string) ($this->isian['nama'] ?? '')) === '') {
            $this->isian['nama'] = $this->namaOtomatis();
        }
    }

    /** Mengunci kembali, dan namanya kembali mengikuti isian. */
    public function kembaliNamaOtomatis(): void
    {
        $this->namaSendiri = false;
        $this->isian['nama'] = $this->namaOtomatis();
        $this->resetValidation('isian.nama');
    }

    /**
     * Memakai nama rakitan sebagai isi kotaknya — "semi otomatis".
     *
     * Dulu rakitannya hanya dipakai bila kotaknya DIBIARKAN kosong, jadi
     * nama yang sudah terlanjur diketik tidak punya jalan kembali: admin
     * harus menghapusnya huruf demi huruf lebih dulu, dan karena itu
     * kebanyakan dibiarkan apa adanya walau sudah tidak cocok lagi dengan
     * destinasi yang kemudian diganti.
     */
    public function pakaiNamaOtomatis(): void
    {
        $nama = $this->namaOtomatis();

        if ($nama === '') {
            return;
        }

        $this->isian['nama'] = $nama;
        $this->resetValidation('isian.nama');
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

        /*
         | Yang SUDAH terpilih tidak ditimpa.
         |
         | Menambah destinasi ke katalog sambil mengisi borang biasanya
         | dilakukan untuk tempat LAIN yang ikut tiket yang sama — bukan
         | untuk mengganti destinasi utamanya. Dulu kolomnya selalu diisi
         | ulang: admin yang sudah memilih Pantai Baron lalu mendaftarkan
         | Pantai Sembukan mendapati destinasinya berganti tanpa diminta,
         | dan ketahuannya kerap baru setelah tersimpan — harga tiketnya
         | pun berjodoh ke destinasi yang salah.
         |
         | Yang kosong tetap diisi: di sanalah penggantinya memang tidak
         | ada, dan menyuruh admin memilih ulang apa yang baru saja
         | diketiknya hanya pekerjaan dua kali.
         */
        $sudahAda = trim((string) ($this->isian['destinasi'] ?? '')) !== '';

        if (! $sudahAda) {
            $this->isian['destinasi'] = $nama;
        }

        $this->namaDestinasiBaru = '';
        $this->destinasiBaru = false;

        $pesan = $balasan['pesan'] ?? 'Destinasi ditambahkan.';

        if ($sudahAda) {
            $pesan = "\"{$nama}\" masuk daftar destinasi. Kolom Destinasi dibiarkan tetap \"{$this->isian['destinasi']}\".";
        }

        $this->dispatch('order-updated', message: $pesan);
    }

    /**
     * Mengeluarkan satu nama dari katalog destinasi, dari dalam pemilihnya.
     *
     * Katalog ini hanya bertambah sejak ada: salah ketik, nama percobaan,
     * dan destinasi yang batal dipakai menumpuk di kotak pilih selamanya,
     * dan satu-satunya jalan membersihkannya ada di menu lain.
     *
     * Yang dihapus HANYA barisnya di katalog. Baris harga, RAB, dan
     * itinerary menyimpan NAMA destinasinya, bukan id katalog, jadi tidak
     * satu pun dari mereka ikut terhapus — Orcha malah MENOLAK menghapus
     * nama yang masih dipakai salah satunya, dan alasannya diteruskan apa
     * adanya ke layar. Hapusnya sendiri bisa dikembalikan: menambahkan
     * nama yang sama lewat "Destinasinya belum ada di daftar" memulihkan
     * baris yang sama berikut provinsi & daerahnya.
     */
    /**
     * Mengganti nama satu destinasi katalog dari dalam jendela pemilih.
     *
     * Hanya untuk destinasi KUSTOM — baris yang sama dengan yang boleh
     * dihapus. Destinasi bawaan hidup di katalog situs publik Orcha, dan
     * menggantinya dari sini berarti mengubah isi situs tanpa disadari.
     *
     * Perambatan ke baris harga & itinerary dikerjakan di sisi Orcha dalam
     * satu transaksi; di sini yang perlu diurus cuma dua: lupakan katalog
     * yang disimpan sementara, dan ikut membetulkan nama yang SEDANG
     * terpilih di borang ini — kalau tidak, isiannya memegang nama yang
     * baru saja tidak ada lagi.
     */
    public function suntingDestinasiKatalog(int $id, string $nama): void
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama));

        if (mb_strlen($nama) < 3) {
            $this->dispatch('toast-error', message: 'Nama destinasi minimal 3 huruf.');

            return;
        }

        try {
            $balasan = $this->orcha()->ubah("/katalog-destinasi/{$id}", ['nama' => $nama]);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        cache()->forget('orcha.rab.katalog');

        $dari = (string) ($balasan['dari'] ?? '');
        $ke = (string) ($balasan['ke'] ?? $nama);

        if ($dari !== '' && trim((string) ($this->isian['destinasi'] ?? '')) === $dari) {
            $this->isian['destinasi'] = $ke;
        }

        // Destinasi tambahan di borang ini ikut, dengan alasan yang sama.
        foreach (($this->destinasiLain ?? []) as $i => $d) {
            if (trim((string) $d) === $dari) {
                $this->destinasiLain[$i] = $ke;
            }
        }

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Nama destinasi diperbarui.');
    }

    /**
     * Mengganti nama & kontak vendor dari dalam jendela pemilih.
     *
     * Dua medan itu saja, bukan seluruh rekaman vendor: jenis, provinsi, dan
     * catatan punya layarnya sendiri, dan jendela kecil di atas borang harga
     * bukan tempat untuk mengisi lima kolom. Yang benar-benar dibetulkan di
     * sini adalah salah ketik nama dan nomor yang berganti — dan nomor
     * itulah yang ikut terbawa ke RAB.
     */
    public function suntingVendorKatalog(int $id, string $nama, ?string $kontak = null): void
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama));

        if (mb_strlen($nama) < 2) {
            $this->dispatch('toast-error', message: 'Nama vendor minimal 2 huruf.');

            return;
        }

        try {
            $balasan = $this->orcha()->ubah("/vendor/{$id}", [
                'nama' => $nama,
                'kontak' => trim((string) $kontak) ?: null,
            ]);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        cache()->forget('orcha.rab.katalog');

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Vendor diperbarui.');
    }

    /**
     * Menambah destinasi dari dalam jendela pemilih.
     *
     * Membungkus jalur yang sudah ada supaya namanya boleh datang sebagai
     * argumen — jendela tidak punya tempat menitipkan nilai ke properti
     * komponen lebih dulu.
     */
    public function tambahDestinasiDariPopup(string $nama): void
    {
        $this->namaDestinasiBaru = $nama;
        $this->tambahDestinasiKatalog();

        // Galat validasi di sini tidak punya tempat tampil: jendelanya sudah
        // tertutup saat ini berjalan. Disuarakan sebagai dialog gagal.
        if ($this->getErrorBag()->has('namaDestinasiBaru')) {
            $this->dispatch('toast-error', message: $this->getErrorBag()->first('namaDestinasiBaru'));
            $this->resetValidation('namaDestinasiBaru');
        }

        $this->namaDestinasiBaru = '';
    }

    public function tambahVendorDariPopup(string $nama, ?string $kontak = null): void
    {
        $this->namaVendorBaru = $nama;
        $this->kontakVendorBaru = (string) $kontak;
        $this->tambahVendorKatalog();

        if ($this->getErrorBag()->has('namaVendorBaru')) {
            $this->dispatch('toast-error', message: $this->getErrorBag()->first('namaVendorBaru'));
            $this->resetValidation('namaVendorBaru');
        }
    }

    public function hapusDestinasiKatalog(int $id): void
    {
        try {
            $balasan = $this->orcha()->hapus("/katalog-destinasi/{$id}");
        } catch (OrchaTidakTerjangkau $e) {
            // Termasuk penolakan 422 "masih dipakai" — pesannya sudah
            // menyebut di mana dipakainya, jadi diteruskan apa adanya.
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        // Tanpa dilupakan, nama yang baru dihapus masih berdiri di kotak
        // pilihnya sendiri sampai sepuluh menit ke depan — dan tampak
        // seperti hapus yang gagal.
        cache()->forget('orcha.rab.katalog');

        $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Destinasi dihapus dari daftar.');
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
        if (! $this->namaSendiri || trim((string) $this->isian['nama']) === '') {
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
            'tipe' => trim((string) ($this->isian['tipe'] ?? '')) ?: null,
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
                'tipe' => trim((string) ($this->isian['tipe'] ?? '')) ?: null,
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
