<?php

use App\Livewire\Pages\Admin\Profile;
use App\Livewire\Pages\Admin\Role;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Admin\Dashboard;

// Data Banners
use App\Livewire\Pages\Admin\Banners\BannersCreate;
use App\Livewire\Pages\Admin\Banners\BannersEdit;
use App\Livewire\Pages\Admin\Banners\BannersList;

// Data Customer
use App\Livewire\Pages\Admin\Customer\CustomerCreate;
use App\Livewire\Pages\Admin\Customer\CustomerEdit;
use App\Livewire\Pages\Admin\Customer\CustomerList;

// Data Akun
use App\Livewire\Pages\Admin\DataAkun\DataAkunCreate;
use App\Livewire\Pages\Admin\DataAkun\DataAkunEdit;
use App\Livewire\Pages\Admin\DataAkun\DataAkunList;

// Data Product
use App\Livewire\Pages\Admin\Product\ProductCreate;
use App\Livewire\Pages\Admin\Product\ProductEdit;
use App\Livewire\Pages\Admin\Product\ProductList;

Route::view('/', 'pages.homepage')->name('home');
Route::view('/homeproduct', 'pages.homeproduct')->name('homeproduct');
Route::view('/cekout', 'pages.cekout')->name('cekout');
Route::view('/about', 'pages.about')->name('about');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['checkrole:admin'])->group(function () {
    Route::get('/admin/role', Role::class)->name('admin.account.role');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/admin/profile', Profile::class)->name('admin.account.profile');

    // Data Akun
    Route::get('/admin/DataAkun', DataAkunList::class)->name('admin.DataAkun.index');
    Route::get('/admin/DataAkun/create', DataAkunCreate::class)->name('admin.DataAkun.create');
    Route::get('/admin/DataAkun/{DataAkun}', DataAkunEdit::class)->name('admin.DataAkun.show');
    Route::get('/admin/DataAkun/{dataAkun}/edit', DataAkunEdit::class)->name('admin.DataAkun.edit');

    // Data Customer
    Route::get('/admin/customer', CustomerList::class)->name('admin.customer.index');
    Route::get('/admin/customer/create', CustomerCreate::class)->name('admin.customer.create');
    Route::get('/admin/customer/{customer}', CustomerEdit::class)->name('admin.customer.show');
    Route::get('/admin/customer/{customer}/edit', CustomerEdit::class)->name('admin.customer.edit');

    // Data Product
    Route::get('/admin/product', ProductList::class)->name('admin.product.index');
    Route::get('/admin/product/create', ProductCreate::class)->name('admin.product.create');
    Route::get('/admin/product/{product}/edit', ProductEdit::class)->name('admin.product.edit');

    // Data Banners
    Route::get('/admin/DataBanners', BannersList::class)->name('admin.Banners.index');
    Route::get('/admin/DataBanners/create', BannersCreate::class)->name('admin.Banners.create');
    Route::get('/admin/DataBanners/{Banners}', BannersEdit::class)->name('admin.Banners.show');
    Route::get('/admin/DataBanners/{Banners}/edit', BannersEdit::class)->name('admin.Banners.edit');
});

require __DIR__ . '/auth.php';
