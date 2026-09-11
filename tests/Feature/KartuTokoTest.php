<?php

use App\Livewire\Pages\Public\ShopPage\Index;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

/**
 * Papan saring & kartu produk di halaman /shop.
 *
 * Dicari lewat potongan HTML yang lengkap: nama kelasnya juga muncul di blok
 * <style> yang selalu dirender, jadi mencarinya begitu saja akan selalu ketemu.
 */
beforeEach(function () {
    Cache::flush();
    Product::query()->delete();
});

function produkKartuToko(string $nama, array $lain = []): Product
{
    return Product::create(array_merge(
        ['nama_akun' => $nama, 'tipe_akun' => 'sharing', 'harga_perbulan' => 20000],
        $lain,
    ));
}

it('chip kategori menyaring daftar, dan kunci yang tidak dikenal dianggap "Semua"', function () {
    produkKartuToko('Chat Gpt Plus Sharing');
    produkKartuToko('Canva Premium');

    Livewire::test(Index::class)
        ->call('pilihKategori', 'ai-tools')
        ->assertSet('kategori', 'ai-tools')
        ->assertSeeHtml('class="sk-nama">Chat Gpt Plus Sharing</a>')
        ->assertDontSeeHtml('class="sk-nama">Canva Premium</a>')
        // Nilai dari peramban: kunci karangan tidak boleh menyaring apa pun.
        ->call('pilihKategori', 'kategori-karangan')
        ->assertSet('kategori', '')
        ->assertSeeHtml('class="sk-nama">Canva Premium</a>');
});

it('reset melepas kategori, tipe akun, dan urutan sekaligus', function () {
    produkKartuToko('Chat Gpt Plus Private', ['tipe_akun' => 'private']);

    Livewire::test(Index::class)
        ->call('pilihKategori', 'ai-tools')
        ->set('tipe', 'private')
        ->set('sortBy', 'termurah')
        ->assertSeeHtml('class="sf-reset"')
        ->call('resetFilters')
        ->assertSet('kategori', '')
        ->assertSet('tipe', '')
        ->assertSet('sortBy', '')
        ->assertDontSeeHtml('class="sf-reset"');
});

it('gambar yang berkasnya tidak ada diganti ubin ikon kategori, bukan teks alt terpotong', function () {
    produkKartuToko('Canva Premium', ['image' => 'Product_tidak_ada_di_disk.webp']);

    $this->get('/shop')
        ->assertSee('class="sk-media is-kosong"', false)
        ->assertDontSee('Product_tidak_ada_di_disk.webp', false);
});

it('produk yang dijeda tetap tampil, tetapi kartunya ditandai dan tombolnya menyebut alasannya', function () {
    produkKartuToko('Kahoot Premium', ['dijeda' => true]);

    $this->get('/shop')
        ->assertSee('class="sk-kartu is-jeda"', false)
        ->assertSee('Tidak Tersedia');
});

it('tipe akun tampil sebagai tombol segmen, bukan kotak pilih', function () {
    produkKartuToko('Chat Gpt Plus Sharing');
    produkKartuToko('Chat Gpt Plus Private', ['tipe_akun' => 'private']);

    $this->get('/shop')
        ->assertSee("wire:click=\"\$set('tipe', 'private')\">Private</button>", false)
        ->assertDontSee('wire:model.live="tipe"', false);
});
