<?php

use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\Promo;
use App\Services\PromoService;

/*
 | Penolongnya diawali "pps" (Promo Paket Saja). Pest menaruh fungsi penolong
 | di ruang nama GLOBAL, jadi nama sepolos produkUji() bertabrakan dengan
 | penolong bernama sama di berkas uji lain — dan tabrakannya FATAL ERROR yang
 | hanya muncul saat seluruh rangkaian dijalankan, bukan saat berkas ini
 | dijalankan sendirian.
 |
 | Promo yang HANYA melampirkan paket bundling tidak boleh mendiskon produk
 | satuan — tidak di keranjang, dan tidak pula di lencana kartu etalase.
 |
 | Keranjang sudah benar sejak paket bisa dilampirkan ke promo. Kartunya
 | tidak: getProductPromos() memakai aturan lama "tidak ada produk = berlaku
 | untuk semua" tanpa menengok daftar paketnya. Akibatnya seluruh produk di
 | /shop memakai lencana "s.d. 25%" sementara potongannya NOL saat dibeli —
 | pembeli melihat diskon, menaruhnya di keranjang, dan harganya tidak
 | berubah. Tidak ada galat apa pun yang memberi tahu bahwa itu terjadi.
 */

function ppsPromo(array $ubah = []): Promo
{
    return Promo::create(array_merge([
        'nama_promo' => 'Gebyar 12.12',
        'tipe_promo' => 'flash_sale',
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => 25,
        'diskon_non_member_persen' => 25,
        'is_active' => true,
        'mulai_promo' => now()->subDay(),
        'selesai_promo' => now()->addDay(),
        'prioritas' => 50,
    ], $ubah));
}

function ppsProduk(): Product
{
    return Product::create(['nama_akun' => 'Canva Premium', 'harga_perbulan' => 20000]);
}

function ppsPaket(Product $produk): ProductBundlings
{
    return ProductBundlings::create([
        'nama_paket' => 'Paket Riset Lengkap',
        'harga_awal' => 'Rp 100.000',
        'harga_bundling' => 'Rp 80.000',
        'status' => 'active',
        'product_1' => $produk->id,
        'durations' => ['product_1' => ['value' => 1, 'type' => 'bulan']],
    ]);
}

function ppsKeranjang(Product $produk): array
{
    return [[
        'product_id' => $produk->id,
        'type' => 'product',
        'quantity' => 1,
        'price' => 20000,
        'subtotal' => 20000,
        'duration_type' => 'bulan',
        'duration_value' => 1,
    ]];
}

test('promo yang hanya melampirkan paket tidak memberi lencana pada produk satuan', function () {
    $produk = ppsProduk();
    $promo = ppsPromo();
    $promo->bundlings()->attach(ppsPaket($produk)->id);

    $layanan = app(PromoService::class);

    expect($layanan->getBestProductDiscount($produk->id, null))->toBeNull();

    // Dan keranjangnya memang tidak memotong apa pun — inilah yang selama ini
    // bertentangan dengan lencananya.
    expect($layanan->calculateDiscount(ppsKeranjang($produk), null)['total_discount'])->toBe(0);
});

test('promo tanpa lampiran apa pun tetap berlaku untuk semua produk', function () {
    /*
     | Jalur yang TIDAK boleh ikut berubah. Promo seperti "Merdeka Sale!
     | Diskon 17% All Item" memang sengaja dibiarkan kosong supaya mengenai
     | seluruh toko; memperketat aturan tanpa penjagaan ini akan mematikannya
     | diam-diam.
     */
    $produk = ppsProduk();
    ppsPromo(['nama_promo' => 'Merdeka Sale']);

    $diskon = app(PromoService::class)->getBestProductDiscount($produk->id, null);

    expect($diskon)->not->toBeNull()
        ->and((float) $diskon['value'])->toBe(25.0);
});

test('produk yang dilampirkan sendiri tetap dapat promonya, walau paket ikut dilampirkan', function () {
    /*
     | Admin boleh melampirkan keduanya sekaligus: beberapa produk DAN
     | beberapa paket. Yang dilampirkan tetap dapat; yang tidak, tidak.
     */
    $produk = ppsProduk();
    $lain = Product::create(['nama_akun' => 'Grammarly Premium', 'harga_perbulan' => 30000]);

    $promo = ppsPromo();
    $promo->products()->attach($produk->id);
    $promo->bundlings()->attach(ppsPaket($lain)->id);

    $layanan = app(PromoService::class);

    expect($layanan->getBestProductDiscount($produk->id, null))->not->toBeNull()
        ->and($layanan->getBestProductDiscount($lain->id, null))->toBeNull();
});
