<?php

namespace App\Livewire\Pages\Public\Blog;

use App\Models\BlogPost;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    #[Url(as: 'kategori', keep: false)]
    public string $category = '';

    #[Url(as: 'tag', keep: false)]
    public string $tag = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function filterCategory(string $cat): void
    {
        $this->category = $this->category === $cat ? '' : $cat;
        $this->resetPage();
    }

    /** Pilih/lepas satu tag topik. */
    public function pilihTag(string $nama): void
    {
        $this->tag = $this->tag === $nama ? '' : $nama;
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    /** Bersihkan pencarian, kategori & tag sekaligus (tombol pada empty state). */
    public function resetFilter(): void
    {
        $this->search = '';
        $this->category = '';
        $this->tag = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = BlogPost::published()
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('excerpt', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        // Isi artikel ikut dicari: kalimat yang diingat orang
                        // biasanya ada di badan tulisan, bukan di judulnya.
                        ->orWhere('body', 'like', $term);
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->tag !== '', fn ($q) => $q->whereJsonContains('tags', $this->tag))
            // Artikel yang disematkan admin naik ke atas; sisanya tetap urut
            // tanggal seperti sebelumnya.
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        // 12 = kelipatan grid 4 kolom. Di halaman pertama: 1 utama + 3 "Baru
        // Terbit" + 8 kartu (dua baris penuh), tanpa kartu yatim di baris akhir.
        $posts = $query->paginate(12);

        // Artikel utama halaman blog: yang disematkan admin bila ada, kalau
        // tidak yang terbaru. Hanya di halaman pertama tanpa saringan.
        $featured = null;
        if ($this->search === '' && $this->category === '' && $this->tag === '' && $this->getPage() === 1) {
            $featured = BlogPost::published()
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->first();
        }

        // Artikel utama & "Baru Terbit" diambil dari halaman yang sama lalu
        // DIKELUARKAN dari grid, supaya tidak ada artikel yang tampil dua kali.
        $lain = $posts->getCollection();
        $sorotan = collect();
        if ($featured) {
            $lain = $lain->reject(fn ($p) => $p->id === $featured->id)->values();
            $sorotan = $lain->take(3)->values();
            $lain = $lain->slice(3)->values();
        }

        $categories = BlogPost::published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        // Tag yang benar-benar dipakai artikel terbit — jadi bilah topik di
        // halaman blog, bukan daftar mati.
        $tagDipakai = BlogPost::published()->whereNotNull('tags')->pluck('tags')
            ->flatMap(fn ($t) => (array) $t)
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(12)
            ->keys()
            ->all();

        return view('livewire.pages.public.blog.blog-index', [
            'posts' => $posts,
            'tagDipakai' => $tagDipakai,
            'ketKategori' => $this->category === ''
                ? null
                : \App\Models\BlogCategory::where('name', $this->category)->value('description'),
            'featured' => $featured,
            'sorotan' => $sorotan,
            'lain' => $lain,
            'categories' => $categories,
        ])->layout('layouts.guest');
    }
}
