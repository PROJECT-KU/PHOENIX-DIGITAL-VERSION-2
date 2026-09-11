<?php

use App\Models\Product;
use App\Support\PaketHemat;

/**
 * "Paling hemat" di kartu paket halaman produk dan jendela durasi /shop.
 *
 * Satu aturan (App\Support\PaketHemat) untuk kedua tempat.
 */
it('jumlah bulan paket: bulan apa adanya, tahun dikali dua belas, satuan lain tidak dihitung', function () {
    expect(PaketHemat::bulan('bulan', 5))->toBe(5)
        ->and(PaketHemat::bulan('Tahun', 1))->toBe(12)
        ->and(PaketHemat::bulan('kali', 1))->toBeNull();
});

it('paling hemat = harga per bulan terendah, dan tidak ada bila tak ada pembanding atau semuanya sama', function () {
    expect(PaketHemat::terhemat([0 => 20000, 1 => 15000, 2 => 9000, 3 => 12500]))->toBe(2)
        ->and(PaketHemat::terhemat([0 => 20000]))->toBeNull()
        ->and(PaketHemat::terhemat([0 => 10000, 1 => 10000]))->toBeNull()
        // Paket yang satuannya tak bisa dihitung per bulan tidak ikut dibandingkan.
        ->and(PaketHemat::terhemat([0 => null, 1 => 5000, 2 => 8000]))->toBe(1);
});

it('harga setara per bulan hanya untuk paket lebih dari sebulan', function () {
    expect(PaketHemat::setaraPerBulan(90000, 10))->toBe('≈ Rp9.000/bulan')
        ->and(PaketHemat::setaraPerBulan(90000, 10, 'Rp '))->toBe('≈ Rp 9.000/bulan')
        ->and(PaketHemat::setaraPerBulan(20000, 1))->toBeNull()
        ->and(PaketHemat::setaraPerBulan(5000, null))->toBeNull();
});

it('halaman produk menandai tepat satu paket paling hemat, di kartu yang benar', function () {
    // 1 bln 20.000 · 5 bln 70.000 (14.000/bln) · 10 bln 90.000 (9.000/bln) · 1 thn 150.000 (12.500/bln)
    $p = Product::create([
        'nama_akun' => 'Story Tribe Premium', 'tipe_akun' => 'sharing',
        'harga_perbulan' => 20000, 'harga_5_perbulan' => 70000,
        'harga_10_perbulan' => 90000, 'harga_pertahun' => 150000,
    ]);

    $html = $this->get(route('shop.detail-product', $p->id))->assertOk()->getContent();

    $kartu10 = strpos($html, '<span class="pd-pkg-dur">10 Bulan');
    $kartuTahun = strpos($html, '<span class="pd-pkg-dur">1 Tahun');
    $pil = strpos($html, 'class="pd-pkg-hemat"');

    expect(substr_count($html, 'class="pd-pkg-hemat"'))->toBe(1)
        ->and($pil)->toBeGreaterThan($kartu10)
        ->and($pil)->toBeLessThan($kartuTahun)
        ->and($html)->toContain('≈ Rp 9.000/bulan');
});

it('produk dengan satu paket saja tidak diberi label paling hemat', function () {
    $p = Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    $html = $this->get(route('shop.detail-product', $p->id))->assertOk()->getContent();

    expect(substr_count($html, 'class="pd-pkg-hemat"'))->toBe(0);
});
