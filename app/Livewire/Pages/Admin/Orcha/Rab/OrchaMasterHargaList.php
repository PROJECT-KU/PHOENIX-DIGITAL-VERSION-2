<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Master harga: daftar harga yang dirawat admin dan dipakai setiap RAB.
 *
 * Harga di sini TIDAK mengubah RAB yang sudah ada — RAB membekukan harganya
 * saat baris biaya ditambahkan. Menaikkan tiket Prambanan hari ini hanya
 * berlaku untuk RAB berikutnya; RAB lama cukup diberi tanda "harga master
 * sudah berubah" supaya admin bisa memutuskan sendiri.
 */
class OrchaMasterHargaList extends Component
{
    use IsianRupiah;
    use MemanggilOrcha;

    /*
     | Satuan yang hampir selalu benar untuk tiap kategori.
     |
     | Tiket dihitung per orang, bus per unit per hari, hotel per kamar per
     | malam — pasangan yang nyaris tidak pernah berbeda. Kotak yang
     | jawabannya sudah diketahui dari kotak di sebelahnya bukan pilihan,
     | melainkan penyalinan; dan penyalinan yang dilakukan berpuluh kali
     | sehari adalah tempat salah pilih lahir.
     |
     | Terisinya tetap bisa ditimpa: begitu admin memilih satuannya sendiri,
     | mengganti kategori tidak lagi menimpanya.
     */
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

    /*
     | Pilihan cepat kapasitas, BESERTA namanya.
     |
     | Dulu kotaknya diisi 14 sendiri — angka yang mengandaikan Hiace,
     | padahal transportasi bisa mobil pribadi, Elf, atau bus besar. Tebakan
     | yang salah di sini tidak pernah terlihat salah: angkanya wajar, dan
     | yang meleset cuma jumlah unitnya — jadi RAB-nya kekurangan satu bus
     | tanpa satu pun tanda.
     |
     | Jadi tidak ada lagi yang ditebak. Yang ada pilihan bernama: menekan
     | "Hiace · 14 kursi" lebih cepat daripada mengetik 14, dan yang jauh
     | lebih penting — ia mengatakan angka itu artinya apa.
     */
    private const PILIHAN_KAPASITAS = [
        'unit_hari' => [
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

    #[Url(as: 'kategori', except: '')]
    public string $filterKategori = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    public ?int $sunting = null;

    public bool $tambah = false;

    #[Url(as: 'vendor', except: '')]
    public string $filterVendor = '';

    #[Url(as: 'dipakai', except: '')]
    public string $filterDipakai = '';

    /*
     | Status TIDAK menyembunyikan apa pun saat kosong.
     |
     | Harga yang dimatikan tidak punya kolomnya sendiri — kolom yang isinya
     | sama di hampir setiap baris tidak memberi tahu apa pun. Tetapi
     | membuangnya dari daftar secara diam-diam menukar satu keluhan dengan
     | keluhan yang lebih buruk: "harga saya hilang". Jadi semuanya tetap
     | terlihat, dan saringan ini yang memanggil yang dicari.
     */
    #[Url(as: 'status', except: '')]
    public string $filterStatus = '';

    #[Url(as: 'urut', except: '')]
    public string $urut = '';

    /** Perintah yang akan dijalankan pada baris yang dicentang. */
    public string $aksiMassal = '';

    public string $persenMassal = '';

    public string $berlakuMassal = '';

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

    /** Perintah massal terakhir yang masih bisa dibatalkan. */
    public ?int $batchTerakhir = null;

    public string $batchPesan = '';

    /** Harga yang sedang ditelusuri "dipakai di RAB mana". */
    public ?int $dipakaiUntuk = null;

    public array $dipakai = [];

    public array $dipakaiMeta = [];

    /** Panel "apa saja yang berubah belakangan", seluruh daftar. */
    public bool $riwayatSemuaBuka = false;

    public array $riwayatSemua = [];

    /** Harga yang riwayatnya sedang dibuka; null berarti panelnya tertutup. */
    public ?int $riwayatUntuk = null;

    public array $riwayat = [];

    public array $riwayatMeta = [];

    /** Baris yang dicentang untuk dipasangkan ke satu vendor sekaligus. */
    public array $pilih = [];

    public string $pasangKeVendor = '';

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

    private function kosongkan(): void
    {
        $this->isian = [
            'kategori' => 'tiket',
            'vendor_id' => '',
            'nama' => '',
            'provinsi' => $this->filterProvinsi !== '-' ? $this->filterProvinsi : '',
            'daerah' => '',
            'destinasi' => '',
            'satuan' => 'orang',
            'harga' => '',
            'kapasitas' => '',
            'berlaku_sampai' => '',
            'catatan' => '',
        ];
        $this->aktif = true;
        $this->otomatis = false;
        $this->satuanDisetel = false;
        $this->destinasiBaru = false;
        $this->namaDestinasiBaru = '';
        $this->vendorBaru = false;
        $this->namaVendorBaru = '';
        $this->kontakVendorBaru = '';
    }

    public function mount(): void
    {
        $this->kosongkan();
    }

    public function updatedFilterKategori(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterProvinsi(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterVendor(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterDipakai(): void
    {
        $this->halaman = 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->halaman = 1;
    }

    public function updatedUrut(): void
    {
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterKategori = '';
        $this->filterProvinsi = '';
        $this->filterVendor = '';
        $this->filterDipakai = '';
        $this->filterStatus = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterKategori !== ''
            || $this->filterProvinsi !== '' || $this->filterVendor !== ''
            || $this->filterDipakai !== '' || $this->filterStatus !== '';
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
            'kapasitas' => (string) ($baris['kapasitas'] ?? ''),
            'berlaku_sampai' => (string) ($baris['berlaku_sampai'] ?? ''),
            'catatan' => (string) ($baris['catatan'] ?? ''),
        ];
        $this->aktif = (bool) $baris['aktif'];
        $this->otomatis = (bool) $baris['otomatis'];

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
        $daftar = $this->katalog()['kosakata']['satuan_berkapasitas'] ?? ['unit_hari', 'kamar_malam'];

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
            'isian.kapasitas' => $berkapasitas ? 'required|integer|min:1|max:1000' : 'nullable|integer|min:1|max:1000',
            'isian.catatan' => 'nullable|string|max:500',
            // Kosong berarti "tidak ada batas", bukan "belum diisi".
            'isian.berlaku_sampai' => 'nullable|date',
        ], [
            'isian.kapasitas.required' => 'Isi kapasitasnya — tanpanya jumlah unit untuk rombongan besar tidak bisa dihitung.',
            'isian.nama.required' => 'Isi namanya — atau pilih destinasinya, dan namanya terisi sendiri.',
        ], [
            'isian.nama' => 'nama', 'isian.harga' => 'harga', 'isian.kapasitas' => 'kapasitas',
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
            'satuan' => $this->isian['satuan'],
            'harga' => $this->angkaDari($this->isian['harga']),
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

            $lanjut = $this->lanjutTambah && ! $this->sunting;

            if ($lanjut) {
                $this->siapkanBarisBerikutnya();
            } else {
                $this->tutup();
            }

            $this->dispatch('order-updated', message: $lanjut
                ? 'Harga disimpan. Lanjut ke baris berikutnya.'
                : 'Harga disimpan.');
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

    /**
     * Memasang satu vendor ke beberapa harga sekaligus.
     *
     * Satu vendor biasanya memasok beberapa harga — Hiace, Elf, dan bus
     * besar dari pemilik armada yang sama. Lewat formulir, memasangnya
     * berarti membuka, menyimpan, dan menutup sebanyak barisnya, untuk satu
     * keputusan yang sama.
     */
    public function pasangVendor(): void
    {
        $this->resetValidation('pasangKeVendor');

        if ($this->pilih === [] || $this->pasangKeVendor === '') {
            $this->addError('pasangKeVendor', 'Centang harganya dulu, lalu pilih vendornya.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/vendor/{$this->pasangKeVendor}/pasang", [
                'harga' => array_map('intval', $this->pilih),
            ]);

            cache()->forget('orcha.rab.katalog');
            $this->pilih = [];
            $this->pasangKeVendor = '';
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Vendor dipasangkan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function batalPilih(): void
    {
        $this->pilih = [];
        $this->pasangKeVendor = '';
        $this->aksiMassal = '';
        $this->persenMassal = '';
        $this->berlakuMassal = '';
        $this->resetValidation(['pasangKeVendor', 'aksiMassal', 'persenMassal']);
    }

    /**
     * Satu perintah untuk semua baris yang dicentang.
     *
     * Yang dipulangkan Orcha bukan sekadar "berhasil" melainkan BERAPA baris
     * yang benar-benar tersentuh, dan itu yang ditampilkan — menjanjikan
     * "12 harga dinaikkan" padahal dua di antaranya tidak berubah membuat
     * admin berhenti mempercayai pesannya.
     */
    public function jalankanMassal(): void
    {
        $this->resetValidation(['aksiMassal', 'persenMassal']);

        if ($this->pilih === []) {
            $this->addError('aksiMassal', 'Centang dulu harga yang mau diubah.');

            return;
        }

        if ($this->aksiMassal === '') {
            $this->addError('aksiMassal', 'Pilih dulu mau diapakan.');

            return;
        }

        if (in_array($this->aksiMassal, ['naik', 'turun'], true) && ! $this->persenSah()) {
            $this->addError('persenMassal', 'Isi persennya, antara 0,1 sampai 90.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/master-harga/massal', array_filter([
                'id' => array_map('intval', $this->pilih),
                'aksi' => $this->aksiMassal,
                'persen' => $this->persenSah() ? (float) str_replace(',', '.', $this->persenMassal) : null,
                'berlaku_sampai' => $this->berlakuMassal ?: null,
            ], fn ($n) => $n !== null));

            cache()->forget('orcha.rab.katalog');
            $this->batalPilih();

            /*
             | Tawaran "batalkan" muncul SEKARANG, menempel pada perintah yang
             | baru saja jalan. Tawaran yang datang belakangan — di layar lain,
             | di menu lain — tidak pernah dipakai, karena saat orang sadar
             | salah, yang dicarinya tombol di tempat ia menekan tadi.
             */
            $this->batchTerakhir = $balasan['batch'] ?? null;
            $this->batchPesan = $balasan['pesan'] ?? '';

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Perubahan tersimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    private function persenSah(): bool
    {
        $angka = (float) str_replace(',', '.', $this->persenMassal);

        return $this->persenMassal !== '' && $angka >= 0.1 && $angka <= 90;
    }

    /** Baris mana saja yang terpengaruh perintah massal — dipakai di kalimat pengesahannya. */
    public function jumlahPilih(): int
    {
        return count($this->pilih);
    }

    /**
     * Mencentang seluruh baris yang sedang TERLIHAT, bukan seluruh tabel.
     *
     * Mencentang baris yang tidak kelihatan adalah cara tercepat mematikan
     * harga yang tidak pernah dilihat orangnya.
     */
    public function pilihSemua(array $id): void
    {
        $this->pilih = $this->pilih === [] ? array_map('strval', $id) : [];
    }

    /**
     * Membuka formulir berisi salinan sebuah harga, sebagai baris BARU.
     *
     * Hiace dari dua vendor berbeda hanya berbeda di satu kotak; mengetik
     * ulang delapan kotak lainnya adalah delapan kesempatan salah ketik.
     * Namanya diberi "(salinan)" supaya penjaga kembaran di Orcha tidak
     * menolaknya, sekaligus supaya yang lupa menggantinya tetap terbaca.
     */
    public function salin(array $baris): void
    {
        $this->bukaSunting(0, $baris);

        $this->sunting = null;
        $this->tambah = true;
        $this->isian['nama'] = trim($baris['nama'].' (salinan)');
    }

    /** Riwayat perubahan harga satu baris. */
    public function bukaRiwayat(int $id): void
    {
        try {
            $balasan = $this->orcha()->ambil("/master-harga/{$id}/riwayat");

            $this->riwayatUntuk = $id;
            $this->riwayat = $balasan['data'] ?? [];
            $this->riwayatMeta = $balasan['meta'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupRiwayat(): void
    {
        $this->riwayatUntuk = null;
        $this->riwayat = [];
        $this->riwayatMeta = [];
    }

    /** Mengembalikan seluruh baris perintah massal terakhir ke nilai lamanya. */
    public function batalkanBatch(): void
    {
        if ($this->batchTerakhir === null) {
            return;
        }

        try {
            $balasan = $this->orcha()->kirim("/master-harga/massal/{$this->batchTerakhir}/batal");

            cache()->forget('orcha.rab.katalog');
            $this->batchTerakhir = null;
            $this->batchPesan = '';
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Dikembalikan seperti semula.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function lupakanBatch(): void
    {
        $this->batchTerakhir = null;
        $this->batchPesan = '';
    }

    /** RAB mana saja yang memakai sebuah harga. */
    public function bukaDipakai(int $id): void
    {
        try {
            $balasan = $this->orcha()->ambil("/master-harga/{$id}/dipakai");

            $this->dipakaiUntuk = $id;
            $this->dipakai = $balasan['data'] ?? [];
            $this->dipakaiMeta = $balasan['meta'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupDipakai(): void
    {
        $this->dipakaiUntuk = null;
        $this->dipakai = [];
        $this->dipakaiMeta = [];
    }

    /** "Apa saja yang berubah belakangan" — seluruh daftar, bukan satu baris. */
    public function bukaRiwayatSemua(): void
    {
        try {
            $this->riwayatSemua = $this->orcha()->ambil('/master-harga/riwayat')['data'] ?? [];
            $this->riwayatSemuaBuka = true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupRiwayatSemua(): void
    {
        $this->riwayatSemuaBuka = false;
        $this->riwayatSemua = [];
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

            $bagian = preg_split('/\t|;/', $baris);
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
        $this->resetValidation('tempelIsi');
        $this->tempelGagal = [];

        $baris = $this->barisTempel();

        if ($baris === []) {
            $this->addError('tempelIsi', 'Belum ada baris yang terbaca. Tiap baris: nama, lalu TAB, lalu harganya.');

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

    public function hapus(int $id): void
    {
        $this->hapusData("/master-harga/{$id}", 'Harga dihapus. RAB yang sudah memakainya tidak berubah.');
    }

    /** Provinsi & destinasi untuk pilihan; disimpan sebentar karena jarang berubah. */
    private function katalog(): array
    {
        $simpan = cache()->get('orcha.rab.katalog');

        if (is_array($simpan) && $simpan !== []) {
            return $simpan;
        }

        try {
            $data = $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }

        if ($data !== []) {
            cache()->put('orcha.rab.katalog', $data, now()->addMinutes(10));
        }

        return $data;
    }

    public function render()
    {
        $hasil = $this->muat('/master-harga', $this->parameterDaftar([
            'kategori' => $this->filterKategori,
            'provinsi' => $this->filterProvinsi,
            'vendor' => $this->filterVendor,
            'dipakai' => $this->filterDipakai,
            'status' => $this->filterStatus,
            'urut' => $this->urut,
        ]));

        $katalog = $this->katalog();
        $kosakata = $hasil['meta']['kosakata'] ?? $katalog['kosakata'] ?? [];

        // Destinasi yang ditawarkan di formulir mengikuti provinsi terpilih —
        // daftar 70 destinasi seluruh Indonesia untuk tiket Jogja hanya
        // menambah gulungan.
        $destinasi = collect($katalog['destinasi'] ?? [])
            ->when($this->isian['provinsi'] ?? '', fn ($c, $p) => $c->where('provinsi', $p))
            ->pluck('nama')->values()->all();

        /*
         | Nama destinasi di katalog, SELURUH provinsi — untuk menandai baris
         | yang namanya tidak ada di sana.
         |
         | Penjodohan harga tiket ke itinerary memakai nama ini persis. Satu
         | huruf berbeda ("prambanan" vs "Candi Prambanan") membuat tiketnya
         | tidak pernah ikut tertarik, dan RAB-nya kekurangan modal tanpa
         | satu pun tanda — kesalahan yang baru ketahuan saat uangnya kurang.
         */
        $semuaNama = collect($katalog['destinasi'] ?? [])->pluck('nama')->all();

        return view('livewire.pages.admin.orcha.rab.master-harga', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            // Dihitung Orcha dari SELURUH tabel, bukan dari halaman ini.
            'ringkas' => $hasil['meta']['ringkas'] ?? [],
            'kategori' => $kosakata['kategori'] ?? [],
            'satuan' => $kosakata['satuan'] ?? [],
            'provinsi' => $katalog['provinsi'] ?? [],
            'destinasi' => $destinasi,
            'namaKatalog' => $semuaNama,
            'vendor' => $katalog['vendor'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
