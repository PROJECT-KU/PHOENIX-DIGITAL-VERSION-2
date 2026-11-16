<?php

namespace App\Livewire\Pages\Admin\Orcha\PaketWisata;

use App\Livewire\Pages\Admin\Orcha\Concerns\IsianRupiah;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tambah / ubah paket wisata Orcha dari lemon.
 *
 * Datanya tidak disimpan di sini — dikirim ke server Orcha lewat API, termasuk
 * fotonya. Paket yang sudah punya pendaftar ditolak Orcha bila dihapus.
 *
 * Tiga isian terberat (destinasi, fasilitas, itinerary) sengaja dibuat berupa
 * pilihan dan baris yang bisa ditambah, bukan kotak teks bebas: admin mengisi
 * paket berkali-kali dengan isi yang mirip, dan mengetik ulang itu sumber salah
 * ketik.
 */
class OrchaPaketForm extends Component
{
    use IsianRupiah, MemanggilOrcha, WithFileUploads;

    public ?int $paketId = null;

    public bool $ubah = false;

    public string $nama = '';

    public string $kategori = 'open_trip';

    public string $status = 'terbit';

    public string $tayangMulai = '';

    public string $tayangSampai = '';

    public bool $berakhirOtomatis = true;

    /**
     * Ikut promo rombongan?
     *
     * Tingkat promonya seragam untuk seluruh perusahaan, tetapi tidak setiap
     * trip ikut: sebagian sudah tipis marginnya, sebagian sedang musim ramai
     * dan tidak perlu didorong.
     *
     * Bawaannya MATI. Paket baru tidak boleh diam-diam memberi potongan hanya
     * karena yang membuatnya tidak memperhatikan satu centang.
     */
    public bool $promoRombongan = false;

    public string $durasi = '';

    /**
     * Selama benar, durasi ikut tanggal berangkat/pulang. Begitu admin
     * mengetiknya sendiri, hitungan berhenti mengganggu — sebagian paket
     * memang perlu tulisan khusus, mis. "2 Hari 1 Malam (opsional extend)".
     */
    public bool $durasiOtomatis = true;

    public string $tanggalBerangkat = '';

    public string $tanggalPulang = '';

    public string $titikJemput = '';

    public int $minimalPeserta = 6;

    /**
     * Berapa kursi yang dijual. Kosong berarti BELUM DITETAPKAN, bukan nol.
     *
     * Bedanya penting: paket yang kuotanya kosong berperilaku persis seperti
     * sebelum kolom ini ada — pendaftarannya tidak pernah ditutup sistem.
     * Kalau kosong diperlakukan nol, seluruh paket lama langsung penuh.
     */
    public $kuota = null;

    public string $catatanPromo = '';

    public $harga = 0;

    public $hargaAsli = 0;

    /**
     * Modal per orang: biaya internal yang sudah dihitung untuk satu peserta.
     *
     * Kosong berarti belum dihitung, dan itu keadaan yang sah — private trip
     * sering dibuat paketnya dulu, modalnya menyusul setelah penawaran hotel
     * masuk. Yang tidak boleh adalah menganggapnya nol: laporan keuntungan
     * memisahkan keduanya, dan nol berarti paket ini tidak berbiaya.
     */
    public $hargaModal = null;

    /** Bentuk bertitik yang tampil di layar; angkanya di $harga/$hargaAsli. */
    public string $hargaTeks = '';

    public string $hargaAsliTeks = '';

    public string $hargaModalTeks = '';

    public $diskonPersen = 0;

    /**
     * Selama benar, diskon ikut selisih harga. Berhenti begitu admin
     * mengisinya sendiri — sebagian promo memakai angka bulat yang tidak
     * persis sama dengan hitungan.
     */
    public bool $diskonOtomatis = true;

    public bool $pilihanTerbaik = false;

    /** @var array<int, string> */
    public array $destinasi = [];

    /** @var array<int, string> */
    public array $fasilitas = [];

    /** Isian tambahan untuk yang belum ada di daftar pilihan. */
    public string $destinasiBaru = '';

    public string $fasilitasBaru = '';

    /**
     * Lawan dari fasilitas: yang TIDAK didapat peserta.
     *
     * Daftarnya sendiri, bukan diselipkan jadi catatan: tiket masuk dan
     * pengeluaran pribadi yang baru ketahuan di lokasi selalu berakhir jadi
     * keluhan — bukan karena harganya, melainkan karena tidak pernah disebut.
     *
     * @var array<int, string>
     */
    public array $tidakTermasuk = [];

    public string $tidakTermasukBaru = '';

    /**
     * Titik jemput sebagai DAFTAR, meski Orcha menyimpannya satu kalimat.
     *
     * Bentuk simpanannya dipertahankan karena belasan tempat lain membacanya
     * begitu; yang berubah cuma cara mengisinya. Diketik bebas, "Jogja" dan
     * "Yogyakarta" yang bermaksud sama jadi dua titik berbeda di daftar
     * peserta, dan yang menemukannya cuma kebetulan.
     *
     * @var array<int, string>
     */
    public array $titikJemputDaftar = [];

    public string $titikJemputBaru = '';

    /**
     * Itinerary sebagai baris yang bisa ditambah:
     * [['nama' => 'Hari 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => '...']]]]
     *
     * @var array<int, array{nama: string, agenda: array<int, array{jam: string, kegiatan: string}>}>
     */
    public array $hari = [];

    /**
     * Tanggal keberangkatan paket ini, apa adanya dari Orcha.
     *
     * Dibaca saja — menambah, menyalin, mengubah, dan menutupnya masing
     * masing satu panggilan tersendiri. Menumpangkannya pada borang paket
     * berarti satu simpan mengirim dua macam perubahan sekaligus, dan yang
     * gagal separuh tidak punya cara memberi tahu bagian mana yang masuk.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $keberangkatan = [];

    /** Tanggal yang sedang diisi di baris "tambah". */
    public string $kbTanggal = '';

    public string $kbTanggalPulang = '';

    /** Keberangkatan yang sedang disalin; null berarti sedang menambah baru. */
    public ?int $kbSalinDari = null;

    /**
     * Harga & kuota untuk tanggal yang sedang diisi.
     *
     * KOSONG berarti ikut paket — itu yang paling sering benar, dan karena
     * itu dibiarkan kosong alih-alih diisi angka paket. Angka yang disalin
     * akan tertinggal diam-diam begitu harga paketnya diubah, dan selisihnya
     * baru ketahuan saat ada yang membayar dengan angka lama.
     *
     * Sebelum ada dua isian ini, basis data sudah mendukung harga per
     * tanggal dan keterangan di layar sudah menyebutnya — tetapi tidak ada
     * satu pun kotak untuk mengisinya.
     */
    public string $kbHargaTeks = '';

    public string $kbKuota = '';

    /** Tanggal yang sedang disunting; null berarti tidak ada. */
    public ?int $kbUbahId = null;

    /* -------------------------------- GALERI -------------------------------- */

    /**
     * Foto galeri yang SUDAH tersimpan di Orcha, apa adanya.
     *
     * @var array<int, string>
     */
    public array $galeriLama = [];

    /**
     * Foto baru yang menunggu dikirim.
     *
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $galeriBaru = [];

    public $gambar;

    public ?string $gambarLama = null;

    /** Tautan halaman paket di website, untuk memeriksa hasil hero-nya. */
    public ?string $tautanPublik = null;

    /**
     * Tautan pratinjau bertanda tangan dari Orcha.
     *
     * Dipakai tombol "Lihat halamannya" supaya paket yang belum tayang tetap
     * bisa diperiksa rupanya. Tanpa ini tombol itu mati justru untuk paket
     * yang paling perlu dilihat — yang belum selesai.
     */
    public ?string $tautanPratinjau = null;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'status' => 'required|string',
            'tayangMulai' => 'nullable|date',
            'tayangSampai' => 'nullable|date|after_or_equal:tayangMulai',
            'durasi' => 'nullable|string|max:60',
            'tanggalBerangkat' => 'nullable|date',
            'tanggalPulang' => 'nullable|date|after_or_equal:tanggalBerangkat',
            'titikJemput' => 'nullable|string|max:191',
            'minimalPeserta' => 'required|integer|min:1|max:200',
            // Batas bawahnya minimal peserta: kuota yang lebih kecil daripada
            // jumlah minimum keberangkatan berarti tripnya tidak akan pernah
            // bisa berangkat, dan itu pasti salah ketik.
            'kuota' => 'nullable|integer|min:1|max:500|gte:minimalPeserta',
            'catatanPromo' => 'nullable|string|max:191',
            'harga' => 'required|numeric|min:0',
            'hargaAsli' => 'nullable|numeric|min:0',
            'hargaModal' => 'nullable|numeric|min:0',
            'diskonPersen' => 'nullable|numeric|min:0|max:100',
            'gambar' => 'nullable|image|max:4096',

            // Galeri: batasnya lebih longgar daripada sampul karena fotonya
            // memang foto perjalanan, bukan gambar kartu yang dipotong.
            'galeriBaru' => 'nullable|array|max:12',
            'galeriBaru.*' => 'image|max:8192',

            'tidakTermasuk' => 'nullable|array',
            'tidakTermasuk.*' => 'string|max:191',
            'titikJemputDaftar' => 'nullable|array',
            'titikJemputDaftar.*' => 'string|max:191',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'nama paket',
            'minimalPeserta' => 'minimal peserta',
            'kuota' => 'jumlah kursi',
            'tanggalBerangkat' => 'tanggal berangkat',
            'tanggalPulang' => 'tanggal pulang',
            'hargaAsli' => 'harga asli',
            'hargaModal' => 'modal per orang',
            'diskonPersen' => 'diskon',
            'tayangMulai' => 'mulai tampil',
            'tayangSampai' => 'berhenti tampil',
            'galeriBaru' => 'foto galeri',
            'galeriBaru.*' => 'foto galeri',
            'titikJemputDaftar' => 'titik jemput',
        ];
    }

    /**
     * Pesan galat dalam Bahasa Indonesia.
     *
     * Tanpa ini Laravel memakai pesan bawaannya, dan karena APP_LOCALE=en di
     * server, yang terbaca admin adalah "The nama paket field is required."
     * Admin yang memakai layar ini bukan orang teknis: kalimat Inggris pada
     * saat mereka sedang salah justru membuat mereka berhenti, bukan
     * memperbaiki. Tidak ada folder lang/ di proyek ini, jadi pesannya
     * ditulis di tempat yang memakainya.
     *
     * Kalimatnya menyebut APA YANG HARUS DILAKUKAN, bukan aturan mana yang
     * dilanggar — "paling banyak 255 huruf", bukan "max:255".
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nama.required' => 'Nama paketnya belum diisi.',
            'nama.max' => 'Nama paketnya kepanjangan — paling banyak 255 huruf.',

            'kategori.required' => 'Kategorinya belum dipilih.',
            'status.required' => 'Statusnya belum dipilih.',

            'tayangMulai.date' => 'Tanggal "Mulai tampil" belum benar. Pilih lewat kotak tanggalnya.',
            'tayangSampai.date' => 'Tanggal "Berhenti tampil" belum benar. Pilih lewat kotak tanggalnya.',
            'tayangSampai.after_or_equal' => 'Tanggal berhenti tampil tidak boleh lebih awal daripada tanggal mulai tampil.',

            'durasi.max' => 'Lama tripnya kepanjangan — paling banyak 60 huruf.',

            'tanggalBerangkat.date' => 'Tanggal berangkatnya belum benar. Pilih lewat kotak tanggalnya.',
            'tanggalPulang.date' => 'Tanggal pulangnya belum benar. Pilih lewat kotak tanggalnya.',
            'tanggalPulang.after_or_equal' => 'Tanggal pulang tidak boleh lebih awal daripada tanggal berangkat.',

            'titikJemput.max' => 'Titik jemputnya kepanjangan — paling banyak 191 huruf.',

            'minimalPeserta.required' => 'Minimal pesertanya belum diisi.',
            'minimalPeserta.integer' => 'Minimal peserta harus berupa angka, tanpa titik atau koma.',
            'minimalPeserta.min' => 'Minimal peserta paling sedikit 1 orang.',
            'minimalPeserta.max' => 'Minimal peserta paling banyak 200 orang.',

            'kuota.integer' => 'Jumlah kursi harus berupa angka, tanpa titik atau koma.',
            'kuota.min' => 'Jumlah kursi paling sedikit 1.',
            'kuota.max' => 'Jumlah kursi paling banyak 500.',
            'kuota.gte' => 'Jumlah kursi tidak boleh lebih sedikit daripada minimal peserta — kalau begitu tripnya tidak akan pernah bisa berangkat.',

            'catatanPromo.max' => 'Catatan promonya kepanjangan — paling banyak 191 huruf.',

            'harga.required' => 'Harga jualnya belum diisi.',
            'harga.numeric' => 'Harga jualnya harus berupa angka.',
            'harga.min' => 'Harga jual tidak boleh minus.',

            'hargaAsli.numeric' => 'Harga sebelum diskon harus berupa angka.',
            'hargaAsli.min' => 'Harga sebelum diskon tidak boleh minus.',

            'hargaModal.numeric' => 'Modal per orang harus berupa angka.',
            'hargaModal.min' => 'Modal per orang tidak boleh minus.',

            'diskonPersen.numeric' => 'Diskonnya harus berupa angka.',
            'diskonPersen.min' => 'Diskon paling kecil 0 persen.',
            'diskonPersen.max' => 'Diskon paling besar 100 persen.',

            'gambar.image' => 'Berkas yang dipilih bukan gambar. Pilih foto JPG, PNG, atau WebP.',
            'gambar.max' => 'Fotonya kebesaran — paling besar 4 MB. Perkecil dulu, lalu unggah lagi.',

            'galeriBaru.max' => 'Paling banyak 12 foto sekali unggah.',
            'galeriBaru.*.image' => 'Salah satu berkas yang dipilih bukan gambar. Pilih foto JPG, PNG, atau WebP.',
            'galeriBaru.*.max' => 'Ada foto yang kebesaran — paling besar 8 MB per foto.',
        ];
    }

    public function mount(?int $paket = null): void
    {
        if (! $paket) {
            $this->hari = [$this->hariKosong(1)];

            return;
        }

        $this->paketId = $paket;
        $this->ubah = true;

        $isi = $this->muat("/paket-wisata/{$paket}")['data'] ?? [];

        if ($isi === []) {
            $this->hari = [$this->hariKosong(1)];

            return;
        }

        $this->keberangkatan = $isi['keberangkatan'] ?? [];
        $this->nama = $isi['nama'] ?? '';
        $this->kategori = $isi['kategori'] ?? 'open_trip';
        $this->status = $isi['status'] ?? 'terbit';
        $this->tayangMulai = (string) ($isi['tayang_mulai'] ?? '');
        $this->tayangSampai = (string) ($isi['tayang_sampai'] ?? '');
        $this->berakhirOtomatis = (bool) ($isi['berakhir_otomatis'] ?? true);
        $this->promoRombongan = (bool) ($isi['promo_rombongan'] ?? false);
        $this->durasi = (string) ($isi['durasi'] ?? '');
        $this->tanggalBerangkat = (string) ($isi['tanggal_berangkat'] ?? '');
        $this->tanggalPulang = (string) ($isi['tanggal_pulang'] ?? '');
        $this->titikJemput = (string) ($isi['titik_jemput'] ?? '');
        $this->minimalPeserta = (int) ($isi['minimal_peserta'] ?? 6);
        $this->kuota = $isi['kuota'] ?? null;
        $this->catatanPromo = (string) ($isi['catatan_promo'] ?? '');
        $this->harga = $isi['harga'] ?? 0;
        $this->hargaAsli = $isi['harga_asli'] ?? 0;
        $this->hargaModal = $isi['harga_modal'] ?? null;
        $this->hargaTeks = $this->keRupiah($this->harga);
        $this->hargaAsliTeks = $this->keRupiah($this->hargaAsli);
        $this->hargaModalTeks = $this->hargaModal === null ? '' : $this->keRupiah($this->hargaModal);
        $this->diskonPersen = $isi['diskon_persen'] ?? 0;
        $this->diskonOtomatis = (int) $this->diskonPersen === $this->diskonTerhitung();
        $this->pilihanTerbaik = (bool) ($isi['pilihan_terbaik'] ?? false);
        $this->destinasi = array_values($isi['destinasi'] ?? []);
        $this->fasilitas = array_values($isi['fasilitas'] ?? []);
        $this->tidakTermasuk = array_values($isi['tidak_termasuk'] ?? []);
        $this->titikJemputDaftar = $this->pecahTitikJemput($this->titikJemput);
        $this->galeriLama = array_values($isi['foto_galeri'] ?? []);
        $this->gambarLama = $isi['sampul'] ?? null;
        $this->tautanPublik = $isi['tautan_publik'] ?? null;
        $this->tautanPratinjau = $isi['tautan_pratinjau'] ?? null;

        $this->hari = $this->dariItinerary($isi['itinerary'] ?? []);
        $this->durasiOtomatis = $this->durasi === '' || $this->durasi === $this->durasiTerhitung();
    }

    /* -------------------------------- DURASI -------------------------------- */

    /* ===================== TANGGAL KEBERANGKATAN ===================== */

    /**
     * Membuka baris isian untuk menyalin satu tanggal.
     *
     * Yang diminta HANYA tanggal barunya. Kuota, harga, dan catatannya ikut
     * tersalin, dan tanggal pulangnya bergeser sejauh yang sama di sisi
     * Orcha — menyalin 27–29 November ke 4 Desember hampir selalu berarti
     * 4–6 Desember.
     */
    public function salinKeberangkatan(int $id): void
    {
        $this->kbSalinDari = $id;
        $this->kbTanggal = '';
        $this->kbTanggalPulang = '';
        $this->resetValidation('kbTanggal');
    }

    public function batalKeberangkatan(): void
    {
        $this->kbSalinDari = null;
        $this->kbUbahId = null;
        $this->kbTanggal = '';
        $this->kbTanggalPulang = '';
        $this->kbHargaTeks = '';
        $this->kbKuota = '';
        $this->resetValidation('kbTanggal');
    }

    /**
     * Membuka baris isian untuk MENYUNTING satu tanggal yang sudah ada.
     *
     * Sebelum ini, tanggal yang salah ketik hanya bisa dihapus lalu dibuat
     * ulang — dan tanggal yang sudah punya pendaftar tidak bisa dihapus sama
     * sekali, sehingga salah ketik di sana permanen.
     */
    public function ubahKeberangkatan(int $id): void
    {
        $kb = collect($this->keberangkatan)->firstWhere('id', $id);

        if (! $kb) {
            return;
        }

        $this->kbUbahId = $id;
        $this->kbSalinDari = null;
        $this->kbTanggal = (string) ($kb['tanggal_berangkat'] ?? '');
        $this->kbTanggalPulang = (string) ($kb['tanggal_pulang'] ?? '');

        // Angka MENTAHNYA, bukan yang terpakai: yang mentah membedakan
        // "memang diisi segini" dari "ikut paket", dan yang terpakai tidak.
        $this->kbHargaTeks = ($kb['price'] ?? null) === null ? '' : $this->keRupiah($kb['price']);
        $this->kbKuota = ($kb['kuota'] ?? null) === null ? '' : (string) $kb['kuota'];

        $this->resetValidation('kbTanggal');
    }

    /** Menambah tanggal baru, atau menyalin yang sedang dipilih. */
    public function simpanKeberangkatan(): void
    {
        $this->resetValidation('kbTanggal');

        if (trim($this->kbTanggal) === '') {
            $this->addError('kbTanggal', 'Tanggal berangkatnya belum diisi.');

            return;
        }

        if (! $this->paketId) {
            $this->dispatch('toast-error', message: 'Simpan paketnya dulu, baru tanggalnya bisa ditambahkan.');

            return;
        }

        /*
         | Harga & kuota dikirim sebagai TEKS KOSONG saat tidak diisi, bukan
         | dihilangkan dari muatan.
         |
         | Kosong di sini berarti "ikut paket", dan itu keputusan yang harus
         | sampai ke Orcha — kunci yang tidak terkirim membuatnya mengira
         | angkanya memang tidak disentuh, sehingga harga sendiri yang baru
         | dihapus admin tetap menempel di tanggal itu.
         */
        $isi = [
            'tanggal_berangkat' => $this->kbTanggal,
            'tanggal_pulang' => trim($this->kbTanggalPulang) ?: '',
            'price' => $this->kbHargaTeks === '' ? '' : $this->angkaDari($this->kbHargaTeks),
            'kuota' => trim($this->kbKuota) === '' ? '' : (int) $this->kbKuota,
        ];

        $jalur = match (true) {
            $this->kbUbahId !== null => "/paket-wisata/{$this->paketId}/keberangkatan/{$this->kbUbahId}",
            $this->kbSalinDari !== null => "/paket-wisata/{$this->paketId}/keberangkatan/{$this->kbSalinDari}/salin",
            default => "/paket-wisata/{$this->paketId}/keberangkatan",
        };

        if (! $this->kirimData($jalur, $isi, 'Tanggal keberangkatan disimpan.')) {
            return;
        }

        $this->batalKeberangkatan();
        $this->muatUlangKeberangkatan();
    }

    /** Menutup / membuka lagi satu tanggal tanpa menghapusnya. */
    public function alihkanKeberangkatan(int $id, bool $aktif): void
    {
        if (! $this->paketId) {
            return;
        }

        $this->kirimData(
            "/paket-wisata/{$this->paketId}/keberangkatan/{$id}",
            ['aktif' => ! $aktif],
            $aktif ? 'Tanggal ditutup dari pendaftaran.' : 'Tanggal dibuka lagi.',
        );

        $this->muatUlangKeberangkatan();
    }

    public function hapusKeberangkatan(int $id): void
    {
        if (! $this->paketId) {
            return;
        }

        // Penolakan "sudah punya pendaftar" datang dari Orcha berikut
        // kalimatnya; diteruskan apa adanya.
        $this->hapusData("/paket-wisata/{$this->paketId}/keberangkatan/{$id}", 'Tanggal keberangkatan dihapus.');

        $this->muatUlangKeberangkatan();
    }

    /**
     * Membaca ulang daftarnya dari Orcha.
     *
     * Bukan menyunting senarai di layar: kuota terpakai & sisa kursinya
     * dihitung di sana, dan menebaknya di sini berarti dua angka untuk satu
     * hal yang akan berselisih begitu ada yang mendaftar.
     */
    private function muatUlangKeberangkatan(): void
    {
        $this->keberangkatan = $this->muat("/paket-wisata/{$this->paketId}")['data']['keberangkatan'] ?? [];
    }

    public function updatedTanggalBerangkat(): void
    {
        $this->hitungDurasiBila();
    }

    public function updatedTanggalPulang(): void
    {
        $this->hitungDurasiBila();
    }

    /** Admin mengetik sendiri: hitungan berhenti menimpa. */
    public function updatedDurasi(): void
    {
        $this->durasiOtomatis = $this->durasi === $this->durasiTerhitung();
    }

    /** Tombol "hitung ulang" — mengembalikan durasi ke hasil hitungan. */
    public function hitungDurasi(): void
    {
        $this->durasiOtomatis = true;
        $this->durasi = $this->durasiTerhitung();
    }

    private function hitungDurasiBila(): void
    {
        if ($this->durasiOtomatis) {
            $this->durasi = $this->durasiTerhitung();
        }
    }

    /**
     * "3 Hari 2 Malam" dari selisih tanggal. Bila tanggalnya belum diisi,
     * jumlah hari di itinerary dipakai sebagai perkiraan.
     */
    public function durasiTerhitung(): string
    {
        $hari = $this->jumlahHari();

        if ($hari < 1) {
            return '';
        }

        $malam = $hari - 1;

        return $malam > 0 ? "{$hari} Hari {$malam} Malam" : '1 Hari';
    }

    private function jumlahHari(): int
    {
        if ($this->tanggalBerangkat !== '' && $this->tanggalPulang !== '') {
            try {
                $berangkat = \Carbon\Carbon::parse($this->tanggalBerangkat)->startOfDay();
                $pulang = \Carbon\Carbon::parse($this->tanggalPulang)->startOfDay();
            } catch (\Throwable) {
                return 0;
            }

            // Tanggal pulang sebelum berangkat berarti isiannya belum benar;
            // validasi yang menegur, bukan hitungan ini.
            return $pulang->lt($berangkat) ? 0 : $berangkat->diffInDays($pulang) + 1;
        }

        // Belum ada tanggal: pakai hari yang sudah ada isinya di itinerary.
        return collect($this->hari)
            ->filter(fn ($satuHari) => collect($satuHari['agenda'] ?? [])
                ->contains(fn ($item) => trim($item['kegiatan'] ?? '') !== ''))
            ->count();
    }

    /* -------------------------------- DISKON -------------------------------- */

    public function updatedHargaTeks(): void
    {
        $this->harga = $this->angkaDari($this->hargaTeks);
        $this->hargaTeks = $this->keRupiah($this->harga);
        $this->hitungDiskonBila();
    }

    public function updatedHargaAsliTeks(): void
    {
        $this->hargaAsli = $this->angkaDari($this->hargaAsliTeks);
        $this->hargaAsliTeks = $this->keRupiah($this->hargaAsli);
        $this->hitungDiskonBila();
    }

    /**
     * Modal dikosongkan tetap null, bukan nol.
     *
     * angkaDari() mengembalikan 0 untuk isian kosong, dan kalau nilai itu
     * dikirim apa adanya, paket yang modalnya sengaja dibiarkan kosong
     * berubah jadi "modal Rp 0" — laporan lalu mengaku untung sebesar seluruh
     * harga jual, yang justru angka paling menyesatkan di halaman ini.
     */
    public function updatedHargaModalTeks(): void
    {
        if (trim($this->hargaModalTeks) === '') {
            $this->hargaModal = null;
            $this->hargaModalTeks = '';

            return;
        }

        $this->hargaModal = $this->angkaDari($this->hargaModalTeks);
        $this->hargaModalTeks = $this->keRupiah($this->hargaModal);
    }

    /**
     * Untung per peserta yang langsung terlihat saat kedua angka diisi.
     *
     * null berarti modalnya belum diisi — bukan nol. Boleh negatif: paket yang
     * dijual di bawah modal memang rugi, dan admin perlu melihatnya sebelum
     * menyimpan, bukan saat menutup buku sebulan kemudian.
     */
    public function marginPerOrang(): ?int
    {
        return $this->hargaModal === null ? null : (int) $this->harga - (int) $this->hargaModal;
    }

    public function marginPersen(): ?float
    {
        $margin = $this->marginPerOrang();

        return $margin === null || (int) $this->harga <= 0
            ? null
            : round($margin / (int) $this->harga * 100, 1);
    }

    public function updatedDiskonPersen(): void
    {
        $this->diskonOtomatis = (int) $this->diskonPersen === $this->diskonTerhitung();
    }

    public function hitungDiskon(): void
    {
        $this->diskonOtomatis = true;
        $this->diskonPersen = $this->diskonTerhitung();
    }

    private function hitungDiskonBila(): void
    {
        if ($this->diskonOtomatis) {
            $this->diskonPersen = $this->diskonTerhitung();
        }
    }

    /**
     * Berapa persen harga turun dari harga sebelum diskon. Dibulatkan ke
     * bawah supaya angka yang dipajang tidak pernah melebih-lebihkan potongan.
     */
    public function diskonTerhitung(): int
    {
        $asli = (float) $this->hargaAsli;
        $jual = (float) $this->harga;

        if ($asli <= 0 || $jual <= 0 || $jual >= $asli) {
            return 0;
        }

        return (int) floor((($asli - $jual) / $asli) * 100);
    }

    /* ------------------------- DESTINASI & FASILITAS ------------------------- */

    /** Klik pada saran: masuk kalau belum ada, keluar kalau sudah. */
    /**
     * Jenis isian berdaftar-pilihan, beserta properti yang menampungnya.
     *
     * Dulu jungkit/tambah/buang memakai tiga rangkaian ternary "destinasi
     * atau fasilitas". Begitu jenis ketiga dan keempat masuk, bentuk itu
     * berubah dari ringkas jadi jebakan: satu ternary yang terlewat membuat
     * isian jatuh ke daftar yang salah TANPA galat — "Tiket masuk" masuk ke
     * Fasilitas, dan paketnya menjanjikan apa yang justru tidak termasuk.
     *
     * @var array<string, array{0: string, 1: string}>  jenis => [properti, properti isian baru]
     */
    private const MEDAN_DAFTAR = [
        'destinasi' => ['destinasi', 'destinasiBaru'],
        'fasilitas' => ['fasilitas', 'fasilitasBaru'],
        'tidak_termasuk' => ['tidakTermasuk', 'tidakTermasukBaru'],
        'titik_jemput' => ['titikJemputDaftar', 'titikJemputBaru'],
    ];

    public function jungkit(string $jenis, string $nama): void
    {
        if (! isset(self::MEDAN_DAFTAR[$jenis])) {
            return;
        }

        [$medan] = self::MEDAN_DAFTAR[$jenis];
        $terpilih = $this->{$medan};

        $this->{$medan} = in_array($nama, $terpilih, true)
            ? array_values(array_diff($terpilih, [$nama]))
            : [...$terpilih, $nama];
    }

    /**
     * Isian baru langsung ikut masuk daftar pilihan di Orcha, jadi paket
     * berikutnya dengan isi yang sama tinggal diklik.
     */
    public function tambah(string $jenis): void
    {
        if (! isset(self::MEDAN_DAFTAR[$jenis])) {
            return;
        }

        [$medan, $medanBaru] = self::MEDAN_DAFTAR[$jenis];
        $nama = trim((string) $this->{$medanBaru});

        if ($nama === '') {
            return;
        }

        if (! in_array($nama, $this->{$medan}, true)) {
            $this->{$medan} = [...$this->{$medan}, $nama];
        }

        $this->kirimData('/saran', ['jenis' => $jenis, 'nama' => $nama], 'Masuk daftar pilihan.');

        $this->{$medanBaru} = '';
    }

    /** Keluarkan dari paket ini — daftar pilihannya tidak tersentuh. */
    public function buang(string $jenis, int $urutan): void
    {
        if (! isset(self::MEDAN_DAFTAR[$jenis])) {
            return;
        }

        [$medan] = self::MEDAN_DAFTAR[$jenis];
        $isi = $this->{$medan};
        unset($isi[$urutan]);

        $this->{$medan} = array_values($isi);
    }

    /**
     * "Jogja, Klaten, Surakarta" jadi tiga potong.
     *
     * Potongan kosong dibuang: koma menggantung di ujung kalimat adalah hal
     * paling lumrah yang diketik orang, dan tanpa penyaringan ini ia jadi
     * satu titik jemput bernama kosong di daftar peserta.
     *
     * @return array<int, string>
     */
    private function pecahTitikJemput(string $kalimat): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $kalimat))));
    }

    /**
     * Hapus dari daftar pilihan di Orcha.
     *
     * Paket yang sudah tersimpan tidak ikut berubah — yang hilang cuma
     * pilihan cepatnya.
     */
    public function hapusSaran(int $id): void
    {
        $this->hapusData("/saran/{$id}", 'Dihapus dari daftar pilihan.');
    }

    /** Harga per tanggal ikut dirapikan jadi bertitik, sama seperti harga paket. */
    public function updatedKbHargaTeks(): void
    {
        $this->kbHargaTeks = $this->kbHargaTeks === '' ? '' : $this->keRupiah($this->angkaDari($this->kbHargaTeks));
    }

    /* -------------------------------- GALERI -------------------------------- */

    /**
     * Membuang satu foto galeri yang SUDAH tersimpan.
     *
     * Hanya dicoret dari daftar di layar; penghapusan berkasnya terjadi di
     * Orcha saat paketnya disimpan, dan hanya untuk yang memang tidak lagi
     * disebut. Menghapusnya di sini seketika berarti membatalkan borang
     * meninggalkan foto yang sudah hilang.
     */
    public function buangGaleriLama(int $urutan): void
    {
        unset($this->galeriLama[$urutan]);
        $this->galeriLama = array_values($this->galeriLama);
    }

    /** Membatalkan satu foto yang baru dipilih, sebelum terkirim. */
    public function buangGaleriBaru(int $urutan): void
    {
        unset($this->galeriBaru[$urutan]);
        $this->galeriBaru = array_values($this->galeriBaru);
    }

    /* ------------------------------ ITINERARY ------------------------------ */

    public function tambahHari(): void
    {
        $this->hari[] = $this->hariKosong(count($this->hari) + 1);
    }

    public function buangHari(int $urutan): void
    {
        unset($this->hari[$urutan]);
        $this->hari = array_values($this->hari);

        if ($this->hari === []) {
            $this->hari = [$this->hariKosong(1)];
        }
    }

    public function tambahAgenda(int $urutanHari): void
    {
        $this->hari[$urutanHari]['agenda'][] = ['jam' => '', 'kegiatan' => ''];
    }

    public function buangAgenda(int $urutanHari, int $urutanAgenda): void
    {
        unset($this->hari[$urutanHari]['agenda'][$urutanAgenda]);
        $this->hari[$urutanHari]['agenda'] = array_values($this->hari[$urutanHari]['agenda']);

        if ($this->hari[$urutanHari]['agenda'] === []) {
            $this->hari[$urutanHari]['agenda'] = [['jam' => '', 'kegiatan' => '']];
        }
    }

    /**
     * Menukar satu hari dengan tetangganya.
     *
     * Sebelum ini jadwal hanya bisa ditambah dan dihapus, jadi menyisipkan
     * satu hari di tengah berarti mengetik ulang seluruh hari sesudahnya.
     *
     * Nomor hari di layar dihitung dari urutannya, jadi menukar isinya sudah
     * cukup — tidak ada nomor tersimpan yang perlu ikut diperbaiki.
     */
    public function pindahHari(int $urutan, int $arah): void
    {
        $tujuan = $urutan + $arah;

        if (! isset($this->hari[$urutan], $this->hari[$tujuan])) {
            return;
        }

        [$this->hari[$urutan], $this->hari[$tujuan]] = [$this->hari[$tujuan], $this->hari[$urutan]];
    }

    /**
     * Menggandakan satu hari beserta seluruh kegiatannya.
     *
     * Hari ke-2 yang isinya mirip hari ke-1 — jam yang sama, kegiatan yang
     * bergeser sedikit — adalah bentuk paling lumrah sebuah trip, dan selama
     * ini satu-satunya cara membuatnya adalah mengetik ulang sepuluh baris.
     *
     * Salinannya disisipkan TEPAT DI BAWAH asalnya, bukan di ujung daftar:
     * yang menyalin hari ke-2 bermaksud menaruhnya jadi hari ke-3.
     */
    public function salinHari(int $urutan): void
    {
        if (! isset($this->hari[$urutan])) {
            return;
        }

        $salinan = $this->hari[$urutan];
        $salinan['nama'] = 'Hari '.($urutan + 2);

        array_splice($this->hari, $urutan + 1, 0, [$salinan]);

        // Nama hari sesudahnya ikut bergeser — tetapi HANYA yang masih
        // bernama bawaan. Hari yang sudah diberi nama sendiri oleh admin
        // ("Puncak Ijen") tidak boleh ditimpa jadi "Hari 4".
        foreach ($this->hari as $i => $satu) {
            if (preg_match('/^(Hari|Day)\s*\d+$/i', trim((string) $satu['nama']))) {
                $this->hari[$i]['nama'] = 'Hari '.($i + 1);
            }
        }
    }

    /** Menukar satu kegiatan dengan tetangganya di hari yang sama. */
    public function pindahAgenda(int $urutanHari, int $urutanAgenda, int $arah): void
    {
        $tujuan = $urutanAgenda + $arah;

        if (! isset($this->hari[$urutanHari]['agenda'][$urutanAgenda], $this->hari[$urutanHari]['agenda'][$tujuan])) {
            return;
        }

        [$this->hari[$urutanHari]['agenda'][$urutanAgenda], $this->hari[$urutanHari]['agenda'][$tujuan]]
            = [$this->hari[$urutanHari]['agenda'][$tujuan], $this->hari[$urutanHari]['agenda'][$urutanAgenda]];
    }

    /**
     * Merapikan jam yang diketik bebas jadi satu bentuk: "07.00".
     *
     * Satu paket bisa memuat "7:00", "07:00", dan "07.00" sekaligus tanpa
     * ada yang menegur, dan ketiganya tampil apa adanya di halaman publik —
     * terbaca sebagai jadwal yang disusun asal-asalan. Yang tidak terbaca
     * sebagai jam dibiarkan apa adanya, bukan dibuang: ia mungkin memang
     * bukan jam ("Menyesuaikan").
     */
    public function updatedHari($nilai, ?string $kunci = null): void
    {
        /*
         | $kunci BOLEH null, dan itu bukan kasus pinggiran.
         |
         | Livewire memanggil kait ini tanpa kunci ketika SELURUH senarai
         | diganti sekaligus — yang persis terjadi saat borangnya diisi dari
         | Orcha, dan saat uji memakai ->set('hari', [...]). Dengan tanda
         | tangan yang menuntut string, panggilan itu melempar TypeError dan
         | menjatuhkan seluruh halaman: borang paket tidak bisa dibuka sama
         | sekali.
         */
        if ($kunci === null || ! str_ends_with($kunci, '.jam')) {
            return;
        }

        /*
         | Kuncinya RELATIF terhadap propertinya: "0.agenda.0.jam", bukan
         | "hari.0.agenda.0.jam". Memakainya apa adanya pada $this akan
         | menulis ke properti bernama "0" — tidak ada galat, dan jamnya
         | tidak pernah berubah.
         */
        $kunci = preg_replace('/^hari\./', '', $kunci);

        $teks = trim((string) $nilai);

        if ($teks === '' || ! preg_match('/^(\d{1,2})[.:,]?(\d{2})$/', $teks, $cocok)) {
            return;
        }

        $jam = (int) $cocok[1];
        $menit = (int) $cocok[2];

        if (23 < $jam || 59 < $menit) {
            return;
        }

        $rapi = $this->hari;
        data_set($rapi, $kunci, sprintf('%02d.%02d', $jam, $menit));
        $this->hari = $rapi;
    }

    private function hariKosong(int $nomor): array
    {
        // "Hari 1", bukan "Day 1": nama ini bukan cuma label di layar admin —
        // ia ikut tersimpan dan TERBACA PENGUNJUNG di halaman paket. Paket yang
        // sudah terlanjur bernama "Day 1" tidak ikut berubah; ganti sendiri di
        // borangnya bila mau.
        return ['nama' => "Hari {$nomor}", 'agenda' => [['jam' => '', 'kegiatan' => '']]];
    }

    /** Bentuk dari API ('hari' + 'agenda') jadi bentuk isian di layar. */
    private function dariItinerary(array $itinerary): array
    {
        $hasil = [];

        foreach ($itinerary as $nomor => $satuHari) {
            $agenda = [];

            foreach ($satuHari['agenda'] ?? [] as $baris) {
                $agenda[] = ['jam' => $baris['jam'] ?? '', 'kegiatan' => $baris['kegiatan'] ?? ''];
            }

            $hasil[] = [
                'nama' => $satuHari['hari'] ?? ('Hari '.($nomor + 1)),
                'agenda' => $agenda ?: [['jam' => '', 'kegiatan' => '']],
            ];
        }

        return $hasil ?: [$this->hariKosong(1)];
    }

    /**
     * Kembali ke format teks yang dipahami Orcha. Hari tanpa agenda terisi
     * dibuang supaya baris kosong tidak ikut tersimpan.
     */
    private function keTeksItinerary(): string
    {
        $baris = [];

        foreach ($this->hari as $satuHari) {
            $agenda = collect($satuHari['agenda'] ?? [])
                ->filter(fn ($item) => trim($item['kegiatan'] ?? '') !== '');

            if ($agenda->isEmpty()) {
                continue;
            }

            $baris[] = trim($satuHari['nama'] ?? '') ?: 'Hari '.(count($baris) + 1);

            foreach ($agenda as $item) {
                $baris[] = trim($item['jam'] ?? '').' | '.trim($item['kegiatan']);
            }

            $baris[] = '';
        }

        return trim(implode("\n", $baris));
    }

    /* -------------------------------- SIMPAN -------------------------------- */

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'status' => $this->status,
            'tayang_mulai' => $this->tayangMulai,
            'tayang_sampai' => $this->tayangSampai,
            'berakhir_otomatis' => $this->berakhirOtomatis,
            'promo_rombongan' => $this->promoRombongan,
            'durasi' => $this->durasi,
            'tanggal_berangkat' => $this->tanggalBerangkat,
            'tanggal_pulang' => $this->tanggalPulang,
            // Disatukan lagi jadi satu kalimat: bentuk simpanannya tidak
            // berubah, cuma cara mengisinya.
            'titik_jemput' => implode(', ', array_values($this->titikJemputDaftar)),
            'minimal_peserta' => $this->minimalPeserta,
            'kuota' => $this->kuota === '' ? null : $this->kuota,
            'catatan_promo' => $this->catatanPromo,
            'harga' => $this->harga,
            'harga_asli' => $this->hargaAsli ?: $this->harga,
            // Dikirim sebagai teks kosong, bukan dihilangkan: perataan
            // multipart membuang nilai null, dan kunci yang tidak terkirim
            // membuat Orcha mengira modalnya memang tidak disentuh.
            'harga_modal' => $this->hargaModal === null ? '' : $this->hargaModal,
            'diskon_persen' => $this->diskonPersen ?: 0,
            'pilihan_terbaik' => $this->pilihanTerbaik,
            'destinasi' => array_values($this->destinasi),
            'fasilitas' => array_values($this->fasilitas),
            'tidak_termasuk' => array_values($this->tidakTermasuk),
            'itinerary_teks' => $this->keTeksItinerary(),

            /*
             | Galeri dikirim sebagai daftar yang DIPERTAHANKAN, bukan daftar
             | yang dihapus.
             |
             | Orcha membuang berkas yang tidak lagi disebut. Mengirim "yang
             | dihapus" berarti dua tempat harus sepakat soal isi galerinya,
             | dan begitu keduanya berselisih — satu tab lama, satu simpanan
             | yang terlambat — yang hilang adalah foto yang tidak pernah
             | diminta hilang.
             */
            'galeri_tetap' => array_values($this->galeriLama),

            // Penanda eksplisit: perataan multipart membuang senarai kosong,
            // jadi borang yang menghapus SEMUA foto akan terbaca di Orcha
            // seperti borang yang tidak menyinggung galeri sama sekali.
            'galeri_disentuh' => true,
        ];

        // Tujuan diteruskan supaya pemberitahuan sukses tampil utuh dulu di
        // halaman ini, baru berpindah ke daftar setelah popupnya menutup.
        // Foto galeri yang baru ikut sebagai berkas tambahan, terpisah dari
        // sampulnya — keduanya tidak boleh saling menimpa.
        $berkasLain = $this->galeriBaru === []
            ? []
            : ['galeri_baru' => array_values($this->galeriBaru)];

        $this->ubah
            ? $this->kirimData("/paket-wisata/{$this->paketId}", $data, 'Paket wisata diperbarui.', $this->gambar, route('admin.orcha.paket'), $berkasLain)
            : $this->kirimData('/paket-wisata', $data, 'Paket wisata ditambahkan.', $this->gambar, route('admin.orcha.paket'), $berkasLain);
    }

    /** Cerminan aturan tayang di Orcha, supaya akibat pilihan langsung terlihat. */
    public function statusTayang(): string
    {
        if (in_array($this->status, ['draf', 'arsip'], true)) {
            return $this->status;
        }

        $waktu = fn (string $nilai) => $nilai !== '' ? \Carbon\Carbon::parse($nilai) : null;

        if (($mulai = $waktu($this->tayangMulai)) && $mulai->isFuture()) {
            return 'terjadwal';
        }

        if (($sampai = $waktu($this->tayangSampai)) && $sampai->isPast()) {
            return 'berakhir';
        }

        $akhirTrip = $waktu($this->tanggalPulang) ?? $waktu($this->tanggalBerangkat);

        if ($this->berakhirOtomatis && $akhirTrip && $akhirTrip->endOfDay()->isPast()) {
            return 'berakhir';
        }

        return 'tayang';
    }

    public function render()
    {
        // Daftar pilihan hidup di Orcha dan tumbuh sendiri setiap ada isian
        // baru, jadi diambil segar tiap render — bukan dari cache rujukan.
        $saran = $this->muat('/saran')['data'] ?? [];

        return view('livewire.pages.admin.orcha.paket-wisata.form', [
            'pilihanKategori' => $this->rujukan('kategori_paket'),
            'pilihanStatusPaket' => $this->rujukan('status_paket'),
            'statusTayang' => $statusTayang = $this->statusTayang(),
            'statusTayangLabel' => $this->rujukan('status_tayang')[$statusTayang] ?? 'Tayang',
            'saranDestinasi' => $saran['destinasi'] ?? [],
            'saranFasilitas' => $saran['fasilitas'] ?? [],
            'saranTidakTermasuk' => $saran['tidak_termasuk'] ?? [],
            'saranTitikJemput' => $saran['titik_jemput'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
