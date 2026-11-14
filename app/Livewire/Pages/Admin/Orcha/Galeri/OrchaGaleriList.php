<?php

namespace App\Livewire\Pages\Admin\Orcha\Galeri;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Galeri momen perjalanan yang tampil di beranda Orcha.
 *
 * Layarnya sendiri, bukan menumpang halaman etalase bersama destinasi dan
 * partner. Dua alasan: isinya foto, jadi yang pantas ditampilkan adalah
 * fotonya sendiri dalam petak besar — bukan baris tabel bernama; dan cara
 * kerjanya berbeda, karena admin datang membawa dua puluh foto sepulang trip,
 * bukan satu data yang diketik.
 *
 * Sebelum ini bagian Galeri di beranda meminjam foto destinasi, lalu diam-diam
 * jatuh ke foto bawaan repo bila jumlahnya kurang dari enam. Admin tidak punya
 * cara memajang foto rombongan tanpa mengarang destinasi baru.
 */
class OrchaGaleriList extends Component
{
    use MemanggilOrcha, WithFileUploads;

    /**
     * Foto yang sedang diunggah, bisa banyak sekaligus.
     *
     * Inilah bedanya dengan formulir etalase yang lain: sepulang trip admin
     * memegang selusin foto, dan mengunggahnya satu per satu lewat jendela
     * berarti dua belas kali buka-isi-simpan. Yang paling sering terjadi bukan
     * admin mengeluh, melainkan admin berhenti di foto kelima.
     *
     * @var array<int, mixed>
     */
    public $fotoBaru = [];

    /**
     * Keterangan TIAP foto yang sedang diunggah, sejajar dengan $fotoBaru.
     *
     * Satu keterangan untuk seluruh unggahan benar selama fotonya serombongan
     * dari acara yang sama — dan salah begitu admin memilih foto dari beberapa
     * trip sekaligus. Yang terjadi kalau dipaksa satu bukan admin mengeluh,
     * melainkan keterangannya diisi asal-asalan lalu tidak pernah dibetulkan.
     *
     * @var array<int, string>
     */
    public array $keteranganPer = [];

    /** Isian untuk menyamakan keterangan seluruh foto sekali tekan. */
    public string $keteranganBaru = '';

    /** Foto baru langsung tampil di beranda, kecuali admin memang menahannya. */
    public bool $tampilBaru = true;

    /** Baris yang keterangannya sedang disunting; null berarti tidak ada. */
    public ?int $sedangDiubah = null;

    public string $keterangan = '';

    public int $urutan = 0;

    public bool $tampil = true;

    /**
     * Titik fokus foto yang sedang disunting, dalam persen.
     *
     * Beranda memajang tiap foto dalam kotak 4:3, jadi foto tegak kehilangan
     * atas dan bawahnya. Selama titiknya selalu di tengah, rombongan yang
     * berdiri di bagian bawah terpotong kepalanya — dan satu-satunya jalan
     * keluar bagi admin adalah memotong fotonya di luar aplikasi lalu
     * mengunggah ulang.
     *
     * Yang disimpan POSISINYA, bukan hasil potongannya: berkasnya tidak
     * pernah diubah, jadi titiknya boleh digeser berkali-kali tanpa mutunya
     * menurun.
     */
    public int $fokusX = 50;

    public int $fokusY = 50;

    /**
     * Aturan berkas foto — SENGAJA tanpa batas ukuran.
     *
     * Yang disimpan Orcha bukan berkas yang dikirim admin: di sana ia diubah
     * jadi WebP dan sisi terpanjangnya dipangkas ke 1920px, sehingga foto
     * kamera ponsel 12 MB mendarat sebagai beberapa ratus KB. Menolaknya di
     * depan pintu dengan alasan "terlalu besar" berarti menyuruh admin
     * mengecilkan sendiri foto yang sebentar lagi dikecilkan mesin — dan yang
     * benar-benar terjadi bukan admin mengecilkan foto, melainkan foto itu
     * tidak jadi diunggah.
     *
     * Batas nyatanya tetap ada dan datang dari tempat yang memang berwenang:
     * gerbang unggah sementara Livewire (100MB di config/livewire.php) serta
     * upload_max_filesize di server.
     *
     * @var string
     */
    private const ATURAN_FOTO = 'image';

    /** @var array<string, string> */
    private const PESAN_FOTO = [
        'fotoBaru.required' => 'Pilih dulu foto yang mau diunggah.',
        'fotoBaru.*.image' => 'Berkas ke-:position bukan gambar. Format yang bisa dibaca: JPG, PNG, WebP, atau GIF.',
    ];

    public function updatedFotoBaru(): void
    {
        $this->validate([
            'fotoBaru.*' => self::ATURAN_FOTO,
        ], self::PESAN_FOTO, ['fotoBaru.*' => 'foto']);

        // Disiapkan sepanjang jumlah fotonya, supaya isian tiap kartu punya
        // tempatnya sendiri sejak awal — larik yang tumbuh sambil diketik
        // membuat Livewire kehilangan jejak baris mana milik foto mana.
        $this->keteranganPer = array_pad([], count($this->fotoBaru ?? []), '');
    }

    /** Menyamakan keterangan seluruh foto — untuk unggahan serombongan. */
    public function samakanKeterangan(): void
    {
        $this->keteranganPer = array_fill(0, count($this->fotoBaru ?? []), trim($this->keteranganBaru));
    }

    /**
     * Menyimpan seluruh foto yang dipilih, satu permintaan per foto.
     *
     * Dikirim satu-satu, bukan sekaligus: jalur Orcha menerima satu gambar per
     * pemanggilan, dan yang gagal di tengah tidak menyeret yang sudah berhasil.
     * Jumlah yang benar-benar masuk disebut di pemberitahuannya, supaya admin
     * tahu persis berapa yang perlu diulang.
     */
    public function unggah(): void
    {
        $this->validate([
            'fotoBaru' => 'required|array|min:1',
            'fotoBaru.*' => self::ATURAN_FOTO,
            'keteranganPer.*' => 'nullable|string|max:191',
        ], self::PESAN_FOTO, ['fotoBaru' => 'foto', 'fotoBaru.*' => 'foto', 'keteranganPer.*' => 'keterangan']);

        $berhasil = 0;
        $gagal = 0;

        foreach ($this->fotoBaru as $urutan => $foto) {
            try {
                $this->orcha()->kirim('/galeri', [
                    'keterangan' => trim($this->keteranganPer[$urutan] ?? ''),
                    // Dikirim sebagai teks: multipart hanya mengenal pasangan
                    // nama-nilai berupa teks, dan boolean PHP jadi "1"/"" yang
                    // tidak lolos aturan boolean di sisi Orcha.
                    'tampil' => $this->tampilBaru ? '1' : '0',
                ], $foto);
                $berhasil++;
            } catch (OrchaTidakTerjangkau) {
                $gagal++;
            }
        }

        $this->reset(['fotoBaru', 'keteranganBaru', 'keteranganPer']);
        $this->tampilBaru = true;

        if ($berhasil === 0) {
            $this->dispatch('toast-error', message: 'Tidak ada foto yang berhasil diunggah. Coba lagi.');

            return;
        }

        $this->dispatch('order-updated', message: $gagal === 0
            ? $berhasil.' foto ditambahkan ke galeri.'
            : $berhasil.' foto masuk, '.$gagal.' gagal. Coba unggah ulang yang gagal.');
    }

    public function ubah(array $baris): void
    {
        $this->sedangDiubah = (int) $baris['id'];
        $this->keterangan = (string) ($baris['keterangan'] ?? '');
        $this->urutan = (int) ($baris['urutan'] ?? 0);
        $this->tampil = (bool) ($baris['tampil'] ?? true);
        $this->fokusX = (int) ($baris['fokus_x'] ?? 50);
        $this->fokusY = (int) ($baris['fokus_y'] ?? 50);
        $this->resetValidation();
    }

    public function simpan(): void
    {
        $this->validate([
            'keterangan' => 'nullable|string|max:191',
            'urutan' => 'integer|min:0|max:9999',
            'fokusX' => 'integer|min:0|max:100',
            'fokusY' => 'integer|min:0|max:100',
        ]);

        $tersimpan = $this->kirimData(
            '/galeri/'.$this->sedangDiubah,
            [
                'keterangan' => $this->keterangan,
                'urutan' => $this->urutan,
                'tampil' => $this->tampil,
                'fokus_x' => $this->fokusX,
                'fokus_y' => $this->fokusY,
            ],
            'Foto diperbarui.',
        );

        if ($tersimpan) {
            $this->batal();
        }
    }

    /**
     * Menyembunyikan atau menampilkan foto tanpa membuka formulirnya.
     *
     * Foto yang kurang pantas dipajang lebih sering perlu disembunyikan
     * cepat-cepat daripada disunting keterangannya — dan menghapusnya berarti
     * kehilangan berkasnya, padahal yang diminta cuma "jangan tampil dulu".
     */
    public function balikTampil(array $baris): void
    {
        $this->kirimData(
            '/galeri/'.$baris['id'],
            [
                'keterangan' => $baris['keterangan'] ?? '',
                'urutan' => $baris['urutan'] ?? 0,
                'tampil' => ! ($baris['tampil'] ?? true),
                // Ikut dikirim supaya menyembunyikan foto tidak pernah
                // tersangkut jadi "menggeser titik fokusnya ke tengah".
                'fokus_x' => $baris['fokus_x'] ?? 50,
                'fokus_y' => $baris['fokus_y'] ?? 50,
            ],
            ($baris['tampil'] ?? true) ? 'Foto disembunyikan dari beranda.' : 'Foto ditampilkan lagi di beranda.',
        );
    }

    public function hapus(int $id): void
    {
        $this->hapusData("/galeri/{$id}", 'Foto dihapus dari galeri.');
    }

    public function batal(): void
    {
        $this->reset(['sedangDiubah', 'keterangan', 'urutan', 'tampil', 'fokusX', 'fokusY']);
        $this->tampil = true;
        $this->resetValidation();
    }

    public function render()
    {
        $daftar = collect($this->muat('/galeri')['data'] ?? []);

        return view('livewire.pages.admin.orcha.galeri.index', [
            'daftar' => $daftar->all(),
            'jumlahTampil' => $daftar->where('tampil', true)->count(),
        ])->layout('livewire.layout.templateindex');
    }
}
