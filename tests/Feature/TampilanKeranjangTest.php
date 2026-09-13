<?php

use App\Livewire\Pages\Public\ShopPage\CartPage;
use App\Models\Product;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(fn () => HargaPaket::lupakan());

/**
 * Tampilan /cart: barisnya memakai bahasa yang sama dengan Shop, Bundling, dan
 * Wishlist — warna mengikuti kategori produk, dan ubin ikon dipakai bila
 * gambarnya tak ada.
 */
it('baris produk berwarna kategori dengan ubin cadangan', function () {
    $p = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    session()->put('cart', [
        'k1' => [
            'product_id' => $p->id, 'product_name' => 'Canva Premium',
            // Berkasnya memang tidak ada — persis keadaan yang memunculkan
            // gambar rusak sebelum perbaikan ini.
            'product_image' => 'Product_64229.webp',
            'duration_type' => 'bulan', 'duration_value' => 1,
            'price' => 15000, 'quantity' => 1, 'subtotal' => 15000,
        ],
    ]);

    Livewire::test(CartPage::class)
        ->assertSeeHtml('<div class="kr-baris" style="--c: #db2777"')
        ->assertSeeHtml('<span class="kr-cadangan"><i class="bi bi-palette"></i></span>')
        ->assertSee('Desain & Kreatif')
        ->assertSee('Canva Premium')
        ->assertSee('Rp 15.000')
        // <img> TIDAK dipasang sama sekali, supaya teks alt tidak tampil
        // sebagai gambar rusak.
        ->assertDontSeeHtml('storage/img/Product/');
});

it('paket bundling memakai baris yang sama dengan jingga rumah', function () {
    session()->put('cart', [
        'p1' => [
            'product_id' => 'paket-1', 'product_name' => 'Paket Riset Sultan',
            'product_image' => 'ProductBundlings_15890.webp',
            'type' => 'bundling', 'duration_type' => null, 'duration_value' => null,
            'price' => 550000, 'quantity' => 1, 'subtotal' => 550000,
        ],
    ]);

    Livewire::test(CartPage::class)
        ->assertSeeHtml('<div class="kr-baris" style="--c: #f26522"')
        ->assertSeeHtml('<span class="kr-cadangan"><i class="bi bi-box2-heart-fill"></i></span>')
        ->assertSee('Paket Bundling')
        ->assertSee('Rp 550.000')
        ->assertDontSeeHtml('storage/img/ProductBundlings/');
});

it('gambar dipasang bila berkasnya benar-benar ada', function () {
    // Kebalikan dari dua uji di atas: yang dijaga bukan "jangan pernah pasang
    // gambar", melainkan "pasang hanya bila ada". Tanpa uji ini, menghapus
    // seluruh <img> pun akan terbaca lulus.
    $nama = 'Product_uji_'.uniqid().'.png';
    $tujuan = public_path('storage/img/Product/'.$nama);
    @mkdir(dirname($tujuan), 0777, true);
    file_put_contents($tujuan, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

    session()->put('cart', [
        'k1' => [
            'product_id' => 'x', 'product_name' => 'Kahoot Premium', 'product_image' => $nama,
            'duration_type' => 'bulan', 'duration_value' => 1,
            'price' => 20000, 'quantity' => 1, 'subtotal' => 20000,
        ],
    ]);

    try {
        Livewire::test(CartPage::class)
            ->assertSeeHtml('storage/img/Product/'.$nama)
            ->assertDontSeeHtml('class="kr-cadangan"');
    } finally {
        @unlink($tujuan);
    }
});

it('ringkasan menjumlahkan seluruh baris', function () {
    session()->put('cart', [
        'a' => ['product_id' => 'a', 'product_name' => 'NotebookLM', 'product_image' => null,
            'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000],
        'b' => ['product_id' => 'b', 'product_name' => 'Research Rabbit', 'product_image' => null,
            'duration_type' => 'tahun', 'duration_value' => 1, 'price' => 150000, 'quantity' => 1, 'subtotal' => 150000],
    ]);

    Livewire::test(CartPage::class)
        ->assertSee('2 item')
        ->assertSee('siap dibayar')
        ->assertSee('Rp 200.000');
});

it('rincian jasa tetap tampil di baris keranjang', function () {
    // Halaman dilewati & add-on adalah satu-satunya tempat pembeli jasa bisa
    // memastikan yang ia bayar sama dengan yang ia pilih.
    session()->put('cart', [
        'j1' => [
            'product_id' => 'j', 'product_name' => 'Cek Plagiasi Turnitin', 'product_image' => null,
            'duration_type' => 'halaman', 'duration_value' => 1,
            'price' => 185000, 'quantity' => 1, 'subtotal' => 185000,
            'jumlah_halaman' => 40, 'halaman_dihitung' => 35, 'halaman_dikecualikan' => '1,2,3,4,5',
            'addons' => [['nama' => 'Hapus Kutipan', 'harga' => 10000]],
        ],
    ]);

    Livewire::test(CartPage::class)
        ->assertSee('35 halaman')
        ->assertSee('dari 40')
        ->assertSee('Lewati hal. 1,2,3,4,5')
        ->assertSee('Hapus Kutipan')
        ->assertSee('Cek Plagiasi');
});

it('tombol hapus & kosongkan tetap memakai konfirmasi yang lama', function () {
    // Pendengarnya ada di public-custom-scripts.js yang beku di server:
    // nama event TIDAK boleh berubah, kalau tidak konfirmasinya diam saja.
    session()->put('cart', [
        'k1' => ['product_id' => 'a', 'product_name' => 'NotebookLM', 'product_image' => null,
            'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000],
    ]);

    Livewire::test(CartPage::class)
        ->assertSeeHtml("\$dispatch('confirm-empty-cart')")
        ->assertSeeHtml("\$dispatch('confirm-delete-product-cart', 'k1')");
});

it('keranjang kosong menawarkan jalan keluar', function () {
    session()->forget('cart');

    Livewire::test(CartPage::class)
        ->assertSee('Keranjang Anda kosong')
        ->assertSeeHtml('href="'.route('shop.index').'"');
});

it('tiga langkah sesudah checkout diterangkan di halamannya', function () {
    session()->put('cart', [
        'k1' => ['product_id' => 'a', 'product_name' => 'NotebookLM', 'product_image' => null,
            'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000],
    ]);

    Livewire::test(CartPage::class)
        ->assertSee('Setelah Checkout')
        ->assertSee('Bayar')
        ->assertSee('Kami proses')
        ->assertSee('Terima hasilnya');
});
