<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogImageService;
use App\Support\BedaTeks;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class BlogForm extends Component
{
    use WithFileUploads;

    public ?BlogPost $post = null;

    public $title = '';

    public $slug = '';

    public $category = '';

    public $excerpt = '';

    public $body = '';

    public $cover; // upload baru

    /** Gambar yang disisipkan ke DALAM isi artikel lewat tombol editor. */
    public $gambarIsi;

    public $existingCover = null; // nama file lama

    public $status = 'draft';

    public $published_at = '';

    public $meta_title = '';

    public $meta_description = '';

    public $mode = 'create';

    /** Slug diketik sendiri; kalau false slug mengikuti judul (hanya saat membuat). */
    public bool $slugManual = false;

    public array $tags = [];

    public string $tagBaru = '';

    public $cover_alt = '';

    /** Ringkasan & meta diketik sendiri, bukan disusun otomatis. */
    public bool $seoManual = false;

    /** Hasil periksa tautan: [['url' => ..., 'keadaan' => ..., 'pesan' => ...]] */
    public array $tautanPeriksa = [];

    public bool $sudahPeriksaTautan = false;

    /** Ditandai saat simpan otomatis berhasil — dibaca tampilan untuk pesan kecil. */
    public ?string $simpanOtomatisPada = null;

    public $unpublish_at = '';

    public $focus_keyword = '';

    /**
     * Cap waktu artikel saat formulir dibuka.
     *
     * Dipakai mendeteksi bentrok: kalau di basis data sudah lebih baru, ada
     * orang lain yang menyimpan lebih dulu dan menimpanya akan menghapus
     * pekerjaan orang itu tanpa jejak.
     */
    public ?string $capWaktu = null;

    /** Diisi saat bentrok terdeteksi; tampilan menawarkan timpa atau batal. */
    public bool $bentrok = false;

    /** Id revisi yang sedang dibandingkan dengan isi sekarang. */
    public ?int $revisiDilihat = null;

    public function mount()
    {
        if ($this->post) {
            $this->title = $this->post->title;
            $this->slug = $this->post->slug;
            $this->category = $this->post->category;
            $this->excerpt = $this->post->excerpt;
            $this->body = $this->post->body;
            $this->existingCover = $this->post->cover;
            $this->status = $this->post->status;
            $this->published_at = $this->post->published_at?->format('Y-m-d\TH:i');
            $this->meta_title = $this->post->meta_title;
            $this->meta_description = $this->post->meta_description;
            $this->tags = $this->post->tagDaftar();
            $this->cover_alt = $this->post->cover_alt ?? '';
            $this->unpublish_at = $this->post->unpublish_at?->format('Y-m-d\TH:i');
            $this->focus_keyword = $this->post->focus_keyword ?? '';
            $this->capWaktu = (string) $this->post->updated_at;
            $this->post->tandaiDibuka();
            $this->mode = 'edit';
            // Slug artikel yang sudah ada TIDAK ikut berubah saat judul diubah;
            // mengubah URL yang sudah tayang memutus tautan dan peringkatnya.
            $this->slugManual = true;
            // Kalau ringkasan/meta-nya sudah pernah disunting orang, jangan
            // ditimpa penyusun otomatis.
            $this->seoManual = filled($this->post->meta_description) || filled($this->post->excerpt);
        }
    }

    public function updatedTitle($value): void
    {
        // Slug otomatis mengikuti judul saat MEMBUAT artikel (tak perlu input manual).
        // Saat mengedit, slug lama dipertahankan agar URL/SEO tidak berubah.
        if ($this->mode === 'create' && ! $this->slugManual) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug($value): void
    {
        // Apa pun yang diketik dirapikan jadi slug yang sah, supaya URL tidak
        // pernah berisi spasi atau tanda baca.
        $this->slug = Str::slug((string) $value);
        $this->slugManual = true;
    }

    /** Kembalikan slug mengikuti judul lagi. */
    public function slugIkutJudul(): void
    {
        $this->slugManual = false;
        $this->slug = Str::slug((string) $this->title);
    }

    public function updatedBody(): void
    {
        // Isi ringkasan & SEO otomatis begitu ada isi artikel (kalau masih
        // kosong) — kecuali admin memilih mengetiknya sendiri.
        if (! $this->seoManual && trim((string) $this->excerpt) === '' && $this->plainText((string) $this->body) !== '') {
            $this->generateSeo();
        }

        // Hasil periksa tautan jadi basi begitu isinya berubah.
        $this->sudahPeriksaTautan = false;
    }

    // ===== Tag =====

    public function tambahTag(): void
    {
        $nama = trim($this->tagBaru);
        $this->tagBaru = '';

        if ($nama === '' || mb_strlen($nama) > 40) {
            return;
        }

        foreach ($this->tags as $ada) {
            if (mb_strtolower($ada) === mb_strtolower($nama)) {
                return;
            }
        }

        if (count($this->tags) >= 8) {
            $this->dispatch('swal-error', message: 'Maksimal 8 tag per artikel.');

            return;
        }

        $this->tags[] = $nama;
    }

    public function hapusTag(int $i): void
    {
        unset($this->tags[$i]);
        $this->tags = array_values($this->tags);
    }

    // ===== Hitungan langsung saat menulis =====

    public function getJumlahKataProperty(): int
    {
        $teks = $this->plainText((string) $this->body);

        return $teks === '' ? 0 : count(preg_split('/\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY));
    }

    public function getLamaBacaProperty(): int
    {
        return max(1, (int) ceil($this->jumlahKata / 200));
    }

    /**
     * Daftar periksa kelengkapan artikel.
     *
     * Ditaruh di layar supaya yang kurang ketahuan SEBELUM menekan Simpan,
     * bukan lewat pesan galat sesudahnya.
     */
    public function getKelengkapanProperty(): array
    {
        $metaPanjang = mb_strlen(trim((string) $this->meta_description));

        return [
            ['label' => 'Judul minimal 5 huruf', 'ok' => mb_strlen(trim((string) $this->title)) >= 5, 'wajib' => true],
            ['label' => 'Isi artikel minimal 300 kata', 'ok' => $this->jumlahKata >= 300, 'wajib' => false],
            ['label' => 'Kategori dipilih', 'ok' => filled($this->category), 'wajib' => false],
            ['label' => 'Gambar sampul', 'ok' => (bool) ($this->cover || $this->existingCover), 'wajib' => false],
            ['label' => 'Teks alternatif sampul', 'ok' => filled($this->cover_alt), 'wajib' => false],
            ['label' => 'Minimal satu tag', 'ok' => count($this->tags) > 0, 'wajib' => false],
            ['label' => 'Meta description 120–155 huruf', 'ok' => $metaPanjang >= 120 && $metaPanjang <= 155, 'wajib' => false],
            ['label' => 'Kata kunci fokus terisi', 'ok' => filled($this->focus_keyword), 'wajib' => false],
        ];
    }

    // ===== Periksa tautan =====

    /**
     * Cari tautan di isi artikel lalu periksa satu per satu.
     *
     * Tautan internal diperiksa ke basis data sendiri; tautan luar lewat
     * permintaan HEAD singkat. Hanya berisi alamat yang admin tulis sendiri —
     * tidak ada data pelanggan yang ikut keluar.
     */
    public function periksaTautan(): void
    {
        $this->tautanPeriksa = [];
        $this->sudahPeriksaTautan = true;

        preg_match_all('/href=["\']([^"\']+)["\']/i', (string) $this->body, $m);
        $alamat = array_values(array_unique(array_filter($m[1] ?? [], fn ($u) => ! str_starts_with($u, '#'))));

        // Batas 20: memeriksa ratusan tautan membuat permintaan menggantung
        // sampai habis waktu, dan yang menekan tombolnya menyimpulkan rusak.
        foreach (array_slice($alamat, 0, 20) as $url) {
            $this->tautanPeriksa[] = $this->periksaSatuTautan($url);
        }
    }

    private function periksaSatuTautan(string $url): array
    {
        $bersih = trim(html_entity_decode($url));

        if (str_starts_with($bersih, 'mailto:') || str_starts_with($bersih, 'tel:')) {
            return ['url' => $bersih, 'keadaan' => 'lewat', 'pesan' => 'Bukan tautan web'];
        }

        // Tautan internal ke artikel lain: cukup dicek ke basis data sendiri.
        $jalur = parse_url($bersih, PHP_URL_PATH) ?: '';
        $tuanRumah = parse_url($bersih, PHP_URL_HOST);
        $sendiri = parse_url(config('app.url'), PHP_URL_HOST);

        if ((! $tuanRumah || $tuanRumah === $sendiri) && str_starts_with($jalur, '/blog/')) {
            $slug = trim(substr($jalur, 6), '/');
            $ada = BlogPost::where('slug', $slug)->exists();

            return [
                'url' => $bersih,
                'keadaan' => $ada ? 'baik' : 'rusak',
                'pesan' => $ada ? 'Artikel ditemukan' : 'Artikel dengan slug ini tidak ada',
            ];
        }

        if (! $tuanRumah) {
            return ['url' => $bersih, 'keadaan' => 'ragu', 'pesan' => 'Tautan relatif — tidak diperiksa'];
        }

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(4)->withoutVerifying()->head($bersih);

            return [
                'url' => $bersih,
                'keadaan' => $res->successful() || $res->redirect() ? 'baik' : 'rusak',
                'pesan' => 'Jawaban '.$res->status(),
            ];
        } catch (\Throwable $e) {
            return ['url' => $bersih, 'keadaan' => 'rusak', 'pesan' => 'Tidak bisa dihubungi'];
        }
    }

    /**
     * Buat ulang ringkasan & meta SEO otomatis dari judul + isi artikel.
     * Bisa dipanggil admin lewat tombol "Acak lagi" untuk pilihan kalimat berbeda.
     */
    public function generateSeo(): void
    {
        $text = $this->plainText((string) $this->body);

        $this->excerpt = $this->pickInterestingSentence($text, 200);
        $this->meta_description = $this->pickInterestingSentence($text, 155, $this->excerpt);
        $this->meta_title = Str::limit(trim((string) $this->title), 65, '');
    }

    /**
     * @param  string|null  $sebagai  'draft' atau 'published' — dipakai dua
     *                                tombol simpan supaya statusnya tidak
     *                                harus diubah dulu di panel kanan.
     */
    public function save(?string $sebagai = null)
    {
        if (in_array($sebagai, ['draft', 'published'], true)) {
            $this->status = $sebagai;
        }

        $rules = [
            'title' => 'required|string|min:5|max:180',
            'slug' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:80',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string|min:20',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:180',
            'meta_description' => 'nullable|string|max:300',
            'cover' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'cover_alt' => 'nullable|string|max:180',
            'tags' => 'nullable|array|max:8',
            'tags.*' => 'string|max:40',
            'focus_keyword' => 'nullable|string|max:80',
            'unpublish_at' => 'nullable|date|after:published_at',
        ];

        $this->validate($rules);

        // Ada yang menyimpan lebih dulu sejak formulir ini dibuka.
        if ($this->mode === 'edit' && ! $this->bentrok && $this->adaBentrok()) {
            $this->bentrok = true;
            $this->dispatch('swal-error', message: 'Artikel ini baru saja diubah orang lain. Periksa dulu sebelum menimpanya.');

            return null;
        }

        // Kompres juga gambar yang tertanam di ISI artikel (mis. hasil paste ke editor
        // yang jadi base64 raksasa) → disimpan sebagai file WEBP ringan + lazy-load.
        $gambar = app(BlogImageService::class);
        $this->body = $gambar->processBodyImages($this->body);
        $this->body = $gambar->lengkapiAltGambar($this->body, (string) $this->title);

        // Ringkasan & meta SEO otomatis — pastikan selalu terisi, termasuk
        // saat admin memilih mengetiknya sendiri lalu membiarkannya kosong.
        $plain = $this->plainText((string) $this->body);
        if (trim((string) $this->excerpt) === '') {
            $this->excerpt = $this->pickInterestingSentence($plain, 200);
        }
        if (trim((string) $this->meta_title) === '') {
            $this->meta_title = Str::limit(trim((string) $this->title), 65, '');
        }
        if (trim((string) $this->meta_description) === '') {
            $this->meta_description = $this->pickInterestingSentence($plain, 155, $this->excerpt);
        }

        // Kalau admin mengetik kategori baru di select, daftarkan ke tabel kategori.
        $this->registerCategoryIfNew();

        if ($this->mode === 'create') {
            return $this->createPost();
        }

        return $this->updatePost();
    }

    /** Sudah ada yang menyimpan artikel ini sejak formulir dibuka? */
    private function adaBentrok(): bool
    {
        if (! $this->post || ! $this->capWaktu) {
            return false;
        }

        return (string) $this->post->fresh()?->updated_at !== $this->capWaktu;
    }

    /** Timpa saja — dipakai tombol "Tetap simpan" saat bentrok. */
    public function timpaSaja()
    {
        $this->bentrok = false;
        $this->capWaktu = (string) $this->post?->fresh()?->updated_at;

        return $this->save();
    }

    public function batalkanBentrok(): void
    {
        $this->bentrok = false;
        $this->capWaktu = (string) $this->post?->fresh()?->updated_at;
    }

    /**
     * Perpanjang penanda "sedang dibuka".
     *
     * Dipanggil berkala dari layar. Penandanya kedaluwarsa sendiri, jadi tab
     * yang ditutup paksa tidak mengunci artikel selamanya.
     */
    public function jagaKunci(): void
    {
        $this->post?->tandaiDibuka();
    }

    /** Siapa yang sedang membuka artikel ini selain saya — null bila tidak ada. */
    public function getDipegangProperty(): ?string
    {
        $segar = $this->post?->fresh();

        return $segar && $segar->dipegangOrangLain()
            ? ($segar->pembuka?->name ?: 'admin lain')
            : null;
    }

    /**
     * Angka baca 30 hari terakhir, satu nilai per hari.
     *
     * Ditaruh di layar sunting karena di sinilah keputusannya diambil: mau
     * diperbarui atau dibiarkan. Hari tanpa baris dihitung nol, supaya
     * grafiknya tidak memampatkan hari-hari sepi.
     */
    public function getGrafikBacaProperty(): array
    {
        if (! $this->post) {
            return [];
        }

        $baris = $this->post->bacaHarian()
            ->where('tanggal', '>=', now()->subDays(29)->toDateString())
            ->pluck('jumlah', 'tanggal');

        $deret = [];
        for ($i = 29; $i >= 0; $i--) {
            $hari = now()->subDays($i)->toDateString();
            $kunci = $baris->keys()->first(fn ($k) => str_starts_with((string) $k, $hari));
            $deret[] = (int) ($kunci === null ? 0 : $baris[$kunci]);
        }

        return $deret;
    }

    /** Riwayat versi artikel ini. */
    public function getRiwayatProperty()
    {
        return $this->post ? $this->post->revisi()->with('penyunting')->take(10)->get() : collect();
    }

    public function lihatBeda(int $id): void
    {
        $this->revisiDilihat = $this->revisiDilihat === $id ? null : $id;
    }

    public function tutupBeda(): void
    {
        $this->revisiDilihat = null;
    }

    /**
     * Perbedaan isi antara versi yang dipilih dan isi sekarang.
     *
     * Tanpa ini riwayat hanya bisa menjawab "kapan berubah", bukan "apa yang
     * berubah" — dan satu-satunya cara tahu adalah memulihkannya dulu.
     */
    public function getBedaProperty(): ?array
    {
        if (! $this->revisiDilihat || ! $this->post) {
            return null;
        }

        $revisi = $this->post->revisi()->whereKey($this->revisiDilihat)->first();

        if (! $revisi) {
            return null;
        }

        return [
            'revisi' => $revisi,
            'judul' => BedaTeks::antara($revisi->title, $this->title),
            'isi' => BedaTeks::antara($revisi->body, $this->body),
            'ringkas' => BedaTeks::ringkas($revisi->body, $this->body),
        ];
    }

    /** Alamat lama yang masih mengarah ke artikel ini. */
    public function getPengalihanProperty()
    {
        return $this->post ? $this->post->pengalihan()->latest('id')->get() : collect();
    }

    /**
     * Buang satu pengalihan.
     *
     * Berguna untuk alamat yang lahir dari salah ketik: membiarkannya berarti
     * alamat keliru itu selamanya sah dan bisa muncul di hasil pencarian.
     */
    public function hapusPengalihan(int $id): void
    {
        if (! auth()->user()?->hasPermission('edit_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah artikel.');

            return;
        }

        $this->post?->pengalihan()->whereKey($id)->delete();
        $this->dispatch('swal-success', message: 'Alamat lama dihapus. Tautan lama itu sekarang berakhir di halaman tidak ditemukan.');
    }

    /**
     * Kembalikan isi ke salah satu versi lama.
     *
     * Status, jadwal, dan sampul TIDAK ikut dikembalikan: memulihkan tulisan
     * tidak boleh diam-diam menerbitkan atau menurunkan artikel.
     */
    public function pulihkanRevisi(int $id): void
    {
        $revisi = $this->post?->revisi()->whereKey($id)->first();

        if (! $revisi) {
            return;
        }

        $this->post->catatRevisi();

        $this->title = $revisi->title;
        $this->excerpt = (string) $revisi->excerpt;
        $this->body = (string) $revisi->body;

        $this->dispatch('isi-dipulihkan', isi: (string) $revisi->body);
        $this->dispatch('swal-success', message: 'Isi dikembalikan ke versi '.$revisi->created_at->locale('id')->translatedFormat('d M Y H:i').'. Tekan simpan untuk menyimpannya.');
    }

    /**
     * Pemeriksaan kata kunci fokus.
     *
     * Bukan skor ajaib — hanya empat tempat yang memang menentukan apakah
     * sebuah halaman terbaca sebagai jawaban atas kata kunci itu.
     */
    public function getPeriksaKunciProperty(): array
    {
        $kunci = mb_strtolower(trim((string) $this->focus_keyword));

        if ($kunci === '') {
            return [];
        }

        $isi = mb_strtolower($this->plainText((string) $this->body));
        $awal = mb_substr($isi, 0, 300);

        return [
            ['label' => 'Muncul di judul', 'ok' => str_contains(mb_strtolower((string) $this->title), $kunci)],
            ['label' => 'Muncul di alamat artikel', 'ok' => str_contains(mb_strtolower((string) $this->slug), Str::slug($kunci))],
            ['label' => 'Muncul di paragraf awal', 'ok' => str_contains($awal, $kunci)],
            ['label' => 'Muncul di meta description', 'ok' => str_contains(mb_strtolower((string) $this->meta_description), $kunci)],
        ];
    }

    /**
     * Unggah gambar yang disisipkan ke dalam isi artikel.
     *
     * Disimpan sebagai berkas WEBP ringan lebih dulu, bukan base64 di dalam
     * naskah: satu foto kamera yang ditempel mentah bisa membengkakkan isi
     * artikel sampai puluhan megabyte dan membuat penyimpanan gagal.
     */
    public function updatedGambarIsi(): void
    {
        $this->validate([
            'gambarIsi' => 'required|image|mimes:png,jpg,jpeg,webp|max:8192',
        ], [], ['gambarIsi' => 'gambar']);

        $svc = app(BlogImageService::class);
        $nama = 'blog_isi_'.time().'_'.mt_rand(10000, 99999).'.webp';
        $dir = Storage::disk('public')->path('img/blog');

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (! $svc->compressFileToWebp($this->gambarIsi->getRealPath(), $dir.DIRECTORY_SEPARATOR.$nama, 1600)) {
            // Kompres gagal (mis. ekstensi GD tidak lengkap) — simpan apa adanya
            // supaya tombolnya tetap bisa dipakai.
            $nama = 'blog_isi_'.time().'_'.mt_rand(10000, 99999).'.'.($this->gambarIsi->getClientOriginalExtension() ?: 'jpg');
            $this->gambarIsi->storeAs('img/blog', $nama, 'public');
        }

        $this->reset('gambarIsi');

        $this->dispatch('gambar-tersisip', url: asset('storage/img/blog/'.$nama));
    }

    /**
     * Simpan diam-diam sebagai draf, tanpa berpindah halaman.
     *
     * Dipanggil berkala dari layar saat ada perubahan. Artikel yang belum
     * pernah disimpan DIBUATKAN dulu sebagai draf, lalu formulirnya berpindah
     * ke mode sunting di tempat — tanpa itu tulisan panjang yang belum sempat
     * disimpan hilang begitu tab tertutup.
     */
    public function simpanOtomatis(): void
    {
        // Terlalu kosong untuk disimpan; menyimpan artikel kosong hanya
        // menumpuk draf sampah.
        if (mb_strlen(trim((string) $this->title)) < 5 && $this->jumlahKata < 20) {
            return;
        }

        $isi = [
            'title' => trim($this->title) ?: 'Tanpa judul',
            'category' => $this->category ? trim($this->category) : null,
            'tags' => $this->tags ?: null,
            'excerpt' => $this->excerpt ? trim($this->excerpt) : null,
            'body' => (string) $this->body,
            'cover_alt' => $this->cover_alt ? trim($this->cover_alt) : null,
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
        ];

        // Saat bentrok, simpan otomatis DIAM: menulis diam-diam ke artikel
        // yang baru diubah orang lain persis hal yang mau dicegah.
        if ($this->mode === 'edit' && ($this->bentrok || $this->adaBentrok())) {
            $this->bentrok = true;

            return;
        }

        if ($this->mode === 'create') {
            $post = BlogPost::create($isi + [
                'slug' => BlogPost::makeSlug($this->slug ?: $isi['title']),
                // Simpan otomatis SELALU draf. Menerbitkan tulisan yang belum
                // selesai tanpa diminta jauh lebih merugikan daripada hilang.
                'status' => 'draft',
                'published_at' => null,
                'author' => 'admin',
                // Ditandai belum pernah disimpan sengaja — inilah yang boleh
                // dipangkas kalau ditinggalkan berbulan-bulan.
                'disimpan_manual' => false,
            ]);

            $this->post = $post;
            $this->slug = $post->slug;
            $this->mode = 'edit';
            $this->slugManual = true;
            $this->post->tandaiDibuka();
        } else {
            $this->post->update($isi);
        }

        $this->capWaktu = (string) $this->post->fresh()->updated_at;

        $this->simpanOtomatisPada = now()->format('H:i');
        $this->dispatch('artikel-tersimpan-otomatis');
    }

    private function storeCover(): ?string
    {
        if (! ($this->cover && is_object($this->cover))) {
            return null;
        }

        $svc = app(BlogImageService::class);
        $filename = 'blog_'.time().'_'.mt_rand(10000, 99999).'.webp';
        $dir = Storage::disk('public')->path('img/blog');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Kompres + kecilkan (maks lebar 1600px, kualitas 82) tanpa bikin blur.
        if ($svc->compressFileToWebp($this->cover->getRealPath(), $dir.DIRECTORY_SEPARATOR.$filename, $svc->coverMaxWidth)) {
            return $filename;
        }

        // Fallback: kalau kompres gagal, simpan file asli apa adanya.
        $orig = 'blog_'.time().'_'.mt_rand(10000, 99999).'.'.($this->cover->getClientOriginalExtension() ?: 'jpg');
        $this->cover->storeAs('img/blog', $orig, 'public');

        return $orig;
    }

    private function registerCategoryIfNew(): void
    {
        $name = trim((string) $this->category);
        if ($name === '') {
            return;
        }

        if (! BlogCategory::where('name', $name)->exists()) {
            BlogCategory::create([
                'name' => $name,
                'slug' => BlogCategory::makeSlug($name),
            ]);
        }
    }

    private function resolvePublishedAt(): ?string
    {
        // Saat dipublikasikan tapi tanggal kosong → pakai waktu sekarang.
        if ($this->status === 'published') {
            return $this->published_at ?: now()->format('Y-m-d H:i:s');
        }

        return $this->published_at ?: null;
    }

    private function createPost()
    {
        try {
            $filename = $this->storeCover();

            BlogPost::create([
                'title' => trim($this->title),
                'slug' => BlogPost::makeSlug($this->slug ?: $this->title),
                'category' => $this->category ? trim($this->category) : null,
                'excerpt' => $this->excerpt ? trim($this->excerpt) : null,
                'body' => $this->body,
                'cover' => $filename,
                'status' => $this->status,
                'published_at' => $this->resolvePublishedAt(),
                'cover_alt' => $this->cover_alt ? trim($this->cover_alt) : null,
                'tags' => $this->tags ?: null,
                'unpublish_at' => $this->unpublish_at ?: null,
                'focus_keyword' => $this->focus_keyword ? trim($this->focus_keyword) : null,
                'disimpan_manual' => true,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                // Statis "admin", bukan nama akun yang login: halaman publik
                // tidak boleh menampilkan nama karyawan.
                'author' => 'admin',
            ]);

            session()->flash('successCreated', 'Artikel blog berhasil ditambahkan!');

            return redirect()->route('admin.blog.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan artikel: '.$e->getMessage());
        }
    }

    private function updatePost()
    {
        try {
            $data = [
                'title' => trim($this->title),
                'slug' => BlogPost::makeSlug($this->slug ?: $this->title, $this->post->id),
                'category' => $this->category ? trim($this->category) : null,
                'excerpt' => $this->excerpt ? trim($this->excerpt) : null,
                'body' => $this->body,
                'status' => $this->status,
                'published_at' => $this->resolvePublishedAt(),
                'cover_alt' => $this->cover_alt ? trim($this->cover_alt) : null,
                'tags' => $this->tags ?: null,
                'unpublish_at' => $this->unpublish_at ?: null,
                'focus_keyword' => $this->focus_keyword ? trim($this->focus_keyword) : null,
                'disimpan_manual' => true,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
            ];

            if ($this->cover && is_object($this->cover)) {
                if ($this->existingCover && Storage::disk('public')->exists('img/blog/'.$this->existingCover)) {
                    Storage::disk('public')->delete('img/blog/'.$this->existingCover);
                }
                $data['cover'] = $this->storeCover();
            }

            // Riwayat dicatat SEBELUM ditimpa — yang dicari orang saat ingin
            // kembali adalah versi yang barusan tergantikan.
            $this->post->catatRevisi();

            $slugLama = $this->post->slug;
            $this->post->update($data);

            // Alamat lama tetap hidup supaya tautan yang sudah beredar tidak
            // mati begitu slug diubah.
            $this->post->catatAlamatLama($slugLama);
            $this->post->lepasTandaDibuka();

            session()->flash('successUpdated', 'Perubahan artikel berhasil disimpan!');

            return redirect()->route('admin.blog.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate artikel: '.$e->getMessage());
        }
    }

    /* ==================== Ringkasan & SEO otomatis dari isi ==================== */

    /**
     * Ubah HTML artikel menjadi teks polos yang bersih (untuk analisis kalimat).
     */
    private function plainText(string $html): string
    {
        $t = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);
        $t = strip_tags((string) $t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /**
     * Ambil SATU kalimat "menarik" dari isi artikel (bukan kalimat pertama, dipilih
     * acak di antara kandidat terbaik) untuk ringkasan/meta yang enak & SEO-friendly.
     */
    private function pickInterestingSentence(string $text, int $maxLen, ?string $exclude = null): string
    {
        if ($text === '') {
            return '';
        }

        // Pecah jadi kalimat.
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sentences = array_map('trim', $sentences);

        // Buang kalimat PERTAMA (permintaan: jangan ambil kalimat awal).
        if (count($sentences) > 1) {
            array_shift($sentences);
        }

        // Kata pemikat → menaikkan skor "menarik".
        $keywords = ['tips', 'cara', 'kenapa', 'mengapa', 'penting', 'gratis', 'mudah', 'terbaik',
            'hemat', 'aman', 'bergaransi', 'rahasia', 'panduan', 'solusi', 'cepat', 'wajib',
            'harus', 'manfaat', 'keuntungan', 'trik', 'langkah', 'pilih', 'hindari', 'kunci'];

        $scored = [];
        foreach ($sentences as $s) {
            $len = mb_strlen($s);
            if ($len < 40 || $len > 240) {
                continue; // terlalu pendek/panjang → lewati
            }
            if ($exclude !== null && $s === $exclude) {
                continue;
            }

            $score = 0;
            $low = mb_strtolower($s);
            foreach ($keywords as $k) {
                if (str_contains($low, $k)) {
                    $score += 3;
                }
            }
            if (preg_match('/\d/', $s)) {
                $score += 2; // ada angka → cenderung informatif
            }
            if ($len >= 80 && $len <= 170) {
                $score += 2; // panjang ideal untuk ringkasan
            }

            $scored[] = ['s' => $s, 'score' => $score];
        }

        if (empty($scored)) {
            // Fallback: potong dari teks (tetap hindari mulai dari nol kalau bisa).
            return Str::limit($text, $maxLen);
        }

        // Ambil kandidat terbaik lalu pilih ACAK di antaranya (variasi tiap generate).
        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = $scored[0]['score'];
        $candidates = array_values(array_filter($scored, fn ($x) => $x['score'] >= max(0, $top - 2)));
        if (empty($candidates)) {
            $candidates = $scored;
        }

        $pick = $candidates[array_rand($candidates)]['s'];

        return Str::limit($pick, $maxLen);
    }

    /* ==================== Category picker (popup) ==================== */

    /** Daftar kategori + flag apakah sedang dipakai artikel. */
    public function categoryOptions(): array
    {
        return BlogCategory::orderBy('name')->get()->map(fn ($c) => [
            'name' => $c->name,
            'used' => BlogPost::where('category', $c->name)->exists(),
        ])->all();
    }

    public function addCategoryReturn($name): array
    {
        if (! auth()->user()->hasPermission('create_blog')) {
            return ['error' => 'Anda tidak memiliki izin menambah kategori.'];
        }

        $name = trim((string) $name);
        $v = Validator::make(
            ['name' => $name],
            ['name' => 'required|string|min:2|max:60|unique:blog_categories,name'],
            [],
            ['name' => 'nama kategori']
        );
        if ($v->fails()) {
            return ['error' => $v->errors()->first()];
        }

        BlogCategory::create(['name' => $name, 'slug' => BlogCategory::makeSlug($name)]);

        return ['error' => null, 'list' => $this->categoryOptions()];
    }

    public function deleteCategoryReturn($name): array
    {
        if (! auth()->user()->hasPermission('delete_blog')) {
            return ['error' => 'Anda tidak memiliki izin menghapus kategori.'];
        }

        $cat = BlogCategory::where('name', trim((string) $name))->first();
        if (! $cat) {
            return ['error' => 'Kategori tidak ditemukan.'];
        }
        if (BlogPost::where('category', $cat->name)->exists()) {
            return ['error' => 'Kategori masih dipakai artikel, tidak bisa dihapus.'];
        }

        $cat->delete();

        return ['error' => null, 'list' => $this->categoryOptions()];
    }

    public function render()
    {
        $categories = BlogCategory::orderBy('name')->pluck('name');

        return view('livewire.pages.admin.blog.blog-form', [
            'categories' => $categories,
        ]);
    }
}
