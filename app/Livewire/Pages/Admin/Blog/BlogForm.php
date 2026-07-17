<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogImageService;
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

    public $existingCover = null; // nama file lama

    public $status = 'draft';

    public $published_at = '';

    public $meta_title = '';

    public $meta_description = '';

    public $mode = 'create';

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
            $this->mode = 'edit';
        }
    }

    public function updatedTitle($value): void
    {
        // Slug otomatis mengikuti judul saat MEMBUAT artikel (tak perlu input manual).
        // Saat mengedit, slug lama dipertahankan agar URL/SEO tidak berubah.
        if ($this->mode === 'create') {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedBody(): void
    {
        // Isi ringkasan & SEO otomatis begitu ada isi artikel (kalau masih kosong).
        if (trim((string) $this->excerpt) === '' && $this->plainText((string) $this->body) !== '') {
            $this->generateSeo();
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

    public function save()
    {
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
        ];

        $this->validate($rules);

        // Kompres juga gambar yang tertanam di ISI artikel (mis. hasil paste ke editor
        // yang jadi base64 raksasa) → disimpan sebagai file WEBP ringan + lazy-load.
        $this->body = app(BlogImageService::class)->processBodyImages($this->body);

        // Ringkasan & meta SEO otomatis (readonly di form) — pastikan selalu terisi.
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
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                'author' => auth()->user()->name ?? null,
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
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
            ];

            if ($this->cover && is_object($this->cover)) {
                if ($this->existingCover && Storage::disk('public')->exists('img/blog/'.$this->existingCover)) {
                    Storage::disk('public')->delete('img/blog/'.$this->existingCover);
                }
                $data['cover'] = $this->storeCover();
            }

            $this->post->update($data);

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
