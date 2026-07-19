<?php

namespace App\Livewire\Pages\Public\Blog;

use App\Models\BlogPost;
use Livewire\Attributes\Layout;
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
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $posts = $query->paginate(9);

        // Artikel unggulan (terbaru) hanya di halaman pertama tanpa filter.
        $featured = null;
        if ($this->search === '' && $this->category === '' && $this->getPage() === 1) {
            $featured = BlogPost::published()->orderByDesc('published_at')->orderByDesc('id')->first();
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
            'categories' => $categories,
        ])->layout('layouts.guest');
    }
}
