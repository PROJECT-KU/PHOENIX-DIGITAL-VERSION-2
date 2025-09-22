<?php

use App\Livewire\Pages\Admin\Customer\CustomerCreate;
use App\Livewire\Pages\Admin\Customer\CustomerEdit;
use App\Livewire\Pages\Admin\Customer\CustomerList;
use App\Livewire\Pages\Admin\Dashboard;
use App\Livewire\Pages\Admin\Product;
use App\Livewire\Pages\Admin\Product\ProductCreate;
use App\Livewire\Pages\Admin\Product\ProductEdit;
use App\Livewire\Pages\Admin\Product\ProductList;
use App\Livewire\Pages\Admin\Profile;
use App\Livewire\Pages\Admin\Role;
use Illuminate\Support\Facades\Route;

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

    Route::get('/admin/product', ProductList::class)->name('admin.product.index');
    Route::get('/admin/product/create', ProductCreate::class)->name('admin.product.create');
    Route::get('/admin/product/{product}', ProductEdit::class)->name('admin.product.show');
    Route::get('/admin/product/{product}/edit', ProductEdit::class)->name('admin.product.edit');

    //customer route
    Route::get('/admin/customer', CustomerList::class)->name('admin.customer.index');
    Route::get('/admin/customer/create', CustomerCreate::class)->name('admin.customer.create');
    Route::get('/admin/customer/{customer}', CustomerEdit::class)->name('admin.customer.show');
    Route::get('/admin/customer/{customer}/edit', CustomerEdit::class)->name('admin.customer.edit');
});

require __DIR__ . '/auth.php';
