<?php

namespace App\Livewire\Pages\Admin\ProductReview;

use App\Models\ProductReview;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewModeration extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->resetPage();
    }

    public function approve($id): void
    {
        ProductReview::whereKey($id)->update(['status' => 'approved']);
        $this->dispatch('swal-success', message: 'Ulasan disetujui & kini tampil di produk.');
    }

    public function reject($id): void
    {
        ProductReview::whereKey($id)->update(['status' => 'hidden']);
        $this->dispatch('swal-success', message: 'Ulasan disembunyikan.');
    }

    public function remove($id): void
    {
        ProductReview::whereKey($id)->delete();
        $this->dispatch('swal-success', message: 'Ulasan dihapus permanen.');
    }

    public function render()
    {
        $reviews = ProductReview::with('product')
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('ulasan', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p->where('nama_akun', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15);

        $tabCounts = [
            'all' => ProductReview::count(),
            'pending' => ProductReview::where('status', 'pending')->count(),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'hidden' => ProductReview::where('status', 'hidden')->count(),
        ];

        return view('livewire.pages.admin.product-review.review-moderation', [
            'reviews' => $reviews,
            'tabCounts' => $tabCounts,
        ])->layout('livewire.layout.templateindex');
    }
}
