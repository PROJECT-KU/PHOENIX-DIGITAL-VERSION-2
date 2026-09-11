<?php

use App\Livewire\Components\ProductReviews;
use App\Models\Product;
use App\Models\ProductReview;
use Livewire\Livewire;

/**
 * Ulasan pelanggan di halaman detail produk.
 *
 * Kelas dicari lewat atribut class yang lengkap: nama kelasnya juga muncul di
 * blok <style> yang selalu dirender, jadi mencarinya begitu saja akan selalu
 * ketemu dan ujinya tidak menguji apa pun.
 */
function produkUntukUlasan(): Product
{
    return Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);
}

function ulasanUji(Product $p, int $rating, string $teks, string $status = 'approved'): ProductReview
{
    return ProductReview::create([
        'product_id' => $p->id, 'nama' => 'Pengulas', 'rating' => $rating, 'ulasan' => $teks, 'status' => $status,
    ]);
}

it('ulasan yang belum disetujui admin tidak tampil', function () {
    $p = produkUntukUlasan();
    ulasanUji($p, 5, 'Prosesnya cepat sekali');
    ulasanUji($p, 1, 'Masih menunggu moderasi', 'pending');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSeeHtml('class="ul-kartu"')
        ->assertSee('Prosesnya cepat sekali')
        ->assertDontSee('Masih menunggu moderasi');
});

it('sebaran rating dihitung per bintang dan batangnya proporsional', function () {
    $p = produkUntukUlasan();
    ulasanUji($p, 5, 'Mantap sekali');
    ulasanUji($p, 5, 'Bagus sekali');
    ulasanUji($p, 4, 'Cukup memuaskan');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertViewHas('sebaran', fn ($s) => (int) $s[5] === 2 && (int) $s[4] === 1 && ! isset($s[3]))
        ->assertSeeHtml('--w: 67%')
        ->assertSeeHtml('--w: 33%')
        ->assertSeeHtml('--w: 0%');
});

it('rata-rata 4,3 tampil dengan setengah bintang, bukan dibulatkan', function () {
    $p = produkUntukUlasan();
    ulasanUji($p, 5, 'Mantap sekali');
    ulasanUji($p, 4, 'Bagus sekali');
    ulasanUji($p, 4, 'Cukup memuaskan');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSee('4,3')
        ->assertSeeHtml('bi-star-half');
});

it('tanpa ulasan, formulir langsung terbuka di samping ajakan — tanpa ringkasan kosong', function () {
    $p = produkUntukUlasan();

    // Formulir TIDAK dilipat (tanpa x-show): menulis ulasan adalah satu-satunya
    // hal yang bisa dilakukan di bagian ini, jadi tidak perlu diklik dulu.
    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSeeHtml('class="ul-kosong"')
        ->assertSeeHtml('class="ul-form" x-ref="form" >')
        ->assertDontSeeHtml('x-show="showForm"')
        ->assertDontSeeHtml('class="ul-ringkas"');
});

it('sesudah ada ulasan, formulir dilipat di balik tombol Tulis Ulasan', function () {
    $p = produkUntukUlasan();
    ulasanUji($p, 5, 'Mantap sekali');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSeeHtml('class="ul-ringkas"')
        ->assertSeeHtml('x-show="showForm"')
        ->assertSee('Tulis Ulasan');
});

it('ulasan baru masuk sebagai pending dan pengirim melihat ucapan terima kasih', function () {
    $p = produkUntukUlasan();

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->set('nama', 'Sari')
        ->set('rating', 4)
        ->set('ulasan', 'Prosesnya cepat dan rapi')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSeeHtml('class="ul-terima"')
        ->assertDontSeeHtml('class="ul-form"');

    expect(ProductReview::where('product_id', $p->id)->sole()->status)->toBe('pending');
});

it('tanggal ulasan tercetak dengan nama bulan Indonesia', function () {
    $p = produkUntukUlasan();
    $r = ulasanUji($p, 5, 'Mantap sekali');
    $r->forceFill(['created_at' => '2026-08-17 10:00:00'])->save();

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        // Singkatan Carbon untuk Agustus dalam bahasa Indonesia adalah "Agt".
        ->assertSee('17 Agt 2026')
        ->assertDontSee('17 Aug 2026');
});
