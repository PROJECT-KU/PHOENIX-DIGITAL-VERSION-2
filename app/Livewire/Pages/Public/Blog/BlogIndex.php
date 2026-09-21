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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function filterCategory(string $cat): void
    {
        $this->category = $this->category === $cat ? '' : $cat;
        $this->resetPage();
    }

    /** Bersihkan pencarian & kategori sekaligus (tombol pada empty state). */
    public function resetFilter(): void
    {
        $this->search = '';
        $this->category = '';
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
                        ->orWhere('category', 'like', $term);
                });
            })
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
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
        if ($this->search === '' && $this->category === '' && $this->getPage() === 1) {
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

        return view('livewire.pages.public.blog.blog-index', [
            'posts' => $posts,
            'featured' => $featured,
            'sorotan' => $sorotan,
            'lain' => $lain,
            'categories' => $categories,
        ])->layout('layouts.guest');
    }
}
