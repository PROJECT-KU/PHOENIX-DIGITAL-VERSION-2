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

it('bilah filter memakai kelas yang sama dengan shop', function () {
    $produk = Product::create(['nama_akun' => 'Canva Pro', 'harga_perbulan' => 20000]);
    buatPaket('Paket A', 100000, $produk->id);

    $html = Livewire::test(HalamanPaket::class)->html();

    foreach (['shop-filter', 'shop-filter-controls', 'shop-select', 'shop-filter-count'] as $kelas) {
        expect($html)->toContain($kelas);
    }
});

it('gambar paket memakai gaya yang sama dengan produk satuan', function () {
    buatPaket('Paket A', 100000);

    $html = Livewire::test(HalamanPaket::class)->html();

    // Penanda dari partials/media-produk-style, yang juga dipakai kartu shop.
    expect($html)->toContain('.fs-card-media')
        ->and($html)->toContain('mediaGlow');
});
