<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banners;

class HomepageController extends Controller
{
    public function index()
    {
        // Ambil banner yang status-nya active
        $banners = Banners::where('status', 'active')->get();

        // Kirim ke view
        return view('pages.homepage', compact('banners'));
    }
}
