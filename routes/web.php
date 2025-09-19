<?php

use App\Livewire\Pages\Admin\Customer\CustomerCreate;
use App\Livewire\Pages\Admin\Customer\CustomerEdit;
use App\Livewire\Pages\Admin\Customer\CustomerList;
use App\Livewire\Pages\Admin\Dashboard;
use App\Livewire\Pages\Admin\Product;
use App\Livewire\Pages\Admin\Profile;
use App\Livewire\Pages\Admin\Role;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');
Route::view('/homeproduct', 'pages.homeproduct')->name('homeproduct');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['checkrole:admin'])->group(function () {
    Route::get('/admin/role', Role::class)->name('admin.account.role');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/product', Product::class)->name('admin.product.index');
    Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/admin/profile', Profile::class)->name('admin.account.profile');

    //customer route
    Route::get('/admin/customer', CustomerList::class)->name('admin.customer.index');
    Route::get('/admin/customer/create', CustomerCreate::class)->name('admin.customer.create');
    Route::get('/admin/customer/{customer}', CustomerEdit::class)->name('admin.customer.show');
    Route::get('/admin/customer/{customer}/edit', CustomerEdit::class)->name('admin.customer.edit');
});

require __DIR__ . '/auth.php';
