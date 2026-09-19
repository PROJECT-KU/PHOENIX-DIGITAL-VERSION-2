<?php

use App\Livewire\Pages\Admin\ProductReview\ReviewModeration;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\ProductReview;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Layar Moderasi Ulasan Produk: kartu status (sekaligus tab), saringan,
 * jendela detail, aksi massal, dan unduhan.
 */
function adminUlasan(array $izin = ['view_productreview', 'edit_productreview', 'delete_productreview']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-ul-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function produkUji(string $nama = 'Grammarly Premium'): Product
{
    return Product::create(['nama_akun' => $nama]);
}

function ulasan(array $isian = []): ProductReview
{
    return ProductReview::create(array_merge([
        'product_id' => $isian['product_id'] ?? produkUji()->id,
        'jenis' => 'produk',
        'nama' => 'Pengulas '.Str::random(4),
        'rating' => 5,
        'ulasan' => 'Akunnya cepat sekali dikirim, terima kasih.',
        'status' => 'pending',
    ], $isian));
}

it('kartu status menghitung dan menyaring tiap tab moderasi', function () {
    $this->actingAs(adminUlasan());
    ulasan(['nama' => 'Masih Ditinjau']);
    ulasan(['nama' => 'Sudah Tampil', 'status' => 'approved']);
    ulasan(['nama' => 'Disembunyikan Admin', 'status' => 'hidden']);

    $t = Livewire::test(ReviewModeration::class);
    expect($t->viewData('tabCounts'))->toMatchArray(['all' => 3, 'pending' => 1, 'approved' => 1, 'hidden' => 1]);

    $t->assertSet('filter', 'pending')->assertSee('Masih Ditinjau')->assertDontSee('Sudah Tampil');
    $t->call('setFilter', 'hidden')->assertSee('Disembunyikan Admin')->assertDontSee('Masih Ditinjau');
    $t->call('setFilter', 'all')->assertSee('Sudah Tampil')->assertSee('Disembunyikan Admin');
    $t->call('setFilter', 'ngawur')->assertSet('filter', 'pending');
});

it('rata-rata bintang hanya dari ulasan yang disetujui', function () {
    $this->actingAs(adminUlasan());
    ulasan(['status' => 'approved', 'rating' => 5]);
    ulasan(['status' => 'approved', 'rating' => 4]);
    ulasan(['status' => 'pending', 'rating' => 1]);
    ulasan(['status' => 'hidden', 'rating' => 1]);

    expect(Livewire::test(ReviewModeration::class)->viewData('rataRating'))->toBe(4.5);
});

it('menyaring menurut bintang, jenis, dan rentang tanggal', function () {
    $this->actingAs(adminUlasan());
    $paket = ProductBundlings::create(['nama_paket' => 'Combo Skripsi', 'harga_bundling' => 1000]);
    // Nama pengulas sengaja khas: "Ulasan Produk" akan cocok dengan judul
    // halaman ("Moderasi Ulasan Produk") dan membuat assertDontSee palsu.
    ulasan(['nama' => 'Pengulas Satuan', 'rating' => 5]);
    ulasan(['nama' => 'Pengulas Bundel', 'rating' => 3, 'jenis' => 'paket', 'product_id' => $paket->id]);

    $t = Livewire::test(ReviewModeration::class);
    $t->set('fRating', '3')->assertSee('Pengulas Bundel')->assertDontSee('Pengulas Satuan');
    $t->set('fRating', '')->set('fJenis', 'produk')->assertSee('Pengulas Satuan')->assertDontSee('Pengulas Bundel');
    $t->set('fJenis', 'paket')->assertSee('Pengulas Bundel')->assertDontSee('Pengulas Satuan');

    $t->set('fJenis', '')->set('fDari', now()->addDay()->toDateString())
        ->assertDontSee('Pengulas Satuan')->assertDontSee('Pengulas Bundel');

    $t->call('resetSaring')->assertSet('fDari', '')->assertSee('Pengulas Satuan');
});

it('pencarian menjangkau nama produk, nama pengulas, dan isi ulasan', function () {
    $this->actingAs(adminUlasan());
    ulasan(['nama' => 'Budi', 'ulasan' => 'Pengiriman kilat sekali.', 'product_id' => produkUji('Canva Pro')->id]);
    ulasan(['nama' => 'Sita', 'ulasan' => 'Harganya ramah di kantong.', 'product_id' => produkUji('Scopus Access')->id]);

    $t = Livewire::test(ReviewModeration::class);
    $t->set('search', 'Canva')->assertSee('Budi')->assertDontSee('Sita');
    $t->set('search', 'kantong')->assertSee('Sita')->assertDontSee('Budi');
    $t->set('search', 'Budi')->assertSee('Pengiriman kilat')->assertDontSee('Harganya ramah');
});

it('kata pencarian disorot di daftar', function () {
    $this->actingAs(adminUlasan());
    ulasan(['ulasan' => 'Akun Grammarly dikirim cepat.']);

    Livewire::test(ReviewModeration::class)->set('search', 'Grammarly')
        ->assertSee('<mark class="sorot-kata">Grammarly</mark>', false);
});

it('pengurutan bintang dan tanggal bekerja', function () {
    $this->actingAs(adminUlasan());
    $rendah = ulasan(['rating' => 2]);
    $tinggi = ulasan(['rating' => 5]);

    $t = Livewire::test(ReviewModeration::class);
    expect($t->set('urut', 'tinggi')->viewData('reviews')->first()->id)->toBe($tinggi->id);
    expect($t->set('urut', 'rendah')->viewData('reviews')->first()->id)->toBe($rendah->id);
    $t->set('urut', 'ngawur')->assertSet('urut', 'baru');
});

it('menyetujui, menyembunyikan, dan menghapus satu ulasan', function () {
    $this->actingAs(adminUlasan());
    $u = ulasan();

    Livewire::test(ReviewModeration::class)->call('approve', $u->id);
    expect($u->fresh()->status)->toBe('approved');

    Livewire::test(ReviewModeration::class)->call('reject', $u->id);
    expect($u->fresh()->status)->toBe('hidden');

    Livewire::test(ReviewModeration::class)->call('remove', $u->id);
    expect(ProductReview::find($u->id))->toBeNull();
});

it('jendela detail dibuka, bisa pindah tetangga, dan tertutup saat ulasannya dihapus', function () {
    $this->actingAs(adminUlasan());
    $baru = ulasan(['nama' => 'Ulasan Baru']);
    $lama = ulasan(['nama' => 'Ulasan Lama']);
    $lama->forceFill(['created_at' => now()->subDay()])->saveQuietly();

    $t = Livewire::test(ReviewModeration::class)
        ->call('lihat', $baru->id)
        ->assertSet('lihatId', (string) $baru->id)
        ->assertSee('Detail Ulasan');

    $t->call('detailTetangga', 1)->assertSet('lihatId', (string) $lama->id)
        ->call('detailTetangga', -1)->assertSet('lihatId', (string) $baru->id)
        // Di ujung daftar jendelanya tetap terbuka pada ulasan yang sama.
        ->call('detailTetangga', -1)->assertSet('lihatId', (string) $baru->id);

    $t->call('remove', $baru->id)->assertSet('lihatId', null);
});

it('aksi massal menyetujui, menyembunyikan, dan menghapus yang dicentang', function () {
    $this->actingAs(adminUlasan());
    $a = ulasan();
    $b = ulasan();

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $a->id, (string) $b->id])
        ->call('setujuiTerpilih')->assertSet('pilih', []);
    expect([$a->fresh()->status, $b->fresh()->status])->toBe(['approved', 'approved']);

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $a->id])->call('sembunyikanTerpilih');
    expect($a->fresh()->status)->toBe('hidden');

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $a->id, (string) $b->id])->call('hapusTerpilih');
    expect(ProductReview::count())->toBe(0);
});

it('pilih semua di halaman mencentang lalu melepas kembali', function () {
    $this->actingAs(adminUlasan());
    $a = ulasan();
    $b = ulasan();
    $ids = [(string) $a->id, (string) $b->id];

    Livewire::test(ReviewModeration::class)
        ->call('pilihHalaman', $ids)->assertSet('pilih', $ids)
        ->call('pilihHalaman', $ids)->assertSet('pilih', []);
});

it('chip saringan menampilkan yang aktif dan bisa dilepas satu-satu', function () {
    $this->actingAs(adminUlasan());

    $t = Livewire::test(ReviewModeration::class)->set('fRating', '5')->set('fJenis', 'paket');
    expect(collect($t->instance()->chipSaring)->pluck('nama')->all())->toBe(['fRating', 'fJenis']);

    $t->call('lepasSaring', 'fRating')->assertSet('fRating', '')->assertSet('fJenis', 'paket');
    // Nama properti yang tidak dikenal diabaikan, bukan menimpa apa pun.
    $t->call('lepasSaring', 'perHalaman')->assertSet('perHalaman', 12);
});

it('unduhan mengikuti centang bila ada, dan butuh izin lihat', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-19 12:00:00');
    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->actingAs(adminUlasan());

    $dipilih = ulasan(['nama' => 'Dicentang']);
    ulasan(['nama' => 'Tidak Dicentang']);

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $dipilih->id])->call('unduhExcel');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('ulasan-produk-20260919-120000.xlsx', function (\App\Exports\UlasanProdukExport $ekspor) {
        $nama = $ekspor->view()->getData()['ulasan']->pluck('nama');

        return $nama->contains('Dicentang') && ! $nama->contains('Tidak Dicentang');
    });

    Livewire::test(ReviewModeration::class)->call('unduhPdf')->assertFileDownloaded();

    // Tanpa izin lihat, unduhan ditolak.
    $this->actingAs(\App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::create(['name' => 'uji-ul-nihil-'.Str::random(4), 'description' => 'uji'])->id,
        'status' => 'active',
    ]));
    Livewire::test(ReviewModeration::class)->call('unduhExcel')->assertForbidden();
    Livewire::test(ReviewModeration::class)->call('unduhPdf')->assertForbidden();

    \Illuminate\Support\Carbon::setTestNow();
});

it('penanda kecurigaan menandai tautan, ulasan pendek, dan produk terhapus', function () {
    $produk = produkUji('Produk Sementara');
    $bertautan = ulasan(['ulasan' => 'Kunjungi toko-lain.com sekarang juga ya']);
    expect($bertautan->kecurigaan())->toContain('Mengandung tautan');

    expect(ulasan(['ulasan' => 'Bagus'])->kecurigaan())->toContain('Terlalu pendek');

    $yatim = ulasan(['product_id' => $produk->id]);
    $produk->delete();
    expect($yatim->fresh()->load('product')->kecurigaan())->toContain('Produk sudah terhapus');

    expect(ulasan(['ulasan' => 'Pelayanannya cepat dan ramah sekali.'])->kecurigaan())->toBe([]);
});

it('tautan publik mengarah ke halaman produk atau paket, dan kosong bila targetnya hilang', function () {
    $produk = produkUji('Canva Pro');
    $paket = ProductBundlings::create(['nama_paket' => 'Combo Riset', 'harga_bundling' => 1000]);

    $uProduk = ulasan(['product_id' => $produk->id]);
    $uPaket = ulasan(['jenis' => 'paket', 'product_id' => $paket->id]);

    expect($uProduk->tautanPublik())->toBe(route('shop.detail-product', $produk->id))
        ->and($uPaket->tautanPublik())->toBe(route('bundling.detail', $paket->id));

    $produk->delete();
    expect($uProduk->fresh()->load('product')->tautanPublik())->toBeNull();
});

it('nama target dan lencana status sesuai jenisnya', function () {
    $paket = ProductBundlings::create(['nama_paket' => 'Combo Riset', 'harga_bundling' => 1000]);

    expect(ulasan(['product_id' => produkUji('Canva Pro')->id])->namaTarget())->toBe('Canva Pro')
        ->and(ulasan(['jenis' => 'paket', 'product_id' => $paket->id])->namaTarget())->toBe('Combo Riset')
        ->and(ulasan(['status' => 'approved'])->tampilanStatus()[0])->toBe('Disetujui')
        ->and(ulasan(['status' => 'hidden'])->tampilanStatus()[0])->toBe('Disembunyikan')
        ->and(ulasan()->tampilanStatus()[0])->toBe('Menunggu');
});

// ===================== Izin terpisah =====================

it('izin lihat saja tidak boleh memoderasi maupun menghapus', function () {
    $u = ulasan();
    $this->actingAs(adminUlasan(['view_productreview']));

    $t = Livewire::test(ReviewModeration::class);
    $t->call('approve', $u->id);
    $t->call('reject', $u->id);
    $t->call('remove', $u->id);

    expect($u->fresh()->status)->toBe('pending')
        ->and($u->fresh()->trashed())->toBeFalse();

    // Aksi massal juga tertutup.
    $t->set('pilih', [(string) $u->id])->call('setujuiTerpilih');
    expect($u->fresh()->status)->toBe('pending');
});

it('izin moderasi tanpa izin hapus boleh setujui tapi tidak boleh mengarsipkan', function () {
    $u = ulasan();
    $this->actingAs(adminUlasan(['view_productreview', 'edit_productreview']));

    Livewire::test(ReviewModeration::class)->call('approve', $u->id);
    expect($u->fresh()->status)->toBe('approved');

    Livewire::test(ReviewModeration::class)->call('remove', $u->id);
    expect($u->fresh()->trashed())->toBeFalse();
});

// ===================== Arsip & urungkan =====================

it('hapus mengarsipkan dulu, bisa dipulihkan, lalu dibuang permanen', function () {
    $this->actingAs(adminUlasan());
    $u = ulasan(['nama' => 'Mau Diarsip']);

    Livewire::test(ReviewModeration::class)->call('remove', $u->id);
    expect(ProductReview::find($u->id))->toBeNull()
        ->and(ProductReview::onlyTrashed()->whereKey($u->id)->exists())->toBeTrue();

    // Arsip mengabaikan tab status — kalau tidak, isinya tampak kosong.
    Livewire::test(ReviewModeration::class)->set('arsip', true)->assertSee('Mau Diarsip');

    Livewire::test(ReviewModeration::class)->call('pulihkan', $u->id);
    expect(ProductReview::find($u->id))->not->toBeNull();

    Livewire::test(ReviewModeration::class)->call('remove', $u->id);
    Livewire::test(ReviewModeration::class)->call('buangPermanen', $u->id);
    expect(ProductReview::withTrashed()->whereKey($u->id)->exists())->toBeFalse();
});

it('memilih tab status selalu keluar dari arsip', function () {
    $this->actingAs(adminUlasan());

    Livewire::test(ReviewModeration::class)
        ->set('arsip', true)
        ->call('setFilter', 'approved')
        ->assertSet('arsip', false);
});

it('keputusan setujui & sembunyikan bisa diurungkan', function () {
    $this->actingAs(adminUlasan());
    $u = ulasan();

    $t = Livewire::test(ReviewModeration::class)->call('approve', $u->id);
    expect($u->fresh()->status)->toBe('approved')
        ->and($t->get('urungkan')['status'])->toBe('pending');

    $t->call('urungkanTerakhir')->assertSet('urungkan', null);
    expect($u->fresh()->status)->toBe('pending')
        // Kembali menunggu berarti belum pernah ditinjau.
        ->and($u->fresh()->ditinjau_at)->toBeNull();

    Livewire::test(ReviewModeration::class)->call('reject', $u->id)->call('urungkanTerakhir');
    expect($u->fresh()->status)->toBe('pending');
});

it('setiap keputusan mencatat siapa yang meninjau dan kapan', function () {
    $admin = adminUlasan();
    $this->actingAs($admin);
    $u = ulasan();

    Livewire::test(ReviewModeration::class)->call('approve', $u->id);

    expect($u->fresh()->ditinjau_oleh)->toEqual($admin->id)
        ->and($u->fresh()->ditinjau_at)->not->toBeNull();
});

it('arsip & pulihkan massal bekerja', function () {
    $this->actingAs(adminUlasan());
    $a = ulasan();
    $b = ulasan();

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $a->id, (string) $b->id])->call('hapusTerpilih');
    expect(ProductReview::onlyTrashed()->count())->toBe(2);

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $a->id])->call('pulihkanTerpilih');
    expect(ProductReview::find($a->id))->not->toBeNull();

    Livewire::test(ReviewModeration::class)->set('pilih', [(string) $b->id])->call('buangTerpilih');
    expect(ProductReview::withTrashed()->whereKey($b->id)->exists())->toBeFalse();
});

// ===================== Pembeli asli =====================

it('ulasan ditautkan ke pelanggan hanya bila nomornya benar-benar membeli produk itu', function () {
    $produk = produkUji('Canva Pro');
    $lain = produkUji('Scopus Access');

    $pelanggan = \App\Models\Customer::create(['nama' => 'Pembeli Setia', 'no_hp' => '081298765432']);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-UL-1', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => $pelanggan->id,
    ]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Canva Pro',
        'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1,
    ]);

    expect(ProductReview::cariPembeli('081298765432', 'produk', $produk->id)?->id)->toBe($pelanggan->id)
        // Produk lain: nomornya benar, tapi tidak pernah membelinya.
        ->and(ProductReview::cariPembeli('081298765432', 'produk', $lain->id))->toBeNull()
        // Tanpa nomor, tidak ada yang ditautkan.
        ->and(ProductReview::cariPembeli('', 'produk', $produk->id))->toBeNull();
});

it('kiriman ulasan menyimpan nomor & tautan pembeli, dan tanpa nomor tetap diterima', function () {
    $produk = produkUji('Canva Pro');
    $pelanggan = \App\Models\Customer::create(['nama' => 'Pembeli Setia', 'no_hp' => '081277001100']);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-UL-2', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => $pelanggan->id,
    ]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => 'Canva Pro',
        'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 1, 'quantity' => 1, 'subtotal' => 1,
    ]);

    Livewire::test(\App\Livewire\Components\ProductReviews::class, ['productId' => $produk->id])
        ->set('nama', 'Pembeli Setia')
        ->set('no_hp', '081277001100')
        ->set('ulasan', 'Akunnya aktif cepat sekali, terima kasih.')
        ->call('submit')
        ->assertSet('submitted', true);

    $ulasan = ProductReview::where('nama', 'Pembeli Setia')->first();
    expect($ulasan->customer_id)->toBe($pelanggan->id)
        ->and($ulasan->pembeliAsli())->toBeTrue()
        ->and($ulasan->status)->toBe('pending');

    // Tanpa nomor: tetap diterima, hanya tanpa label.
    Livewire::test(\App\Livewire\Components\ProductReviews::class, ['productId' => $produk->id])
        ->set('nama', 'Tanpa Nomor')
        ->set('ulasan', 'Bagus dan cepat sekali pelayanannya.')
        ->call('submit')
        ->assertSet('submitted', true);

    expect(ProductReview::where('nama', 'Tanpa Nomor')->first()->pembeliAsli())->toBeFalse();
});

it('nomor pengulas tidak ikut saat ulasan diserialisasi', function () {
    $u = ulasan(['no_hp' => '081200001111']);

    expect($u->toArray())->not->toHaveKey('no_hp');
});

// ===================== Data terstruktur produk =====================

it('bintang produk ikut ke data terstruktur hanya bila ada ulasan disetujui', function () {
    $produk = produkUji('Canva Pro');

    expect(\App\Support\RingkasanUlasan::jsonLd('produk', $produk->id))->toBe([]);

    ulasan(['product_id' => $produk->id, 'status' => 'approved', 'rating' => 5]);
    ulasan(['product_id' => $produk->id, 'status' => 'approved', 'rating' => 4]);
    // Yang menunggu tidak ikut dihitung.
    ulasan(['product_id' => $produk->id, 'status' => 'pending', 'rating' => 1]);

    $jsonLd = \App\Support\RingkasanUlasan::jsonLd('produk', $produk->id);
    expect($jsonLd['aggregateRating']['ratingValue'])->toBe(4.5)
        ->and($jsonLd['aggregateRating']['reviewCount'])->toBe(2);
});

it('halaman produk membawa aggregateRating di JSON-LD', function () {
    $produk = produkUji('Canva Pro');
    ulasan(['product_id' => $produk->id, 'status' => 'approved', 'rating' => 5]);

    $this->get(route('shop.detail-product', $produk->id))->assertOk();

    $jsonLd = json_decode(view()->shared('seoJsonLd'), true);
    expect($jsonLd['@type'])->toBe('Product')
        ->and($jsonLd['aggregateRating']['reviewCount'])->toBe(1);
});

// ===================== Ringkasan per produk =====================

it('ringkasan per produk menaikkan bintang terendah lebih dulu', function () {
    $this->actingAs(adminUlasan());
    $bagus = produkUji('Produk Bagus');
    $buruk = produkUji('Produk Buruk');

    ulasan(['product_id' => $bagus->id, 'status' => 'approved', 'rating' => 5]);
    ulasan(['product_id' => $buruk->id, 'status' => 'approved', 'rating' => 2]);
    // Yang menunggu tidak masuk rekap.
    ulasan(['product_id' => $bagus->id, 'status' => 'pending', 'rating' => 1]);

    $rekap = Livewire::test(ReviewModeration::class)->set('lihatRingkasan', true)->viewData('ringkasanProduk');

    expect($rekap->first()['nama'])->toBe('Produk Buruk')
        ->and($rekap->first()['rata'])->toBe(2.0)
        ->and($rekap->firstWhere('nama', 'Produk Bagus')['jumlah'])->toBe(1);
});
