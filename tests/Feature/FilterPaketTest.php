<?php

use App\Livewire\Pages\Public\Bundling\ProductBundlings as HalamanPaket;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

function buatPaket(string $nama, int $harga, ?string $produkId = null): ProductBundlings
{
    return ProductBundlings::create([
        'nama_paket' => $nama,
        // Harga disimpan sebagai teks berformat, sama seperti data sungguhan.
        'harga_awal' => 'Rp '.number_format($harga + 50000, 0, ',', '.'),
        'harga_bundling' => 'Rp '.number_format($harga, 0, ',', '.'),
        'status' => 'active',
        'product_1' => $produkId,
    ]);
}

it('mengurutkan dari termurah walau harga tersimpan sebagai teks berformat', function () {
    buatPaket('Zeta Mahal', 300000);
    buatPaket('Alfa Murah', 70000);
    buatPaket('Beta Tengah', 150000);

    $urut = Livewire::test(HalamanPaket::class)
        ->set('sortBy', 'termurah')
        ->viewData('bundlings')
        ->pluck('nama_paket')
        ->all();

    expect($urut)->toBe(['Alfa Murah', 'Beta Tengah', 'Zeta Mahal']);
});

it('mengurutkan dari termahal', function () {
    buatPaket('Zeta Mahal', 300000);
    buatPaket('Alfa Murah', 70000);

    $urut = Livewire::test(HalamanPaket::class)
        ->set('sortBy', 'termahal')
        ->viewData('bundlings')
        ->pluck('nama_paket')
        ->all();

    expect($urut)->toBe(['Zeta Mahal', 'Alfa Murah']);
});

it('mengurutkan berdasarkan nama A sampai Z', function () {
    buatPaket('Zeta', 300000);
    buatPaket('Alfa', 70000);

    $urut = Livewire::test(HalamanPaket::class)
        ->set('sortBy', 'nama')
        ->viewData('bundlings')
        ->pluck('nama_paket')
        ->all();

    expect($urut)->toBe(['Alfa', 'Zeta']);
});

it('menyaring paket berdasarkan produk yang ada di dalamnya', function () {
    $canva = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    $lain = Product::create(['nama_akun' => 'Netflix', 'harga_perbulan' => 30000]);

    buatPaket('Paket Desain', 100000, $canva->id);
    buatPaket('Paket Nonton', 120000, $lain->id);

    $hasil = Livewire::test(HalamanPaket::class)
        ->set('isi', $canva->id)
        ->viewData('bundlings');

    expect($hasil->pluck('nama_paket')->all())->toBe(['Paket Desain']);
});

it('pilihan isi hanya berisi produk yang benar-benar dipakai paket tayang', function () {
    $dipakai = Product::create(['nama_akun' => 'Dipakai', 'harga_perbulan' => 10000]);
    $tidak = Product::create(['nama_akun' => 'Tidak Dipakai', 'harga_perbulan' => 10000]);

    buatPaket('Paket A', 100000, $dipakai->id);

    $pilihan = Livewire::test(HalamanPaket::class)->viewData('pilihanIsi');

    expect($pilihan->keys()->all())->toContain($dipakai->id)
        ->and($pilihan->keys()->all())->not->toContain($tidak->id);
});

it('reset mengembalikan filter ke keadaan semula', function () {
    buatPaket('Paket A', 100000);

    $t = Livewire::test(HalamanPaket::class)
        ->set('sortBy', 'termahal')
        ->set('isi', 'apa-saja')
        ->call('resetFilters');

    expect($t->get('sortBy'))->toBe('')
        ->and($t->get('isi'))->toBe('');
});

it('papan saring memakai bahasa visual yang sama dengan shop', function () {
    $produk = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    buatPaket('Paket A', 100000, $produk->id);

    $html = Livewire::test(HalamanPaket::class)->html();

    // Dicari lewat atribut class lengkap: nama kelasnya juga ada di blok <style>.
    foreach (['sf-papan', 'sf-kategori', 'sf-urut', 'sf-info'] as $kelas) {
        expect($html)->toContain('class="'.$kelas.'"');
    }
});

it('chip isi paket menandai produk yang sedang menyaring', function () {
    $canva = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    buatPaket('Paket Desain', 100000, $canva->id);

    Livewire::test(HalamanPaket::class)
        ->assertSeeHtml('data-isi="" aria-pressed="true"')
        ->set('isi', $canva->id)
        ->assertSeeHtml('data-isi="'.$canva->id.'" aria-pressed="true"')
        ->assertSeeHtml('data-isi="" aria-pressed="false"');
});

it('paket tanpa gambar menampilkan tumpukan ikon produk isinya, bukan gambar rusak', function () {
    $canva = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    buatPaket('Paket Desain', 100000, $canva->id);

    Livewire::test(HalamanPaket::class)
        ->assertSeeHtml('class="pb-media is-kosong"')
        // Canva masuk kategori Desain & Kreatif: ubinnya berwarna & berikon kategori itu.
        ->assertSeeHtml('--p: #db2777')
        ->assertSeeHtml('<span class="pb-daftar-nama">Canva Pro</span>')
        ->assertSeeHtml('<span class="pb-daftar-dur">1 Bulan</span>');
});

it('harga kartu paket memakai HargaPaket, sumber yang sama dengan keranjang', function () {
    buatPaket('Paket A', 100000); // awal 150.000, paket 100.000, tanpa promo

    Livewire::test(HalamanPaket::class)
        ->assertSeeHtml('<span class="pb-rp">Rp</span>100.000</b>')
        ->assertSeeHtml('<s>Rp150.000</s>')
        ->assertSeeHtml('<span class="pb-hemat">Hemat Rp50.000</span>')
        ->assertSeeHtml('<i class="bi bi-tag-fill"></i>-33%');
});

it('jarak dan grid halaman paket sama dengan halaman shop', function () {
    $paket = file_get_contents(resource_path('views/livewire/pages/public/bundling/product-bundlings.blade.php'));
    $shop = file_get_contents(resource_path('views/livewire/pages/public/shop-page/index.blade.php'));

    // Gutter grid sama persis dengan shop.
    expect($paket)->toContain('class="row g-3 g-lg-4"')
        ->and($shop)->toContain('class="row g-3 g-lg-4"');

    // Empat kolom mulai dari lg, sama seperti shop — bukan xl.
    expect($paket)->toContain('col-6 col-md-4 col-lg-3')
        ->and($paket)->not->toContain('col-6 col-md-4 col-xl-3');

    // Tanpa perataan tengah, supaya kartunya tersusun dari kiri seperti shop.
    expect($paket)->not->toContain('row g-4 justify-content-center');

    // Section daftar tidak lagi memakai 60px bawaan .best-sellers di atas.
    expect($paket)->toContain('style="padding-top: 0; padding-bottom: 60px;"');
});
