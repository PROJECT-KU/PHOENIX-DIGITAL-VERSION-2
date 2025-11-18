<?php

namespace App\Livewire\Pages\Public\Homepage;

use App\Models\Banners;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public $search = '';

    #[Layout('layouts.guest')]
    public function render()
    {
        $banners = Banners::where('status', 'active')->get();
        $products = Product::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('nama_akun', 'like', "%{$this->search}%")
                    ->orWhere('deskripsi', 'like', "%{$this->search}%");
            });
        })
            ->latest()
            ->take(4)
            ->get();

        return view('livewire.pages.public.homepage.index', [
            'banners' => $banners,
            'products' => $products
        ]);
    }
}
