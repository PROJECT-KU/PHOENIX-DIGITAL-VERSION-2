<?php

use App\Models\Product;

/**
 * Kartu "Produk Lainnya" di halaman detail produk.
 *
 * Dicari lewat potongan HTML yang lengkap: nama kelasnya juga muncul di blok
 * <style> yang selalu dirender, jadi mencarinya begitu saja akan selalu ketemu.
 */
function produkRekomendasi(string $nama, array $lain = []): Product
{
    return Product::create(array_merge(
        ['nama_akun' => $nama, 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000],
        $lain,
    ));
}

it('produk yang dijeda tidak direkomendasikan, begitu pula produk yang sedang dibuka', function () {
    $utama = produkRekomendasi('Canva Premium');
    produkRekomendasi('Gemini Plus');
    produkRekomendasi('Kahoot Premium', ['dijeda' => true]);

    $this->get(route('shop.detail-product', $utama->id))
        ->assertOk()
        ->assertSee('<span class="rk-nama">Gemini Plus</span>', false)
        ->assertDontSee('<span class="rk-nama">Kahoot Premium</span>', false)
        ->assertDontSee('<span class="rk-nama">Canva Premium</span>', false);
});

it('gambar yang berkasnya tidak ada diganti ubin ikon kategori, bukan gambar rusak', function () {
    $utama = produkRekomendasi('Canva Premium');
    produkRekomendasi('Gemini Plus', ['image' => 'Product_tidak_ada_di_disk.webp']);

    $this->get(route('shop.detail-product', $utama->id))
        ->assertSee('class="rk-media is-kosong"', false)
        ->assertDontSee('Product_tidak_ada_di_disk.webp', false);
});

it('kartu memakai warna, ikon, dan label kategori produknya', function () {
    $utama = produkRekomendasi('Canva Premium');
    produkRekomendasi('Gemini Plus');

    $this->get(route('shop.detail-product', $utama->id))
        ->assertSee('class="rk-kartu" style="--c: #7c3aed"', false)
        ->assertSee('<i class="bi bi-robot"></i><span>AI Tools</span>', false)
        ->assertSee('<span class="rk-jenis">Sharing</span>', false);
});

it('tanpa produk lain, bagian rekomendasi tidak dirender', function () {
    $utama = produkRekomendasi('Canva Premium');

    $this->get(route('shop.detail-product', $utama->id))
        ->assertOk()
        ->assertDontSee('class="rk-deret"', false);
});
