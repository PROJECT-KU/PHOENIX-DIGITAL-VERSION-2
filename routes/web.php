<?php

use App\Livewire\Pages\Admin\Dashboard;
use App\Livewire\Pages\Admin\Product;
use App\Livewire\Pages\Admin\Profile;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/product', Product::class)->name('admin.product.index');
    Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/admin/profile', Profile::class)->name('admin.profile');
});

require __DIR__ . '/auth.php';
