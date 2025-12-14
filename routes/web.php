<?php

use App\Http\Controllers\PaymentCallbackController;
use App\Livewire\Pages\Admin\Banners\BannersCreate;
use App\Livewire\Pages\Admin\Banners\BannersEdit;
use App\Livewire\Pages\Admin\Banners\BannersList;
use App\Livewire\Pages\Admin\Customer\CustomerCreate;
// Data Banners
use App\Livewire\Pages\Admin\Customer\CustomerEdit;
use App\Livewire\Pages\Admin\Customer\CustomerList;
use App\Livewire\Pages\Admin\Dashboard;
// Data Customer
use App\Livewire\Pages\Admin\DataAkun\DataAkunCreate;
use App\Livewire\Pages\Admin\DataAkun\DataAkunEdit;
use App\Livewire\Pages\Admin\DataAkun\DataAkunList;
// Data Akun
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansCreate;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansEdit;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansList;
// Data Gaji Karyawan
use App\Livewire\Pages\Admin\Loan\LoanCreate;
use App\Livewire\Pages\Admin\Loan\LoanEdit;
use App\Livewire\Pages\Admin\Loan\LoanList;
// Data Product
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanCreate;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanEdit;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Livewire\Pages\Admin\Message\MessageDetail;
use App\Livewire\Pages\Admin\Message\MessageList;
use App\Livewire\Pages\Admin\Order\DeliverOrder;
use App\Livewire\Pages\Admin\Order\OrderDetail;
// Data Paket Bundling
use App\Livewire\Pages\Admin\Order\OrderList;
use App\Livewire\Pages\Admin\Order\ProcessOrder;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList;
// Data Promo
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscCreate;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscEdit;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscList;
// Data Spending
use App\Livewire\Pages\Admin\Pengembalian\PengembalianCreate;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianEdit;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianList;
use App\Livewire\Pages\Admin\Permission\PermissionCreate;
use App\Livewire\Pages\Admin\Permission\PermissionEdit;
use App\Livewire\Pages\Admin\Permission\PermissionList;
// Data Loan
use App\Livewire\Pages\Admin\Product\ProductCreate;
use App\Livewire\Pages\Admin\Product\ProductEdit;
use App\Livewire\Pages\Admin\Product\ProductList;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsCreate;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsEdit;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsList;
use App\Livewire\Pages\Admin\Profile\ProfileSetting;
use App\Livewire\Pages\Admin\Promo\PromoCreate;
use App\Livewire\Pages\Admin\Promo\PromoEdit;
// Data Pengembalian
use App\Livewire\Pages\Admin\Promo\PromoList;
use App\Livewire\Pages\Admin\RoleUser\RoleList;
use App\Livewire\Pages\Admin\RoleUser\RolePermissionEdit;
use App\Livewire\Pages\Admin\Spending\SpendingCreate;
// Data Pemesanan RSC
use App\Livewire\Pages\Admin\Spending\SpendingEdit;
use App\Livewire\Pages\Admin\Spending\SpendingList;
use App\Livewire\Pages\Public\Homepage\Index;
use App\Livewire\Pages\Public\ShopPage\CartPage;
use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Livewire\Pages\Public\ShopPage\Index as ShopPageIndex;
use App\Livewire\Pages\Public\ShopPage\OrderSuccessPage;
use App\Livewire\Pages\Public\ShopPage\PaymentPage;
use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use Illuminate\Support\Facades\Route;

Route::get('/', Index::class)->name('homepage');
Route::get('/shop', ShopPageIndex::class)->name('shop.index');
Route::get('/shop/product/{id}', ProductDetail::class)->name('shop.detail-product');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/payment/{order}', PaymentPage::class)->name('payment');
Route::post('/payment/callback/midtrans', [PaymentCallbackController::class, 'midtrans'])->name('payment.callback.midtrans');
Route::get('/order/{order}/success', OrderSuccessPage::class)->name('order.success');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['checkrole:admin,admin-mimin'])->group(function () {
    // Data Pemesanan RSC dan pemesanan toko online
    Route::get('/admin/pesananrsc', PemesananrscList::class)->name('admin.pesananrsc.index');
    Route::get('/admin/pesananrsc/create', PemesananrscCreate::class)->name('admin.pesananrsc.create');
    Route::get('/admin/pesananrsc/{pemesananrsc}/edit', PemesananrscEdit::class)->name('admin.pesananrsc.edit');
    Route::get('/admin/pesanantoko', OrderList::class)->name('admin.pesanantoko.index');
    Route::get('/admin/pesanantoko/{order}', OrderDetail::class)->name('admin.pesanantoko.detail');
    Route::get('/admin/pesanantoko/{id}/process', ProcessOrder::class)->name('admin.pesanantoko.process');
    Route::get('/admin/pesanantoko/{order}/deliver', DeliverOrder::class)->name('admin.pesanantoko.deliver');

    // Data Customer
    Route::get('/admin/customer', CustomerList::class)->name('admin.customer.index');
    Route::get('/admin/customer/create', CustomerCreate::class)->name('admin.customer.create');
    Route::get('/admin/customer/{customer}', CustomerEdit::class)->name('admin.customer.show');
    Route::get('/admin/customer/{customer}/edit', CustomerEdit::class)->name('admin.customer.edit');
});

Route::middleware(['checkrole:admin'])->group(function () {
    Route::get('/admin/role', RoleList::class)->name('admin.account.role');
    Route::get('/admin/role/{role}/edit', RolePermissionEdit::class)->name('admin.account.role.permission');
    Route::get('/admin/permission', PermissionList::class)->name('admin.account.permission');
    Route::get('/admin/permission/create', PermissionCreate::class)->name('admin.account.permission.create');
    Route::get('/admin/permission/{permission}/edit', PermissionEdit::class)->name('admin.account.permission.edit');
});
Route::middleware(['checkrole:admin,finance,admin-mimin'])->group(function () {
    Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/admin/profile', ProfileSetting::class)->name('admin.account.profile');
});

Route::middleware(['checkrole:admin,finance'])->group(function () {

    // Data Akun
    Route::get('/admin/DataAkun', DataAkunList::class)->name('admin.DataAkun.index');
    Route::get('/admin/DataAkun/create', DataAkunCreate::class)->name('admin.DataAkun.create');
    Route::get('/admin/DataAkun/{DataAkun}', DataAkunEdit::class)->name('admin.DataAkun.show');
    Route::get('/admin/DataAkun/{dataAkun}/edit', DataAkunEdit::class)->name('admin.DataAkun.edit');

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

    // Route Lowongan Pekerjaan
    Route::get('/admin/lowongan', LowonganPekerjaanList::class)->name('admin.lowongan.index');
    Route::get('/admin/lowongan/create', LowonganPekerjaanCreate::class)->name('admin.lowongan.create');
    Route::get('/admin/lowongan/{lowongan}/edit', LowonganPekerjaanEdit::class)->name('admin.lowongan.edit');

    // route data pelamar
    Route::get('/admin/pelamar', PelamarKerjaList::class)->name('admin.pelamar.index');
    Route::get('/admin/pelamar/{id}', PelamarKerjaDetail::class)->name('admin.pelamar.detail');

    // Route Promo
    Route::get('/admin/promo', PromoList::class)->name('admin.promo.index');
    Route::get('/admin/promo/create', PromoCreate::class)->name('admin.promo.create');
    Route::get('/admin/promo/{promo}/edit', PromoEdit::class)->name('admin.promo.edit');

    // route pesan masuk
    Route::get('/admin/message', MessageList::class)->name('admin.message.index');
    Route::get('/admin/message/{message}', MessageDetail::class)->name('admin.message.detail');
});

require __DIR__.'/auth.php';
