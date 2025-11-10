<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\Product;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        // Ambil banner yang status-nya active
        // $banners = Banners::where('status', 'active')->get();

        // $product = Product::latest()->take(4)->get();

        $search = $request->input('search');

        // Banner aktif
        $banners = Banners::where('status', 'active')->get();

        // Produk terbaru (limit 4) + support search
        $product = Product::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_akun', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->take(4)
            ->get();

        // Kirim ke view
        return view('pages.homepage', compact('banners', 'product', 'search'));
    }

    public function productDetail($id)
    {
        $product = Product::findOrFail($id);

        return view('pages.productdetail', compact('product'));
    }

    public function allProduct(Request $request)
    {
        $search = $request->input('search');

        $product = Product::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_akun', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString(); // <-- penting supaya search tetap ada saat pindah halaman

        return view('pages.homeproduct', compact('product', 'search'));
    }
}
