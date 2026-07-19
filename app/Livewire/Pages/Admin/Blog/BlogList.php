<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogList extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public string $search = '';

    #[Url(as: 'category', keep: false)]
    public string $category = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearCategory(): void
    {
        $this->category = '';
        $this->resetPage();
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->resetPage();
    }

    /**
     * Publikasikan / kembalikan ke draf.
     */
    public function togglePublish($id): void
    {
        if (! auth()->user()->hasPermission('edit_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah artikel.');

            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        if ($post->status === 'published') {
            $post->update(['status' => 'draft']);
            $this->dispatch('swal-success', message: 'Artikel dikembalikan ke draf.');
        } else {
            $post->update([
                'status' => 'published',
                'published_at' => $post->published_at ?? now(),
            ]);
            $this->dispatch('swal-success', message: 'Artikel berhasil dipublikasikan.');
        }
    }

    public function delete($id): void
    {
        if (! auth()->user()->hasPermission('delete_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus artikel.');

            return;
        }

        $post = BlogPost::find($id);
        if (! $post) {
            return;
        }

        if ($post->cover && Storage::disk('public')->exists('img/blog/'.$post->cover)) {
            Storage::disk('public')->delete('img/blog/'.$post->cover);
        }

        $post->delete();
        $this->dispatch('swal-success', message: 'Artikel berhasil dihapus.');
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('excerpt', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);

        $tabCounts = [
            'all' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
        ];

        return view('livewire.pages.admin.blog.blog-list', [
            'posts' => $posts,
            'tabCounts' => $tabCounts,
        ])->layout('livewire.layout.templateindex');
    }
}
