<?php

use App\Livewire\Pages\Admin\Customer\CustomerList;
use App\Livewire\Pages\Admin\Dashboard;
use App\Livewire\Pages\Admin\Product;
use App\Livewire\Pages\Admin\Profile;
use App\Livewire\Pages\Admin\Role;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');

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
    Route::get('/admin/customer/{idcustomer}/edit', CustomerList::class)->name('admin.customer.edit');
    Route::get('/admin/customer/create', CustomerList::class)->name('admin.customer.create');
});

require __DIR__ . '/auth.php';
