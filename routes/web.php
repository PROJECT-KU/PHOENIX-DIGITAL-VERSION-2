<?php

use App\Http\Controllers\Admin\OrchaEksporController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PemesananrscController;
// Data Banners
use App\Http\Controllers\PushSubscriptionController;
use App\Livewire\Pages\Admin\ActivityLog\ActivityLogList;
use App\Livewire\Pages\Admin\Banners\BannersCreate;
use App\Livewire\Pages\Admin\Banners\BannersEdit;
use App\Livewire\Pages\Admin\Banners\BannersList;
use App\Livewire\Pages\Admin\CashFlow\CashFlowDetail;
use App\Livewire\Pages\Admin\CashFlow\CashFlowList;
use App\Livewire\Pages\Admin\Customer\CustomerCreate;
use App\Livewire\Pages\Admin\Customer\CustomerEdit;
// Data Customer
use App\Livewire\Pages\Admin\Customer\CustomerList;
use App\Livewire\Pages\Admin\Dashboard;
use App\Livewire\Pages\Admin\DataAkun\DataAkunCreate;
// Data Data Akun
use App\Livewire\Pages\Admin\DataAkun\DataAkunEdit;
use App\Livewire\Pages\Admin\DataAkun\DataAkunList;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansCreate;
// Data Gajikaryawan
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansEdit;
use App\Livewire\Pages\Admin\GajiKaryawans\GajiKaryawansList;
use App\Livewire\Pages\Admin\Karyawan\KaryawanCreate;
// Data Loan
use App\Livewire\Pages\Admin\Karyawan\KaryawanEdit;
use App\Livewire\Pages\Admin\Karyawan\KaryawanList;
use App\Livewire\Pages\Admin\Loan\LoanCreate;
// Data Gaji Karyawan
use App\Livewire\Pages\Admin\Loan\LoanEdit;
use App\Livewire\Pages\Admin\Loan\LoanList;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanCreate;
// Data Lowongan Pekerjaan
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanEdit;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Livewire\Pages\Admin\Message\CustomerMessageDetail;
use App\Livewire\Pages\Admin\Message\CustomerMessageList;
use App\Livewire\Pages\Admin\Message\MessageDetail;
use App\Livewire\Pages\Admin\Message\MessageList;
use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaForm;
use App\Livewire\Pages\Admin\Orcha\Armada\OrchaArmadaList;
use App\Livewire\Pages\Admin\Orcha\Dashboard\OrchaDashboard;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaEtalaseList;
// Data Order
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketList;
use App\Livewire\Pages\Admin\Orcha\Pembatalan\OrchaPembatalanDetail;
use App\Livewire\Pages\Admin\Orcha\Pembatalan\OrchaPembatalanList;
use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranList;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
// Orcha Journey — data dari aplikasi tetangga lewat API
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanDetail;
use App\Livewire\Pages\Admin\Orcha\Penyewaan\OrchaPenyewaanList;
use App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanDetail;
use App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList;
use App\Livewire\Pages\Admin\Order\OrderCreate;
use App\Livewire\Pages\Admin\Order\OrderDetail;
use App\Livewire\Pages\Admin\Order\OrderList;
use App\Livewire\Pages\Admin\Order\ProcessOrder;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList;
// Data Paket Bundling
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscCreate;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscDetail;
// Data Promo
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscEdit;
use App\Livewire\Pages\Admin\PemesananRSC\PemesananrscList;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianCreate;
// Data Spending
use App\Livewire\Pages\Admin\Pengembalian\PengembalianEdit;
use App\Livewire\Pages\Admin\Pengembalian\PengembalianList;
use App\Livewire\Pages\Admin\Permission\PermissionCreate;
use App\Livewire\Pages\Admin\Permission\PermissionEdit;
// Data Spending
use App\Livewire\Pages\Admin\Permission\PermissionList;
use App\Livewire\Pages\Admin\Product\ProductCreate;
use App\Livewire\Pages\Admin\Product\ProductEdit;
use App\Livewire\Pages\Admin\Product\ProductList;
// Data Product Admin
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsCreate;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsEdit;
use App\Livewire\Pages\Admin\ProductBundlings\ProductBundlingsList;
// Data Loan
use App\Livewire\Pages\Admin\Profile\ProfileSetting;
use App\Livewire\Pages\Admin\Promo\PromoCreate;
use App\Livewire\Pages\Admin\Promo\PromoEdit;
// Data Product Bundling
use App\Livewire\Pages\Admin\Promo\PromoList;
use App\Livewire\Pages\Admin\RoleUser\RoleList;
use App\Livewire\Pages\Admin\RoleUser\RolePermissionEdit;
use App\Livewire\Pages\Admin\Spending\SpendingCreate;
// Data Pengembalian
use App\Livewire\Pages\Admin\Spending\SpendingEdit;
use App\Livewire\Pages\Admin\Spending\SpendingList;
use App\Livewire\Pages\Admin\Testimoni\TestimoniCreate;
// Data Spending
use App\Livewire\Pages\Admin\Testimoni\TestimoniEdit;
use App\Livewire\Pages\Admin\Testimoni\TestimoniList;
// Data Pemesanan RSC
use App\Livewire\Pages\Public\About\AboutPage;
use App\Livewire\Pages\Public\Bundling\Index as BundlingPageIndex;
use App\Livewire\Pages\Public\Bundling\ProductBundlings;
use App\Livewire\Pages\Public\Contact\Contact;
use App\Livewire\Pages\Public\Homepage\Index;
use App\Livewire\Pages\Public\Legal\PrivacyPage;
use App\Livewire\Pages\Public\Legal\TermsPage;
use App\Livewire\Pages\Public\ShopPage\CartPage;
use App\Livewire\Pages\Public\ShopPage\CheckoutPage;
use App\Livewire\Pages\Public\ShopPage\Index as ShopPageIndex;
use App\Livewire\Pages\Public\ShopPage\OrderHistory;
use App\Livewire\Pages\Public\ShopPage\OrderSuccessPage;
use App\Livewire\Pages\Public\ShopPage\PaymentExpired;
use App\Livewire\Pages\Public\ShopPage\PaymentPage;
use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use Illuminate\Support\Facades\Route;

// Service worker PWA — versi cache OTOMATIS mengikuti build (hash manifest Vite),
// jadi setiap deploy cache lama dibuang sendiri tanpa perlu bump manual.
// Pakai controller (bukan closure) agar `php artisan route:cache` tetap jalan.
Route::get('/sw.js', \App\Http\Controllers\ServiceWorkerController::class)->name('sw.js');

Route::get('/', Index::class)->name('homepage');

// Cron CADANGAN via HTTP (dipicu layanan eksternal tiap menit) — jaga-jaga bila
// cron hPanel mati. Token dari config/cron.php; kosong => 404.
Route::get('/cron/run/{token}', \App\Http\Controllers\CronController::class)->name('cron.run');

// Webhook bot Telegram (agen operasional admin). Dijaga secret di header +
// daftar chat ID; token/secret kosong => 404. Dikecualikan dari CSRF di
// App\Http\Middleware\VerifyCsrfToken, sama pola dengan callback Midtrans.
Route::post('/telegram/webhook', \App\Http\Controllers\TelegramWebhookController::class)
    ->name('telegram.webhook');

// Struk pakai token pendek (tanpa expose UUID)
Route::get('/s/{token}', [\App\Http\Controllers\OrderReceiptController::class, 'show'])->name('order.receipt');

// Ebook viewer view-only (link pendek) + streaming terproteksi
Route::get('/e/{token}', [\App\Http\Controllers\EbookViewerController::class, 'show'])->name('ebook.view');
Route::get('/e/{token}/raw', [\App\Http\Controllers\EbookViewerController::class, 'raw'])->name('ebook.raw');

// Umpan produk yang DITARIK Meta secara terjadwal untuk Katalog (WhatsApp,
// Instagram, iklan dinamis). Sengaja tanpa autentikasi: perayap Meta tidak
// membawa sesi, dan isinya memang sama dengan yang sudah tampil di /shop.
Route::get('/katalog/meta.xml', \App\Http\Controllers\KatalogMetaController::class)->name('katalog.meta');

Route::get('/shop', ShopPageIndex::class)->name('shop.index');
Route::get('/shop/product/{id}', ProductDetail::class)->name('shop.detail-product');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/payment/{order}', PaymentPage::class)->name('payment');
Route::get('/order/expired/{order}', PaymentExpired::class)->name('order.expired');
Route::post('/payment/callback/midtrans', [PaymentCallbackController::class, 'midtrans'])->name('payment.callback.midtrans');
Route::get('/order/{order}/success', OrderSuccessPage::class)->name('order.success');

// JASA cek plagiasi — link permanen ber-token (unggah + progress + unduh hasil)
Route::get('/cek/{token}', \App\Livewire\Pages\Public\ShopPage\JasaCekPage::class)->name('jasa.cek');
Route::get('/cek/{token}/hasil/{upload}', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilPublik'])->name('jasa.cek.hasil');
Route::get('/cek/{token}/hasil-ai/{upload}', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilAiPublik'])->name('jasa.cek.hasil-ai');
Route::get('/cek/{token}/hasil-docx/{upload}', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilDocxPublik'])->name('jasa.cek.hasil-docx');
Route::get('/qris/{token}', \App\Livewire\Pages\Public\ShopPage\QrisShare::class)->name('qris.show');
Route::view('/cekout', 'pages.cekout')->name('cekout');
Route::view('/about', 'pages.about')->name('about');
Route::get('/bundling', BundlingPageIndex::class)->name('bundling.index');
Route::get('/bundling/product', ProductBundlings::class)->name('bundling.product-bundlings');
Route::get('/order/history', OrderHistory::class)->name('order.history');
Route::get('/contact', Contact::class)->name('contact');
Route::get('/about', AboutPage::class)->name('about');
Route::get('/terms', TermsPage::class)->name('terms');
Route::get('/privacy', PrivacyPage::class)->name('privacy');
Route::get('/faq', \App\Livewire\Pages\Public\Legal\FaqPage::class)->name('faq');
Route::get('/member', \App\Livewire\Pages\Public\Legal\MemberPage::class)->name('member.info');
Route::get('/layanan', \App\Livewire\Pages\Public\Services\ServicesPage::class)->name('services');
Route::get('/lacak-pesanan', \App\Livewire\Pages\Public\ShopPage\TrackOrder::class)->name('track-order');
Route::get('/wishlist', \App\Livewire\Pages\Public\ShopPage\WishlistPage::class)->name('wishlist');
Route::get('/blog', \App\Livewire\Pages\Public\Blog\BlogIndex::class)->name('blog.index');
Route::get('/blog/{post}', \App\Livewire\Pages\Public\Blog\BlogShow::class)->name('blog.show');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');
// Preview invoice DIPINDAH ke grup 'permission:view_pesananrsc' di bawah.
// Sebelumnya terdaftar di blok publik tanpa auth sama sekali, sehingga siapa
// pun bisa menarik PDF invoice berisi data camp & peserta tanpa login.

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// ======================= AKSES BERBASIS PERMISSION =======================

// Dashboard
Route::middleware('permission:view_dashboard')->group(function () {
    Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
});

// Profil — semua pengguna yang login boleh mengakses profilnya sendiri
Route::middleware('auth')->group(function () {
    Route::get('/admin/profile', ProfileSetting::class)->name('admin.account.profile');

    // Manifest PWA — hanya user yang sudah login yang bisa install aplikasi.
    // Tamu yang akses langsung akan di-redirect ke login (manifest tak valid → tak bisa install).
    Route::get('/manifest.webmanifest', function () {
        return response()->json([
            'name' => 'lemon by acm',
            'short_name' => 'lemon',
            'description' => 'Aplikasi admin lemon by acm',
            'start_url' => '/admin/dashboard?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => '#fffdf2',
            'theme_color' => '#84cc16',
            'lang' => 'id',
            'dir' => 'ltr',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    })->name('pwa.manifest');

    // Langganan Web Push (aktifkan/matikan notifikasi perangkat)
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Jumlah notifikasi belum dibaca (bulan berjalan) — untuk sinkron badge saat app fokus.
    Route::get('/notifications/unread-count', function () {
        $u = auth()->user();
        $count = $u
            ? $u->unreadNotifications()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count()
            : 0;

        return response()->json(['count' => $count]);
    })->name('notifications.unread-count');
});

// Pesanan RSC
Route::middleware('permission:view_pesananrsc')->group(function () {
    Route::get('/admin/pesananrsc', PemesananrscList::class)->name('admin.pesananrsc.index');
    Route::get('/admin/pesananrsc/create', PemesananrscCreate::class)->middleware('permission:create_pesananrsc')->name('admin.pesananrsc.create');
    Route::get('/admin/pesananrsc/{nama_camp}/{batch_camp}/edit', PemesananrscEdit::class)
        ->middleware('permission:edit_pesananrsc')->name('admin.pesananrsc.edit');
    Route::get('/admin/pesananrsc/detail/{nama_camp}/{batch_camp}', PemesananrscDetail::class)->name('admin.pesananrsc.detail');
    Route::get('/admin/preview-invoice', [PemesananrscController::class, 'previewInvoice'])->name('admin.preview.invoice');
});

// Pesanan Toko
Route::middleware('permission:view_pemesanantoko')->group(function () {
    Route::get('/admin/pesanantoko', OrderList::class)->name('admin.pesanantoko.index');
    // Bukti pembayaran: berkas sensitif, hanya lewat route ber-izin.
    Route::get('/admin/pesanantoko/{order}/bukti', [\App\Http\Controllers\BerkasPrivatController::class, 'buktiPembayaran'])
        ->name('admin.pesanantoko.bukti');
    Route::get('/admin/pesanantoko/create', OrderCreate::class)->middleware('permission:create_pemesanantoko')->name('admin.pesanantoko.create');
    Route::get('/admin/pesanantoko/{id}/process', ProcessOrder::class)->middleware('permission:edit_pemesanantoko')->name('admin.pesanantoko.process');
    Route::get('/admin/pesanantoko/{order}/qris', \App\Livewire\Pages\Admin\Order\QrisPayment::class)->name('admin.pesanantoko.qris');
    // Unggah bukti untuk pesanan DRAFT bermetode transfer / QRIS statis.
    // Namanya sengaja 'unggah-bukti', bukan 'bukti' — nama itu sudah dipakai
    // route UNDUH berkas bukti di atas.
    Route::get('/admin/pesanantoko/{order}/unggah-bukti', \App\Livewire\Pages\Admin\Order\BuktiPembayaran::class)
        ->middleware('permission:edit_pemesanantoko')
        ->name('admin.pesanantoko.unggah-bukti');
    // Unduh berkas pengecekan jasa (file masuk customer & file hasil) — disk privat
    Route::get('/admin/pesanantoko/upload/{upload}/berkas', [\App\Http\Controllers\JasaCekController::class, 'unduhBerkasAdmin'])->name('admin.jasa.berkas');
    Route::get('/admin/pesanantoko/upload/{upload}/hasil-ai', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilAiAdmin'])->name('admin.jasa.hasil-ai');
    Route::get('/admin/pesanantoko/upload/{upload}/hasil-docx', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilDocxAdmin'])->name('admin.jasa.hasil-docx');
    Route::get('/admin/pesanantoko/upload/{upload}/pdf', [\App\Http\Controllers\JasaCekController::class, 'unduhPdfAdmin'])->name('admin.jasa.pdf');
    Route::get('/admin/pesanantoko/upload/{upload}/hasil', [\App\Http\Controllers\JasaCekController::class, 'unduhHasilAdmin'])->name('admin.jasa.hasil');
    Route::get('/admin/pesanantoko/{order}', OrderDetail::class)->name('admin.pesanantoko.detail');
});

// Data Customer
Route::middleware('permission:view_customer')->group(function () {
    Route::get('/admin/customer', CustomerList::class)->name('admin.customer.index');
    Route::get('/admin/customer/create', CustomerCreate::class)->middleware('permission:create_customer')->name('admin.customer.create');
    Route::get('/admin/customer/{customer}', CustomerEdit::class)->name('admin.customer.show');
    Route::get('/admin/customer/{customer}/edit', CustomerEdit::class)->middleware('permission:edit_customer')->name('admin.customer.edit');
});

// Customer Message
Route::middleware('permission:view_customer_message')->group(function () {
    Route::get('/admin/customer-message', CustomerMessageList::class)->name('admin.customer-message.index');
    Route::get('/admin/customer-message/{message}', CustomerMessageDetail::class)->name('admin.customer-message.detail');
});

// Role
Route::middleware('permission:view_roles')->group(function () {
    Route::get('/admin/role', RoleList::class)->name('admin.account.role');
    Route::get('/admin/role/{role}/edit', RolePermissionEdit::class)->middleware('permission:edit_roles')->name('admin.account.role.permission');
});

// Permission
Route::middleware('permission:view_permission')->group(function () {
    Route::get('/admin/permission', PermissionList::class)->name('admin.account.permission');
    Route::get('/admin/permission/create', PermissionCreate::class)->middleware('permission:create_permission')->name('admin.account.permission.create');
    Route::get('/admin/permission/{permission}/edit', PermissionEdit::class)->middleware('permission:edit_permission')->name('admin.account.permission.edit');
});

// Log Aktivitas (error & auth) — untuk maintenance.
Route::middleware('permission:view_activity_log')->group(function () {
    Route::get('/admin/activity-log', ActivityLogList::class)->name('admin.account.activity-log');
});

// Karyawan
Route::middleware('permission:view_karyawan')->group(function () {
    Route::get('/admin/karyawan', KaryawanList::class)->name('admin.karyawan.index');
    Route::get('/admin/karyawan/create', KaryawanCreate::class)->middleware('permission:create_karyawan')->name('admin.karyawan.create');
    Route::get('/admin/karyawan/{user}/edit', KaryawanEdit::class)->middleware('permission:edit_karyawan')->name('admin.karyawan.edit');
});

// Presensi
Route::middleware('permission:view_presensi')->group(function () {
    Route::get('/admin/presensi', \App\Livewire\Pages\Admin\Presensi\PresensiIndex::class)->name('admin.presensi.index');
});
Route::middleware('permission:view_all_presensi')->group(function () {
    Route::get('/admin/presensi/rekap', \App\Livewire\Pages\Admin\Presensi\PresensiRekap::class)->name('admin.presensi.rekap');
});
Route::middleware('permission:manage_presensi_setting')->group(function () {
    Route::get('/admin/presensi/pengaturan', \App\Livewire\Pages\Admin\Presensi\PresensiSetting::class)->name('admin.presensi.pengaturan');
});

// Data Akun
Route::middleware('permission:view_dataakun')->group(function () {
    Route::get('/admin/DataAkun', DataAkunList::class)->name('admin.DataAkun.index');
    Route::get('/admin/DataAkun/create', DataAkunCreate::class)->middleware('permission:create_dataakun')->name('admin.DataAkun.create');
    Route::get('/admin/DataAkun/{DataAkun}', DataAkunEdit::class)->name('admin.DataAkun.show');
    Route::get('/admin/DataAkun/{dataAkun}/edit', DataAkunEdit::class)->middleware('permission:edit_dataakun')->name('admin.DataAkun.edit');
});

// Data Product
Route::middleware('permission:view_product')->group(function () {
    Route::get('/admin/product', ProductList::class)->name('admin.product.index');
    Route::get('/admin/product/create', ProductCreate::class)->middleware('permission:create_product')->name('admin.product.create');
    Route::get('/admin/product/{product}/edit', ProductEdit::class)->middleware('permission:edit_product')->name('admin.product.edit');
});

// Ebook Bonus
Route::middleware('permission:view_ebook')->group(function () {
    Route::get('/admin/ebook', \App\Livewire\Pages\Admin\Ebook\EbookList::class)->name('admin.ebook.index');
    Route::get('/admin/ebook/{ebook}/download', [\App\Http\Controllers\EbookController::class, 'download'])->name('admin.ebook.download');
    Route::get('/admin/ebook/create', \App\Livewire\Pages\Admin\Ebook\EbookCreate::class)->middleware('permission:create_ebook')->name('admin.ebook.create');
    Route::get('/admin/ebook/{ebook}/edit', \App\Livewire\Pages\Admin\Ebook\EbookEdit::class)->middleware('permission:edit_ebook')->name('admin.ebook.edit');
});

// Data Banners
Route::middleware('permission:view_banners')->group(function () {
    Route::get('/admin/DataBanners', BannersList::class)->name('admin.Banners.index');
    Route::get('/admin/DataBanners/create', BannersCreate::class)->middleware('permission:create_banners')->name('admin.Banners.create');
    Route::get('/admin/DataBanners/{Banners}', BannersEdit::class)->name('admin.Banners.show');
    Route::get('/admin/DataBanners/{Banners}/edit', BannersEdit::class)->middleware('permission:edit_banners')->name('admin.Banners.edit');
});

// Data Testimoni
Route::middleware('permission:view_testimoni')->group(function () {
    Route::get('/admin/DataTestimoni', TestimoniList::class)->name('admin.testimoni.index');
    Route::get('/admin/DataTestimoni/create', TestimoniCreate::class)->middleware('permission:create_testimoni')->name('admin.testimoni.create');
    Route::get('/admin/DataTestimoni/{testimoni}', TestimoniEdit::class)->name('admin.testimoni.show');
    Route::get('/admin/DataTestimoni/{testimoni}/edit', TestimoniEdit::class)->middleware('permission:edit_testimoni')->name('admin.testimoni.edit');
});

// Moderasi ulasan produk (izin tersendiri, tidak bergantung pada testimoni).
Route::middleware('permission:view_productreview')->group(function () {
    Route::get('/admin/ulasan-produk', \App\Livewire\Pages\Admin\ProductReview\ReviewModeration::class)->name('admin.reviews.index');
});

// Blog / Artikel
Route::middleware('permission:view_blog')->group(function () {
    Route::get('/admin/blog', \App\Livewire\Pages\Admin\Blog\BlogList::class)->name('admin.blog.index');
    Route::get('/admin/blog/kategori', \App\Livewire\Pages\Admin\Blog\CategoryList::class)->name('admin.blog.categories');
    Route::get('/admin/blog/create', \App\Livewire\Pages\Admin\Blog\BlogCreate::class)->middleware('permission:create_blog')->name('admin.blog.create');
    Route::get('/admin/blog/{post}/edit', \App\Livewire\Pages\Admin\Blog\BlogEdit::class)->middleware('permission:edit_blog')->name('admin.blog.edit');
});

// Data Product Bundling
Route::middleware('permission:view_bundlings')->group(function () {
    Route::get('/admin/DataBundlings', ProductBundlingsList::class)->name('admin.Bundlings.index');
    Route::get('/admin/DataBundlings/create', ProductBundlingsCreate::class)->middleware('permission:create_bundlings')->name('admin.Bundlings.create');
    Route::get('/admin/DataBundlings/{ProductBundlings}', ProductBundlingsEdit::class)->name('admin.Bundlings.show');
    Route::get('/admin/DataBundlings/{ProductBundlings}/edit', ProductBundlingsEdit::class)->middleware('permission:edit_bundlings')->name('admin.Bundlings.edit');
});

// Cashflow
Route::middleware('permission:view_cashflow')->group(function () {
    Route::get('/admin/cashflow', CashFlowList::class)->name('admin.cashflow.index');
    Route::get('/admin/cashflow/{cashflow}', CashFlowDetail::class)->name('admin.cashflow.detail');
});

// Data Spending
Route::middleware('permission:view_spending')->group(function () {
    Route::get('/admin/spending', SpendingList::class)->name('admin.spending.index');
    // Lampiran nota/faktur: berkas sensitif, hanya lewat route ber-izin.
    Route::get('/admin/spending/{spending}/lampiran/{index?}', [\App\Http\Controllers\BerkasPrivatController::class, 'lampiranSpending'])
        ->name('admin.spending.lampiran');
    Route::get('/admin/spending/create', SpendingCreate::class)->middleware('permission:create_spending')->name('admin.spending.create');
    Route::get('/admin/spending/{id}/edit', SpendingEdit::class)->middleware('permission:edit_spending')->name('admin.spending.edit');
});

// Data Modal
Route::middleware('permission:view_modal')->group(function () {
    Route::get('/admin/modal', \App\Livewire\Pages\Admin\Modal\ModalList::class)->name('admin.modal.index');
});

// Data Pemasukan Lainnya
Route::middleware('permission:view_pemasukan')->group(function () {
    Route::get('/admin/pemasukan', \App\Livewire\Pages\Admin\Pemasukan\PemasukanList::class)->name('admin.pemasukan.index');
});

// Harga Modal Akun (private)
Route::middleware('permission:view_harga_modal')->group(function () {
    Route::get('/admin/harga-modal', \App\Livewire\Pages\Admin\HargaModal\HargaModalList::class)->name('admin.hargamodal.index');
});

// Data Loan & Pengembalian (satu modul Peminjaman)
Route::middleware('permission:view_loan')->group(function () {
    Route::get('/admin/loan', LoanList::class)->name('admin.loan.index');
    Route::get('/admin/loan/create', LoanCreate::class)->middleware('permission:create_loan')->name('admin.loan.create');
    Route::get('/admin/loan/{id}/edit', LoanEdit::class)->middleware('permission:edit_loan')->name('admin.loan.edit');

    Route::get('/admin/pengembalian', PengembalianList::class)->name('admin.pengembalian.index');
    Route::get('/admin/pengembalian/create', PengembalianCreate::class)->middleware('permission:create_loan')->name('admin.pengembalian.create');
    Route::get('/admin/pengembalian/{id}/edit', PengembalianEdit::class)->middleware('permission:edit_loan')->name('admin.pengembalian.edit');
});

// Data Gaji Karyawan
Route::middleware('permission:view_gajikaryawan')->group(function () {
    Route::get('/admin/GajiKaryawan', GajiKaryawansList::class)->name('admin.gajikaryawan.index');
    Route::get('/admin/GajiKaryawan/create', GajiKaryawansCreate::class)->middleware('permission:create_gajikaryawan')->name('admin.gajikaryawan.create');
    Route::get('/admin/GajiKaryawan/{gajikaryawan}/edit', GajiKaryawansEdit::class)->middleware('permission:edit_gajikaryawan')->name('admin.gajikaryawan.edit');
});

// Penyelesaian Task (admin): kelola task + pool bonus per periode
Route::middleware('permission:manage_task')->group(function () {
    Route::get('/admin/penyelesaian-task', \App\Livewire\Pages\Admin\PenyelesaianTask\PenyelesaianTaskList::class)->name('admin.penyelesaian-task.index');
});

// Task Saya (semua karyawan): lihat & kerjakan task miliknya
Route::middleware('permission:view_task')->group(function () {
    Route::get('/admin/task-saya', \App\Livewire\Pages\Admin\Task\TaskSayaList::class)->name('admin.task-saya.index');
});

// Lowongan Pekerjaan
Route::middleware('permission:view_lowongan')->group(function () {
    Route::get('/admin/lowongan', LowonganPekerjaanList::class)->name('admin.lowongan.index');
    Route::get('/admin/lowongan/create', LowonganPekerjaanCreate::class)->middleware('permission:create_lowongan')->name('admin.lowongan.create');
    Route::get('/admin/lowongan/{lowongan}/edit', LowonganPekerjaanEdit::class)->middleware('permission:edit_lowongan')->name('admin.lowongan.edit');
});

// Pelamar Kerja
Route::middleware('permission:view_pelamar')->group(function () {
    Route::get('/admin/pelamar', PelamarKerjaList::class)->name('admin.pelamar.index');
    Route::get('/admin/pelamar/{id}', PelamarKerjaDetail::class)->name('admin.pelamar.detail');
    // CV & surat lamaran memuat data pribadi — wajib lewat route ber-izin.
    Route::get('/admin/pelamar/{pelamar}/cv', [\App\Http\Controllers\BerkasPrivatController::class, 'cvPelamar'])
        ->name('admin.pelamar.cv');
    Route::get('/admin/pelamar/{pelamar}/surat', [\App\Http\Controllers\BerkasPrivatController::class, 'suratPelamar'])
        ->name('admin.pelamar.surat');
});

// Promo
Route::middleware('permission:view_promo')->group(function () {
    Route::get('/admin/promo', PromoList::class)->name('admin.promo.index');
    Route::get('/admin/promo/create', PromoCreate::class)->middleware('permission:create_promo')->name('admin.promo.create');
    Route::get('/admin/promo/{promo}/edit', PromoEdit::class)->middleware('permission:edit_promo')->name('admin.promo.edit');
});

// Pesan Masuk
Route::middleware('permission:view_message')->group(function () {
    Route::get('/admin/message', MessageList::class)->name('admin.message.index');
    Route::get('/admin/message/{message}', MessageDetail::class)->name('admin.message.detail');
});

/*
|--------------------------------------------------------------------------
| Orcha Journey
|--------------------------------------------------------------------------
|
| Halaman ini menggambar data milik aplikasi Orcha Journey — basis datanya
| tetap di sana, lemon hanya memanggil API-nya. Tujuannya supaya admin cukup
| satu akun dan tidak berpindah aplikasi.
|
| Penjaganya permission 'akses_orcha'. Tombol yang disembunyikan di sidebar
| bukan pengaman; penjagaan sebenarnya ada di sini.
|
*/
Route::middleware('permission:akses_orcha')->group(function () {
    Route::get('/admin/orcha/dashboard', OrchaDashboard::class)->name('admin.orcha.dashboard');
    Route::get('/admin/orcha/pendaftaran', OrchaPendaftaranList::class)->name('admin.orcha.pendaftaran');
    Route::get('/admin/orcha/pendaftaran-manifes', [OrchaEksporController::class, 'manifesDaftar'])->name('admin.orcha.pendaftaran.manifes');
    Route::get('/admin/orcha/pendaftaran/{pendaftaran}', OrchaPendaftaranDetail::class)->name('admin.orcha.pendaftaran.detail');
    Route::get('/admin/orcha/pendaftaran/{pendaftaran}/ekspor/excel', [OrchaEksporController::class, 'excel'])->name('admin.orcha.pendaftaran.excel');
    Route::get('/admin/orcha/pendaftaran/{pendaftaran}/ekspor/pdf', [OrchaEksporController::class, 'pdf'])->name('admin.orcha.pendaftaran.pdf');
    Route::get('/admin/orcha/pendaftaran/{pendaftaran}/kwitansi', [OrchaEksporController::class, 'kwitansi'])->name('admin.orcha.pendaftaran.kwitansi');
    Route::get('/admin/orcha/penyewaan', OrchaPenyewaanList::class)->name('admin.orcha.penyewaan');
    Route::get('/admin/orcha/penyewaan/{penyewaan}', OrchaPenyewaanDetail::class)->name('admin.orcha.penyewaan.detail');
    Route::get('/admin/orcha/penyewaan/{penyewaan}/kwitansi', [OrchaEksporController::class, 'kwitansiSewa'])->name('admin.orcha.penyewaan.kwitansi');
    Route::get('/admin/orcha/pembayaran', OrchaPembayaranList::class)->name('admin.orcha.pembayaran');
    Route::get('/admin/orcha/pembatalan', OrchaPembatalanList::class)->name('admin.orcha.pembatalan');
    Route::get('/admin/orcha/pembatalan/{id}', OrchaPembatalanDetail::class)->name('admin.orcha.pembatalan.detail');
    Route::get('/admin/orcha/pesan', OrchaPesanList::class)->name('admin.orcha.pesan');
    Route::get('/admin/orcha/pesan/{id}', OrchaPesanDetail::class)->name('admin.orcha.pesan.detail');
    Route::get('/admin/orcha/paket-wisata', OrchaPaketList::class)->name('admin.orcha.paket');
    Route::get('/admin/orcha/paket-wisata/tambah', OrchaPaketForm::class)->name('admin.orcha.paket.tambah');
    Route::get('/admin/orcha/paket-wisata/{paket}/ubah', OrchaPaketForm::class)->name('admin.orcha.paket.ubah');

    Route::get('/admin/orcha/armada', OrchaArmadaList::class)->name('admin.orcha.armada');
    Route::get('/admin/orcha/armada/tambah', OrchaArmadaForm::class)->name('admin.orcha.armada.tambah');
    Route::get('/admin/orcha/armada/{kendaraan}/ubah', OrchaArmadaForm::class)->name('admin.orcha.armada.ubah');

    Route::get('/admin/orcha/destinasi', OrchaEtalaseList::class)->name('admin.orcha.destinasi');
    Route::get('/admin/orcha/testimoni', OrchaEtalaseList::class)->defaults('jenis', 'testimoni')->name('admin.orcha.testimoni');
    Route::get('/admin/orcha/partner', OrchaEtalaseList::class)->defaults('jenis', 'partner')->name('admin.orcha.partner');
});

require __DIR__.'/auth.php';
