<?php

use App\Livewire\Pages\Public\Bundling\Detail;
use App\Models\ProductBundlings;
use App\Models\Promo;
use App\Support\HargaPaket;
use Livewire\Livewire;

beforeEach(function () {
    HargaPaket::lupakan();
});

/** Paket contoh: harga satuan 160.000, harga paket 125.000. */
function paketUji(): ProductBundlings
{
    return ProductBundlings::create([
        'nama_paket' => 'Paket Uji Detail',
        'harga_awal' => 160000,
        'harga_bundling' => 125000,
        'status' => 'active',
    ]);
}

function flashSale(int $persen = 10): Promo
{
    return Promo::create([
        'nama_promo' => 'Flash Uji',
        'tipe_promo' => 'flash_sale',
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => $persen,
        'diskon_non_member_persen' => $persen,
        'is_active' => true,
        'mulai_promo' => now()->subDay(),
        'selesai_promo' => now()->addDay(),
        'prioritas' => 99,
    ]);
}

/** Blok harga saja; angka di kartu "Paket Lainnya" bukan harga paket ini. */
function blokHarga(string $html): string
{
    preg_match('#<div class="pd-price">.*?</div>#s', $html, $m);

    return $m[0] ?? '';
}

it('tanpa promo: mencoret harga awal dan menagih harga paket', function () {
    $paket = paketUji();

    $hp = HargaPaket::untuk($paket);

    expect($hp['bayar'])->toBe(125000)
        ->and($hp['coret'])->toBe(160000)
        ->and($hp['potongan'])->toBe(0);
});

it('kena flash sale: potongan dihitung dari harga awal, yang dicoret harga awal', function () {
    $paket = paketUji();
    flashSale(10)->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    $hp = HargaPaket::untuk($paket->fresh());

    // 160.000 x 10% = 16.000 -> 125.000 - 16.000 = 109.000
    expect($hp['bayar'])->toBe(109000)
        // Yang dicoret harga awal, bukan harga paket.
        ->and($hp['coret'])->toBe(160000)
        // "Hemat" selalu selisih terhadap angka yang dicoret.
        ->and($hp['potongan'])->toBe(51000);
});

it('halaman detail menampilkan harga coret dari harga awal', function () {
    $paket = paketUji();
    flashSale(10)->bundlings()->attach($paket->id);
    HargaPaket::lupakan();

    $blok = blokHarga(Livewire::test(Detail::class, ['id' => $paket->id])->html());

    expect($blok)->toContain('Rp 109.000')
        ->and($blok)->toContain('Rp 160.000')
        ->and($blok)->not->toContain('125.000');
});

it('halaman detail memakai kerangka yang sama dengan detail produk shop', function () {
    $html = Livewire::test(Detail::class, ['id' => paketUji()->id])->html();

    expect($html)->toContain('pd-section')
        ->and($html)->toContain('pd-price-old')
        ->and($html)->toContain('pd-add');
});

it('paket bisa dimasukkan keranjang dari halaman detail', function () {
    $paket = paketUji();

    Livewire::test(Detail::class, ['id' => $paket->id])->call('addToCart');

    expect(session('cart'))->toHaveKey("bundling_{$paket->id}");
});

it('halaman ikut hilang ketika jadwal paket sudah berakhir', function () {
    $paket = paketUji();
    $paket->update(['selesai_tayang' => now()->subHour()]);

    Livewire::test(Detail::class, ['id' => $paket->id])->assertStatus(404);
});

it('menolak paket yang tidak dikenal', function () {
    Livewire::test(Detail::class, ['id' => (string) Str::uuid()])->assertStatus(404);
});

it('tombol Lihat di daftar paket menuju halaman detail, bukan halaman itu sendiri', function () {
    $paket = paketUji();

    foreach ([
        App\Livewire\Pages\Public\Bundling\Index::class,
        App\Livewire\Pages\Public\Bundling\ProductBundlings::class,
    ] as $kelas) {
        $html = Livewire::test($kelas)->html();

        expect($html)->toContain('/bundling/paket/'.$paket->id);
    }
});
