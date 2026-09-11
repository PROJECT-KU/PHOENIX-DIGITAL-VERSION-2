<?php

namespace App\Livewire\Components;

use App\Models\ProductReview;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ProductReviews extends Component
{
    /** Ulasan yang tampil pertama kali, dan tambahan tiap "Tampilkan lagi". */
    public const PER_MUAT = 5;

    /**
     * Batas atas ulasan dalam satu halaman. $tampil adalah properti publik yang
     * bisa diubah dari peramban; tanpa batas, satu permintaan bisa meminta
     * seluruh tabel sekaligus.
     */
    public const TAMPIL_MAKS = 200;

    public const URUTAN = ['terbaru', 'tertinggi', 'terendah'];

    public $productId;

    public $nama = '';

    public $rating = 5;

    public $ulasan = '';

    public bool $submitted = false;

    public int $tampil = self::PER_MUAT;

    /** Saring bintang 1–5, atau null untuk semua. */
    public ?int $bintang = null;

    public string $urut = 'terbaru';

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

    public function muatLagi(): void
    {
        $this->tampil = min($this->tampil + self::PER_MUAT, self::TAMPIL_MAKS);
    }

    /** Klik bintang yang sama sekali lagi melepas saringannya. */
    public function saringBintang($bintang = null): void
    {
        $b = (int) $bintang;
        $this->bintang = ($b >= 1 && $b <= 5 && $this->bintang !== $b) ? $b : null;
        $this->tampil = self::PER_MUAT;
    }

    public function updatedUrut(): void
    {
        $this->tampil = self::PER_MUAT;
    }

    public function render()
    {
        // Nilai dari peramban dirapikan dulu sebelum menyentuh query.
        if (! in_array($this->urut, self::URUTAN, true)) {
            $this->urut = 'terbaru';
        }
        if ($this->bintang !== null && ($this->bintang < 1 || $this->bintang > 5)) {
            $this->bintang = null;
        }
        $this->tampil = max(self::PER_MUAT, min($this->tampil, self::TAMPIL_MAKS));

        $base = ProductReview::approved()->where('product_id', $this->productId);

        // Skor, jumlah, dan sebaran selalu dari SEMUA ulasan — saringan hanya
        // mengubah daftar yang dibaca, bukan ringkasannya.
        $daftar = (clone $base)->when($this->bintang, fn ($q) => $q->where('rating', $this->bintang));

        match ($this->urut) {
            'tertinggi' => $daftar->orderByDesc('rating')->latest()->orderByDesc('id'),
            'terendah' => $daftar->orderBy('rating')->latest()->orderByDesc('id'),
            default => $daftar->latest()->orderByDesc('id'),
        };

        return view('livewire.components.product-reviews', [
            'reviews' => (clone $daftar)->take($this->tampil)->get(),
            'jumlahTersaring' => (clone $daftar)->reorder()->count(),
            'avg' => (clone $base)->avg('rating'),
            'count' => (clone $base)->count(),
            // Jumlah ulasan per bintang (5 => 12, 4 => 3, …) untuk batang sebaran.
            'sebaran' => (clone $base)->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating')->all(),
        ]);
    }
}
