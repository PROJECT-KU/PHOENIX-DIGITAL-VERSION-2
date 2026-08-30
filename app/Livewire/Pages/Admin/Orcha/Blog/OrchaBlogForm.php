<?php

namespace App\Livewire\Pages\Admin\Orcha\Blog;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Support\RingkasanOtomatis;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Tulis dan sunting artikel blog Orcha dari lemon.
 *
 * Bentuk dan kebiasaannya dibuat sama dengan formulir blog Phoenix — pratinjau
 * alamat, ringkasan otomatis, pratinjau sampul, toolbar penyunting — supaya
 * admin yang berpindah antara dua blog tidak perlu belajar dua alat.
 *
 * Yang berbeda hanya tempat datanya berakhir: bukan basis data lemon, melainkan
 * API Orcha. Karena itu setiap kegagalan jaringan harus berakhir sebagai pesan
 * yang bisa dibaca, bukan layar galat — lihat MemanggilOrcha.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Artikel Blog Orcha')]
class OrchaBlogForm extends Component
{
    use MemanggilOrcha, WithFileUploads;

    /** Nomor artikel yang disunting; null berarti tulisan baru. */
    public ?int $artikelId = null;

    public string $judul = '';

    public string $slug = '';

    public string $kategori = '';

    public string $penulis = '';

    public string $ringkasan = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $isi = '';

    public string $status = 'draf';

    public string $terbitPada = '';

    /** Unggahan baru; null berarti sampul lama dipertahankan. */
    public $sampul = null;

    /** Jalur sampul yang sudah tersimpan di Orcha. */
    public ?string $sampulLama = null;

    /** Ditandai saat admin menekan "Hapus sampul". */
    public bool $hapusSampul = false;

    public bool $sedangMemuat = false;

    public function mount(?int $artikel = null): void
    {
        if ($artikel === null) {
            return;
        }

        $this->artikelId = $artikel;
        $this->sedangMemuat = true;

        try {
            $data = $this->orcha()->ambil("/artikel/$artikel")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
            $this->sedangMemuat = false;

            return;
        }

        if ($data === []) {
            abort(404);
        }

        $this->judul = $data['judul'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->kategori = $data['kategori'] ?? '';
        $this->penulis = $data['penulis'] ?? '';
        $this->ringkasan = $data['ringkasan'] ?? '';
        // Nilai MENTAH dari Orcha, bukan cadangannya — kotak meta harus tampak
        // kosong selama admin memang belum mengisinya.
        $this->metaTitle = $data['meta_title'] ?? '';
        $this->metaDescription = $data['meta_description'] ?? '';
        $this->isi = $data['isi'] ?? '';
        $this->status = $data['status'] ?? 'draf';
        $this->sampulLama = $data['sampul'] ?? null;

        // datetime-local hanya mengerti bentuk "Y-m-d\TH:i".
        $this->terbitPada = filled($data['terbit_pada'] ?? null)
            ? \Illuminate\Support\Carbon::parse($data['terbit_pada'])->format('Y-m-d\TH:i')
            : '';

        $this->sedangMemuat = false;
    }

    /**
     * Usulan slug diminta ke ORCHA, bukan dihitung di sini.
     *
     * Yang menentukan bentrok atau tidak adalah isi tabel artikel Orcha, dan
     * lemon tidak memilikinya. Menebak dari sini berarti admin baru tahu
     * slugnya berubah setelah menekan simpan — persis saat ia sudah tidak
     * memperhatikan lagi.
     *
     * Hanya berjalan saat MEMBUAT. Saat menyunting, slug lama dipertahankan
     * supaya alamat yang sudah beredar dan nilainya di mesin pencari tidak
     * hangus hanya karena judulnya dirapikan.
     */
    public function updatedJudul(): void
    {
        if ($this->artikelId !== null || trim($this->judul) === '') {
            return;
        }

        try {
            $this->slug = $this->orcha()->ambil('/artikel/slug', ['judul' => $this->judul])['slug'] ?? '';
        } catch (OrchaTidakTerjangkau) {
            // Pratinjau alamat bukan hal yang layak menghentikan pengetikan.
            $this->slug = \Illuminate\Support\Str::slug($this->judul);
        }
    }

    /** Ringkasan diisikan sekali begitu ada isi, kalau admin belum menulisnya. */
    public function updatedIsi(): void
    {
        if (trim($this->ringkasan) === '' && RingkasanOtomatis::teksPolos($this->isi) !== '') {
            $this->buatRingkasan();
        }
    }

    /**
     * Tombol "Acak lagi": susun ulang ringkasan DAN meta SEO sekaligus.
     *
     * Ketiganya dibuat bersamaan, persis seperti generateSeo() di blog Phoenix.
     * Meta description sengaja meminta kalimat yang BERBEDA dari ringkasan —
     * dua kotak berisi kalimat yang sama persis membuat salah satunya terbaca
     * sebagai kesalahan.
     */
    public function buatRingkasan(): void
    {
        $teks = RingkasanOtomatis::teksPolos($this->isi);

        /*
         | Kalimat yang SEDANG dipakai dikecualikan.
         |
         | Tanpa ini "Acak lagi" bisa mengembalikan kalimat yang sama persis —
         | dan dari kursi admin itu terbaca sebagai tombol rusak, bukan sebagai
         | kebetulan. Ia harus benar-benar berganti tiap ditekan.
         */
        $this->ringkasan = RingkasanOtomatis::kalimatMenarik($teks, 200, $this->ringkasan ?: null);
        $this->metaDescription = RingkasanOtomatis::kalimatMenarik($teks, 155, $this->ringkasan);

        /*
         | Meta title SENGAJA tidak diacak.
         |
         | Ia judul artikel yang dipotong 65 huruf — batas yang muat di hasil
         | pencarian Google tanpa dipenggal di tengah kata. Mengacaknya berarti
         | menampilkan kalimat acak sebagai judul di Google, dan itu bukan
         | judul artikelnya lagi. Perilakunya sama dengan blog Phoenix.
         */
        $this->metaTitle = \Illuminate\Support\Str::limit(trim($this->judul), 65, '');
    }

    public function batalkanSampul(): void
    {
        $this->sampul = null;
        $this->hapusSampul = true;
    }

    public function simpan(): void
    {
        $this->validate([
            'judul' => 'required|string|min:5|max:180',
            'slug' => 'nullable|string|max:200',
            'kategori' => 'nullable|string|max:80',
            'penulis' => 'nullable|string|max:100',
            'ringkasan' => 'nullable|string|max:500',
            'metaTitle' => 'nullable|string|max:255',
            'metaDescription' => 'nullable|string|max:300',
            'isi' => 'required|string|min:20',
            'status' => 'required|in:draf,tayang',
            'terbitPada' => 'nullable|date',
            'sampul' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ], [], [
            'judul' => 'judul artikel',
            'isi' => 'isi artikel',
            'terbitPada' => 'tanggal terbit',
            'metaTitle' => 'meta title',
            'metaDescription' => 'meta description',
        ]);

        /*
         | Jaring pengaman: isi yang masih kosong dilengkapi SAAT SIMPAN.
         |
         | updatedIsi() hanya menyala saat admin mengetik di penyunting. Admin
         | yang menempel seluruh artikel sekaligus, atau yang mengosongkan
         | ringkasan lalu menyimpan, akan lolos tanpa ringkasan maupun meta —
         | dan artikelnya muncul di hasil pencarian tanpa keterangan apa pun.
         | Pola yang sama dipakai save() di blog Phoenix.
         */
        $teks = RingkasanOtomatis::teksPolos($this->isi);

        if (trim($this->ringkasan) === '') {
            $this->ringkasan = RingkasanOtomatis::kalimatMenarik($teks, 200);
        }

        if (trim($this->metaTitle) === '') {
            $this->metaTitle = \Illuminate\Support\Str::limit(trim($this->judul), 65, '');
        }

        if (trim($this->metaDescription) === '') {
            $this->metaDescription = RingkasanOtomatis::kalimatMenarik($teks, 155, $this->ringkasan);
        }

        $data = [
            'judul' => $this->judul,
            'slug' => $this->slug ?: null,
            'kategori' => $this->kategori ?: null,
            'penulis' => $this->penulis ?: null,
            'ringkasan' => $this->ringkasan ?: null,
            'meta_title' => $this->metaTitle ?: null,
            'meta_description' => $this->metaDescription ?: null,
            'isi' => $this->isi,
            'status' => $this->status,
            'terbit_pada' => $this->terbitPada ?: null,
            'hapus_sampul' => $this->hapusSampul,
        ];

        /*
         | POST untuk keduanya, bukan PUT saat menyunting.
         |
         | PHP tidak menguraikan multipart pada permintaan PUT, sehingga sampul
         | yang diunggah tidak akan pernah sampai. Jalur /artikel/{id} di Orcha
         | memang menerima POST justru karena ini — pola yang sama sudah dipakai
         | paket wisata dan destinasi.
         */
        $jalur = $this->artikelId ? "/artikel/{$this->artikelId}" : '/artikel';

        $berhasil = $this->kirimData(
            $jalur,
            $data,
            $this->artikelId ? 'Artikel diperbarui.' : 'Artikel disimpan.',
            $this->sampul,
            route('admin.orcha.blog'),
        );

        if ($berhasil) {
            $this->hapusSampul = false;
        }
    }

    /* --------------------------------------------------------- Kategori */

    /*
     | Rubrik dipilih lewat POPUP, bukan dropdown — sama dengan pemilih kategori
     | di blog Phoenix.
     |
     | Ketiga metode di bawah ini sengaja berbentuk "panggil lalu kembalikan
     | daftar terbaru", bukan mengubah properti komponen. Sebabnya popupnya
     | digambar JavaScript di luar pohon Livewire: kalau daftarnya disimpan di
     | properti, tiap penambahan memicu render ulang yang MENUTUP popupnya —
     | dan admin yang ingin menambah dua rubrik harus membukanya lagi tiap kali.
     */

    /**
     * Daftar rubrik untuk isi popup.
     *
     * Diambil langsung dari jalurnya, bukan dari /rujukan yang disimpan sepuluh
     * menit: rubrik yang baru ditambah harus langsung terlihat. Simpanan itu
     * berguna untuk daftar yang jarang berubah, dan justru menyesatkan di layar
     * tempat daftarnya diubah.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pilihanKategori(): array
    {
        try {
            return collect($this->orcha()->ambil('/kategori-artikel')['data'] ?? [])
                ->map(fn ($k) => [
                    'id' => $k['id'],
                    'nama' => $k['nama'],
                    'slug' => $k['slug'],
                    'dipakai' => (bool) ($k['dipakai'] ?? false),
                ])->all();
        } catch (OrchaTidakTerjangkau) {
            return [];
        }
    }

    /** @return array{error: string|null, list?: array<int, array<string, mixed>>} */
    public function tambahKategoriKembali($nama): array
    {
        $nama = trim((string) $nama);

        if (mb_strlen($nama) < 2) {
            return ['error' => 'Nama kategori minimal 2 karakter.'];
        }

        try {
            $balasan = $this->orcha()->kirim('/kategori-artikel', ['nama' => $nama]);
        } catch (OrchaTidakTerjangkau $e) {
            return ['error' => $e->getMessage()];
        }

        cache()->forget('orcha.rujukan');

        return [
            'error' => null,
            'slug' => $balasan['data']['slug'] ?? null,
            'list' => $this->pilihanKategori(),
        ];
    }

    /**
     * Hapus rubrik.
     *
     * Rubrik yang masih dipakai ditolak OLEH ORCHA, dan pesannya diteruskan apa
     * adanya — pemeriksaannya harus di tempat datanya berada, karena lemon
     * tidak bisa tahu berapa artikel yang memakainya tanpa bertanya.
     *
     * @return array{error: string|null, list?: array<int, array<string, mixed>>}
     */
    public function hapusKategoriKembali($id): array
    {
        try {
            $this->orcha()->hapus('/kategori-artikel/'.(int) $id);
        } catch (OrchaTidakTerjangkau $e) {
            return ['error' => $e->getMessage()];
        }

        cache()->forget('orcha.rujukan');

        return ['error' => null, 'list' => $this->pilihanKategori()];
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.blog.orcha-blog-form', [
            // Nama rubrik terpilih untuk tombol pemilih. Popupnya sendiri
            // memuat daftarnya lewat pilihanKategori() saat dibuka.
            'namaKategori' => $this->kategori
                ? collect($this->pilihanKategori())->firstWhere('slug', $this->kategori)['nama'] ?? null
                : null,
        ]);
    }
}
