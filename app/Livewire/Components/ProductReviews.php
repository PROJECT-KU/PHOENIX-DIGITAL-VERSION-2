<?php

namespace App\Livewire\Components;

use App\Models\ProductReview;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ProductReviews extends Component
{
    public $productId;

    public $nama = '';

    public $rating = 5;

    public $ulasan = '';

    public bool $submitted = false;

    public function mount($productId)
    {
        $this->productId = $productId;
    }

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|min:2|max:60',
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'required|string|min:5|max:500',
        ];
    }

    public function submit()
    {
        $key = 'product-review:'.request()->ip().':'.$this->productId;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('ulasan', 'Terlalu banyak ulasan dari perangkat ini. Coba lagi nanti.');

            return;
        }

        $this->validate();
        RateLimiter::hit($key, 3600);

        ProductReview::create([
            'product_id' => $this->productId,
            'nama' => trim($this->nama),
            'rating' => (int) $this->rating,
            'ulasan' => trim($this->ulasan),
            'status' => 'pending', // menunggu persetujuan admin
        ]);

        $this->reset(['nama', 'ulasan']);
        $this->rating = 5;
        $this->submitted = true;
    }

    public function render()
    {
        $base = ProductReview::approved()->where('product_id', $this->productId);

        return view('livewire.components.product-reviews', [
            'reviews' => (clone $base)->latest()->take(20)->get(),
            'avg' => (clone $base)->avg('rating'),
            'count' => (clone $base)->count(),
        ]);
    }
}
