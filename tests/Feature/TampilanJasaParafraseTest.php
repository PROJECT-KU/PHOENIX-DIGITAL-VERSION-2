<?php

use App\Livewire\Pages\Public\ShopPage\ProductDetail;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductPrice;
use App\Support\IkonAddon;
use Livewire\Livewire;

/**
 * Tampilan blok jasa per halaman di detail produk: bagian dokumen yang
 * dilewati, halaman dikecualikan, add-on, dan rincian biaya.
 */
function jasaParafraseTampil(): Product
{
    $produk = Product::create([
        'nama_akun' => 'Jasa Parafrase Manual',
        'butuh_file' => true,
        'jasa_mode' => 'halaman',
        'harga_perbulan' => 15000,
    ]);

    ProductPrice::create(['product_id' => $produk->id, 'durasi_type' => 'halaman', 'durasi_value' => 1, 'harga' => 15000]);

    ProductAddon::create([
        'product_id' => $produk->id, 'nama' => 'Plagiasi di bawah 30% maksimal 20%', 'harga' => 50000,
        'aktif' => true, 'urutan' => 1, 'pakai_exclude' => false, 'cek_ai' => false, 'jenis_layanan' => '',
    ]);
    ProductAddon::create([
        'product_id' => $produk->id, 'nama' => 'Cek Plagiasi AI', 'harga' => 0,
        'aktif' => true, 'urutan' => 2, 'pakai_exclude' => false, 'cek_ai' => true, 'jenis_layanan' => 'ai',
    ]);

    return $produk->fresh();
}

it('ikon add-on ditebak dari sifat cek_ai lalu dari namanya', function () {
    expect(IkonAddon::untuk('Cek Plagiasi AI')['ikon'])->toBe('bi-robot')
        ->and(IkonAddon::untuk('Deteksi tulisan', true)['ikon'])->toBe('bi-robot')
        ->and(IkonAddon::untuk('Cek Plagiasi Turnitin')['ikon'])->toBe('bi-patch-check')
        ->and(IkonAddon::untuk('Plagiasi di bawah 20% maksimal 5%')['ikon'])->toBe('bi-shield-check')
        ->and(IkonAddon::untuk('Bonus lain'))->toBe(IkonAddon::BAWAAN)
        // "ai" harus kata utuh: "detail" tidak boleh jadi add-on AI.
        ->and(IkonAddon::untuk('Revisi detail')['ikon'])->not->toBe('bi-robot');
});

it('add-on tampil sebagai kartu berikon warna, harga nol ditulis Gratis', function () {
    $produk = jasaParafraseTampil();

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->assertSeeHtml('<span class="jd-addon-ic"><i class="bi bi-shield-check"></i></span>')
        ->assertSeeHtml('<span class="jd-addon-ic"><i class="bi bi-robot"></i></span>')
        ->assertSeeHtml('style="--a: #7c3aed"')
        ->assertSee('+Rp 50.000')
        ->assertSeeHtml('jd-addon-harga is-gratis">Gratis</span>')
        ->assertSee('Boleh lebih dari satu')
        ->assertSeeHtml('aria-pressed="false"');
});

it('bagian dokumen tampil sebagai ubin berikon dengan status dilewati atau diparafrase', function () {
    $produk = jasaParafraseTampil();

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->set('draftUploadId', 'uji-tampilan')
        ->set('jumlahHalaman', 5)
        ->set('excludeCover', true)
        ->set('excludeDaftarIsi', false)
        ->assertSeeHtml('wire:model.live="excludeCover"')
        ->assertSeeHtml('<span class="jd-bagian-ic"><i class="bi bi-list-ol"></i></span>')
        ->assertSeeHtml('jd-bagian-status">Dilewati</small>')
        ->assertSeeHtml('jd-bagian-status">Diparafrase</small>')
        ->assertSee('Ada halaman yang tidak perlu diparafrase?');
});

it('rincian biaya merinci tiap add-on terpilih dan halaman yang dikecualikan', function () {
    $produk = jasaParafraseTampil();
    $plagiasi = $produk->addonAktif()->first();

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->set('jumlahHalaman', 12)
        ->set('halamanDikecualikan', '1,12')
        ->call('toggleAddon', $plagiasi->id)
        ->assertSee('Rincian Biaya')
        ->assertSee('Parafrase 10 halaman')
        ->assertSee('2 halaman dikecualikan')
        ->assertSee('Tidak ditagih')
        ->assertSee('Plagiasi di bawah 30% maksimal 20%')
        ->assertSee('Sudah termasuk tambahan')
        // 10 halaman × Rp 15.000 + add-on Rp 50.000
        ->assertSeeHtml('<b>Rp 200.000</b>');
});

it('rincian biaya tidak muncul sebelum PDF terbaca', function () {
    $produk = jasaParafraseTampil();

    Livewire::test(ProductDetail::class, ['id' => $produk->id])
        ->assertDontSee('Rincian Biaya');
});
