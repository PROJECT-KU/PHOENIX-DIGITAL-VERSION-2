<?php

use App\Http\Controllers\PaymentCallbackController;
use App\Livewire\Pages\Admin\Profile;
use App\Livewire\Pages\Admin\Role;
use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Admin\Dashboard;
use App\Http\Controllers\Public\HomepageController;

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

// Data Gaji Karyawan
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansCreate;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansEdit;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansList;

// Data Product
use App\Livewire\Pages\Admin\Product\ProductCreate;
use App\Livewire\Pages\Admin\Product\ProductEdit;
use App\Livewire\Pages\Admin\Product\ProductList;

// Data Paket Bundling
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsCreate;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsEdit;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsList;

// Data Spending
use App\Livewire\Pages\Admin\Spending\SpendingCreate;
use App\Livewire\Pages\Admin\Spending\SpendingEdit;
use App\Livewire\Pages\Admin\Spending\SpendingList;

// Data Loan
use App\Livewire\Pages\Admin\Loan\LoanCreate;
use App\Livewire\Pages\Admin\Loan\LoanEdit;
use App\Livewire\Pages\Admin\Loan\LoanList;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Livewire\Pages\Admin\Order\OrderList;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList;
// Data Pengembalian
use App\Livewire\Pages\Admin\Pengembalian\PengembalianList;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianCreate;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianEdit;

// Data Pemesanan RSC
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscCreate;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscEdit;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscList;
use App\Livewire\Pages\Public\Homepage\Index;
use App\Livewire\Pages\Public\ShopPage\CartPage;
use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Livewire\Pages\Public\ShopPage\Index as ShopPageIndex;
use App\Livewire\Pages\Public\ShopPage\OrderSuccessPage;
use App\Livewire\Pages\Public\ShopPage\PaymentPage;
use App\Livewire\Pages\Public\ShopPage\ProductDetail;

Route::get('/', Index::class)->name('homepage');
Route::get('/shop', ShopPageIndex::class)->name('shop.index');
Route::get('/shop/product/{id}', ProductDetail::class)->name('shop.detail-product');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/payment/{order}', PaymentPage::class)->name('payment');
Route::post('/payment/callback/midtrans', [PaymentCallbackController::class, 'midtrans'])->name('payment.callback.midtrans');
Route::get('/order/{order}/success', OrderSuccessPage::class)->name('order.success');
// Route::get('/', [HomepageController::class, 'index'])->name('homepage');
// Route::get('/productdetail/{id}', [HomepageController::class, 'productDetail'])
//     ->name('productdetail');
// Route::get('/homeproduct', [HomepageController::class, 'allProduct'])
//     ->name('homeproduct');
// Route::view('/homeproduct', 'pages.homeproduct')->name('homeproduct');
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

    // Data Product Bundling
    Route::get('/admin/DataBundlings', ProductBundlingsList::class)->name('admin.Bundlings.index');
    Route::get('/admin/DataBundlings/create', ProductBundlingsCreate::class)->name('admin.Bundlings.create');
    Route::get('/admin/DataBundlings/{ProductBundlings}', ProductBundlingsEdit::class)->name('admin.Bundlings.show');
    Route::get('/admin/DataBundlings/{ProductBundlings}/edit', ProductBundlingsEdit::class)->name('admin.Bundlings.edit');

    // Data Spending
    Route::get('/admin/spending', SpendingList::class)->name('admin.spending.index');
    Route::get('/admin/spending/create', SpendingCreate::class)->name('admin.spending.create');
    Route::get('/admin/spending/{id}/edit', SpendingEdit::class)->name('admin.spending.edit');

    // Data Loan
    Route::get('/admin/loan', LoanList::class)->name('admin.loan.index');
    Route::get('/admin/loan/create', LoanCreate::class)->name('admin.loan.create');
    Route::get('/admin/loan/{id}/edit', LoanEdit::class)->name('admin.loan.edit');

    // Data Gaji Karyawan
    Route::get('/admin/GajiKaryawan', GajiKaryawansList::class)->name('admin.gajikaryawan.index');
    Route::get('/admin/GajiKaryawan/create', GajiKaryawansCreate::class)->name('admin.gajikaryawan.create');
    Route::get('/admin/GajiKaryawan/{gajikaryawan}/edit', GajiKaryawansEdit::class)->name('admin.gajikaryawan.edit');

    // Data Pengembalian
    Route::get('/admin/pengembalian', PengembalianList::class)->name('admin.pengembalian.index');
    Route::get('/admin/pengembalian/create', PengembalianCreate::class)->name('admin.pengembalian.create');
    Route::get('/admin/pengembalian/{id}/edit', PengembalianEdit::class)->name('admin.pengembalian.edit');

    // Data Pemesanan RSC dan pemesanan toko online
    Route::get('/admin/pesananrsc', PemesananrscList::class)->name('admin.pesananrsc.index');
    Route::get('/admin/pesananrsc/create', PemesananrscCreate::class)->name('admin.pesananrsc.create');
    Route::get('/admin/pesananrsc/{pemesananrsc}/edit', PemesananrscEdit::class)->name('admin.pesananrsc.edit');
    Route::get('/admin/pesanantoko', OrderList::class)->name('admin.pesanantoko.index');

    // Route Lowongan Pekerjaan
    Route::get('/admin/lowongan', LowonganPekerjaanList::class)->name('admin.lowongan.index');
    Route::get('/admin/pelamar', PelamarKerjaList::class)->name('admin.pelamar.index');
    Route::get('/admin/pelamar/{id}', PelamarKerjaDetail::class)->name('admin.pelamar.detail');
});

require __DIR__ . '/auth.php';
