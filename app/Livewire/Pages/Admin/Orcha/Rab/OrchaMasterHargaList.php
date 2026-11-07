<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Rab\Concerns\MembacaKatalogOrcha;
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
    use MembacaKatalogOrcha;

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

    #[Url(as: 'kategori', except: '')]
    public string $filterKategori = '';

    #[Url(as: 'provinsi', except: '')]
    public string $filterProvinsi = '';

    #[Url(as: 'vendor', except: '')]
    public string $filterVendor = '';

    #[Url(as: 'dipakai', except: '')]
    public string $filterDipakai = '';

    /*
     | $filterStatus TIDAK dideklarasikan di sini.
     |
     | Ia sudah milik trait MemanggilOrcha, lengkap dengan #[Url] dan
     | pengirimannya sebagai parameter `status`. Mendeklarasikannya ulang
     | akan MENIMPA atribut trait-nya diam-diam — PHP mengizinkannya selama
     | tipe & nilai awalnya sama, jadi tidak ada galat apa pun. Layar Vendor
     | memakai nama $filterStatus2 justru untuk menghindari bentrok ini.
     |
     | Artinya saat kosong daftarnya memuat SEMUANYA: menyembunyikan yang
     | mati diam-diam menukar satu keluhan dengan "harga saya hilang".
     */
    #[Url(as: 'urut', except: '')]
    public string $urut = '';

    public function render()
    {
        $hasil = $this->muat('/master-harga', $this->parameterDaftar([
            'kategori' => $this->filterKategori,
            'provinsi' => $this->filterProvinsi,
            'vendor' => $this->filterVendor,
            'dipakai' => $this->filterDipakai,
            // 'status' tidak ditulis di sini: parameterDaftar() di trait
            // sudah mengirimnya dari $filterStatus yang sama.
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

    /** Perintah yang akan dijalankan pada baris yang dicentang. */
    public string $aksiMassal = '';

    public string $persenMassal = '';

    public string $berlakuMassal = '';

    /*
     | Berapa baris per halaman.
     |
     | Sepuluh benar untuk membaca sekilas, dan salah begitu daftarnya
     | diurutkan: "mana yang paling mahal" dijawab dengan menggulir dua
     | puluh halaman. Pilihannya dibatasi supaya tidak ada yang meminta
     | seribu baris sekaligus.
     */
    #[Url(as: 'baris', except: 10)]
    public int $barisHalaman = 10;

    /** Panel salin harga antar provinsi. */
    public bool $salinBuka = false;

    public string $salinDari = '';

    public string $salinKe = '';

    /** Berapa hari ke belakang yang ditampilkan panel "Perubahan terakhir". */
    public int $riwayatHari = 0;

    public array $riwayatSemuaMeta = [];

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

    public function updatedBarisHalaman(): void
    {
        $this->halaman = 1;
    }

    /**
     * Menimpa bawaan trait (10) dengan pilihan admin.
     *
     * Dibatasi ke daftar yang dikenal, bukan dipercaya apa adanya: angka
     * ini datang dari URL, dan "?baris=100000" akan meminta Orcha
     * memulangkan seluruh tabel dalam satu permintaan.
     */
    protected function perHalaman(): int
    {
        return in_array($this->barisHalaman, [10, 25, 50, 100], true) ? $this->barisHalaman : 10;
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
        return $this->cari !== '' || $this->jumlahSaringLanjut() > 0;
    }

    /**
     * Berapa saringan di balik tombol "Saringan lainnya" yang sedang aktif.
     *
     * Kotak cari TIDAK dihitung: ia tetap terlihat, jadi tidak ada yang
     * perlu diberitahukan tentangnya. Yang dihitung hanya yang tersembunyi
     * — saringan yang bekerja tanpa terlihat membuat daftar yang "kurang"
     * terbaca sebagai data hilang, dan angkanya di tombol itulah yang
     * menjelaskan ke mana sisanya pergi.
     *
     * Urutan TIDAK dihitung: ia menata, bukan membuang baris.
     */
    public function jumlahSaringLanjut(): int
    {
        return count(array_filter([
            $this->filterKategori, $this->filterProvinsi, $this->filterVendor,
            $this->filterDipakai, $this->filterStatus,
        ], fn ($n) => (string) $n !== ''));
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
    public function salin(int $id): void
    {
        /*
         | Membuka HALAMAN tambah berisi salinan, bukan formulir di tempat.
         |
         | Baris yang disalin dibaca ulang dari Orcha di sana — mengirimnya
         | lewat alamat berarti menitipkan sembilan kolom di query string,
         | dan yang terkirim lewat alamat bisa disunting siapa pun yang
         | menempelkannya.
         */
        $this->redirect(route('admin.orcha.master-harga.tambah', ['salin' => $id]), navigate: true);
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
            $balasan = $this->orcha()->ambil('/master-harga/riwayat', array_filter([
                'hari' => $this->riwayatHari ?: null,
                'batas' => 100,
            ]));

            $this->riwayatSemua = $balasan['data'] ?? [];
            $this->riwayatSemuaMeta = $balasan['meta'] ?? [];
            $this->riwayatSemuaBuka = true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupRiwayatSemua(): void
    {
        $this->riwayatSemuaBuka = false;
        $this->riwayatSemua = [];
        $this->riwayatSemuaMeta = [];
    }

    public function updatedRiwayatHari(): void
    {
        if ($this->riwayatSemuaBuka) {
            $this->bukaRiwayatSemua();
        }
    }

    /**
     * Mencentang SELURUH baris yang cocok saringan, bukan yang terlihat.
     *
     * Id-nya diminta ke Orcha, bukan dirakit dari halaman ini — yang
     * terlihat cuma sepuluh dari seratus, dan mencentang yang terlihat lalu
     * menyebutnya "semua" adalah janji yang tidak ditepati.
     */
    public function pilihSemuaCocok(): void
    {
        try {
            $balasan = $this->orcha()->ambil('/master-harga/id-cocok', $this->parameterDaftar([
                'kategori' => $this->filterKategori,
                'provinsi' => $this->filterProvinsi,
                'vendor' => $this->filterVendor,
                'dipakai' => $this->filterDipakai,
            ]));

            $this->pilih = array_map('strval', $balasan['data'] ?? []);
            $this->dispatch('order-updated', message: count($this->pilih).' harga dicentang — seluruh yang cocok saringan.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function bukaSalin(): void
    {
        $this->salinBuka = true;
        $this->salinDari = $this->filterProvinsi !== '-' ? $this->filterProvinsi : '';
        $this->salinKe = '';
        $this->resetValidation(['salinDari', 'salinKe']);
    }

    public function tutupSalin(): void
    {
        $this->salinBuka = false;
        $this->salinDari = '';
        $this->salinKe = '';
        $this->resetValidation(['salinDari', 'salinKe']);
    }

    /** Menyalin seluruh harga satu provinsi ke provinsi lain. */
    public function salinProvinsi(): void
    {
        $this->resetValidation(['salinDari', 'salinKe']);

        if ($this->salinDari === '' || $this->salinKe === '') {
            $this->addError('salinKe', 'Pilih provinsi asal dan tujuannya.');

            return;
        }

        if ($this->salinDari === $this->salinKe) {
            $this->addError('salinKe', 'Provinsi tujuannya sama dengan asalnya.');

            return;
        }

        try {
            $balasan = $this->orcha()->kirim('/master-harga/salin-provinsi', [
                'dari' => $this->salinDari, 'ke' => $this->salinKe,
            ]);

            cache()->forget('orcha.rab.katalog');
            $this->tutupSalin();
            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Harga disalin.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->addError('salinKe', $e->getMessage());
        }
    }

    /**
     * Menekan judul kolom untuk mengurutkan.
     *
     * Dua arah dalam satu tombol: menekan kolom yang sedang dipakai
     * membalik urutannya, menekan kolom lain pindah ke sana. Itu yang
     * dicoba orang lebih dulu, sebelum kotak "Urut" ditemukan.
     */
    public function urutkanLewatKolom(string $kolom): void
    {
        $pasangan = [
            'harga' => ['termahal', 'termurah'],
            'dipakai' => ['tersering', ''],
            'diubah' => ['terbaru', 'terlama'],
        ];

        if (! isset($pasangan[$kolom])) {
            return;
        }

        [$pertama, $kedua] = $pasangan[$kolom];
        $this->urut = $this->urut === $pertama ? $kedua : $pertama;
        $this->halaman = 1;
    }

    /** Arah panah pada judul kolom; null berarti kolomnya tidak sedang dipakai. */
    public function arahUrut(string $kolom): ?string
    {
        return match ($kolom) {
            'harga' => match ($this->urut) {
                'termahal' => 'turun',
                'termurah' => 'naik',
                default => null,
            },
            'dipakai' => $this->urut === 'tersering' ? 'turun' : null,
            'diubah' => match ($this->urut) {
                'terbaru' => 'turun',
                'terlama' => 'naik',
                default => null,
            },
            default => null,
        };
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/master-harga/{$id}", 'Harga dihapus. RAB yang sudah memakainya tidak berubah.');
    }
}
