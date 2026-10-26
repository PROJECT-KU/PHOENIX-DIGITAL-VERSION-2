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

    /** Kotak isian destinasi baru sedang terbuka. */
    public bool $destinasiBaru = false;

    public string $namaDestinasiBaru = '';

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
            'catatan' => '',
        ];
        $this->aktif = true;
        $this->otomatis = false;
        $this->satuanDisetel = false;
        $this->destinasiBaru = false;
        $this->namaDestinasiBaru = '';
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

    public function bersihkanSaringan(): void
    {
        $this->cari = '';
        $this->filterKategori = '';
        $this->filterProvinsi = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterKategori !== '' || $this->filterProvinsi !== '';
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
            'otomatis' => $this->otomatis,
            'aktif' => $this->aktif,
            'catatan' => trim($this->isian['catatan']) ?: null,
        ];

        try {
            $this->sunting
                ? $this->orcha()->ubah("/master-harga/{$this->sunting}", $data)
                : $this->orcha()->kirim('/master-harga', $data);

            $this->tutup();
            $this->dispatch('order-updated', message: 'Harga disimpan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
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
            'kategori' => $kosakata['kategori'] ?? [],
            'satuan' => $kosakata['satuan'] ?? [],
            'provinsi' => $katalog['provinsi'] ?? [],
            'destinasi' => $destinasi,
            'namaKatalog' => $semuaNama,
            'vendor' => $katalog['vendor'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
