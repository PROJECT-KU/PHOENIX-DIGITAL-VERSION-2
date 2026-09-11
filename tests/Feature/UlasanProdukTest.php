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
        ->assertSeeHtml('x-ref="form" x-show="true"')
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

/**
 * Ulasan yang banyak: dibuat berurutan waktu supaya urutan "terbaru" pasti.
 *
 * @param  array<int, int>  $rating
 */
function banyakUlasan(Product $p, array $rating): void
{
    foreach ($rating as $i => $bintang) {
        ulasanUji($p, $bintang, "Ulasan nomor {$i}")
            ->forceFill(['created_at' => now()->subMinutes(count($rating) - $i)])->save();
    }
}

it('ulasan yang banyak tampil lima dulu, lalu bertambah lima tiap klik', function () {
    $p = produkUntukUlasan();
    banyakUlasan($p, array_fill(0, 12, 5));

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertViewHas('reviews', fn ($r) => $r->count() === 5)
        ->assertSee('7 tersisa')
        ->call('muatLagi')
        ->assertViewHas('reviews', fn ($r) => $r->count() === 10)
        ->assertSee('Tampilkan 2 ulasan lagi')
        ->call('muatLagi')
        ->assertViewHas('reviews', fn ($r) => $r->count() === 12)
        ->assertSee('Semua 12 ulasan sudah ditampilkan')
        ->assertDontSeeHtml('class="ul-lagi"');
});

it('saring bintang hanya mengubah daftar, ringkasannya tetap dari semua ulasan', function () {
    $p = produkUntukUlasan();
    banyakUlasan($p, [5, 5, 5, 5, 4, 4, 2]);

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->call('saringBintang', 4)
        ->assertViewHas('reviews', fn ($r) => $r->pluck('rating')->unique()->values()->all() === [4])
        ->assertViewHas('jumlahTersaring', 2)
        ->assertViewHas('count', 7)
        // Klik bintang yang sama melepas saringannya.
        ->call('saringBintang', 4)
        ->assertSet('bintang', null)
        ->assertViewHas('jumlahTersaring', 7);
});

it('urutan rating terendah menaruh ulasan paling kritis di atas', function () {
    $p = produkUntukUlasan();
    banyakUlasan($p, [5, 3, 5, 1, 4, 5]);

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->set('urut', 'terendah')
        ->assertViewHas('reviews', fn ($r) => $r->first()->rating === 1)
        ->set('urut', 'tertinggi')
        ->assertViewHas('reviews', fn ($r) => $r->first()->rating === 5);
});

it('nilai dari peramban yang tidak masuk akal dirapikan sebelum menyentuh query', function () {
    $p = produkUntukUlasan();
    banyakUlasan($p, array_fill(0, 8, 5));

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->set('tampil', 100000)
        ->assertSet('tampil', ProductReviews::TAMPIL_MAKS)
        ->set('bintang', 9)
        ->assertSet('bintang', null)
        ->set('urut', 'acak; drop table')
        ->assertSet('urut', 'terbaru');
});

it('penyaring hanya muncul bila ulasannya lebih dari satu muatan', function () {
    $p = produkUntukUlasan();
    banyakUlasan($p, array_fill(0, 5, 5));

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertDontSeeHtml('class="ul-alat"');

    ulasanUji($p, 4, 'Ulasan keenam');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSeeHtml('class="ul-alat"');
});

it('ulasan panjang dilipat dengan tautan baca selengkapnya', function () {
    $p = produkUntukUlasan();
    ulasanUji($p, 5, str_repeat('Prosesnya cepat dan akunnya langsung bisa dipakai. ', 6));
    ulasanUji($p, 5, 'Singkat saja');

    Livewire::test(ProductReviews::class, ['productId' => $p->id])
        ->assertSeeHtml('class="ul-teks is-lipat"')
        ->assertSeeHtml('class="ul-baca"')
        ->assertSeeHtml('<p class="ul-teks">Singkat saja</p>');
});

it('tidak ada direktif blok Blade di dalam atribut tag komponen ulasan', function () {
    // Livewire tidak memasang penanda morph untuk direktif yang disangkanya
    // berada di dalam tag; pasangan pembuka/penutupnya lalu bisa timpang.
    $blade = file_get_contents(resource_path('views/livewire/components/product-reviews.blade.php'));
    $tanpaKomentar = preg_replace('/\{\{--.*?--\}\}/s', '', $blade);

    preg_match_all('/<[a-z][^<>]*@(if|unless|foreach|for|forelse|while|isset|empty|switch)\b[^<>]*>/i', $tanpaKomentar, $m);

    expect($m[0])->toBe([]);
});

it('penanda morph Livewire di komponen ulasan bersarang dengan benar', function () {
    // Livewire melewati penanda pembuka/penutup sebuah direktif blok bila teks
    // sesudahnya, sampai tanda "<" berikutnya, memuat ">" — misalnya @for (...)
    // yang langsung diikuti @if ($n > 0). Totalnya bisa tetap seimbang tetapi
    // urutannya salah, dan setiap pembaruan (muat lagi, saring, urut) gagal di
    // Block.appendChild: daftar ulasan tampil separuh atau kacau.
    //
    // Pemasang penanda Livewire sendiri dijalankan langsung pada SUMBER view,
    // bukan lewat render: view terkompilasi di-cache dan dipakai bersama, dan
    // salinan yang dikompilasi di luar render komponen Livewire tidak berisi
    // penanda sama sekali — uji lewat render jadi lulus-gagal tergantung siapa
    // yang lebih dulu mengompilasinya.
    $arah = [
        '@if' => '@endif', '@unless' => '@endunless', '@error' => '@enderror', '@isset' => '@endisset',
        '@empty' => '@endempty', '@auth' => '@endauth', '@guest' => '@endguest', '@switch' => '@endswitch',
        '@foreach' => '@endforeach', '@forelse' => '@endforelse', '@while' => '@endwhile', '@for' => '@endfor',
    ];
    foreach (array_keys(\Livewire\invade(app('blade.compiler'))->conditions) as $kondisi) {
        $arah['@'.$kondisi] = '@end'.$kondisi;
    }
    $pembuka = implode('|', array_map(fn ($d) => preg_quote(substr($d, 1), '/'), array_keys($arah)));

    foreach (['views/livewire/components/product-reviews.blade.php', 'views/components/kepala-bagian.blade.php'] as $jalur) {
        $sumber = file_get_contents(resource_path($jalur));
        $hasil = \Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement::compileDirectives($sumber, $arah);
        preg_match_all('/<!--\[if (BLOCK|ENDBLOCK)\]><!\[endif\]-->/', $hasil, $m);

        $dalam = 0;
        $terendah = 0;
        foreach ($m[1] as $jenis) {
            $dalam += $jenis === 'BLOCK' ? 1 : -1;
            $terendah = min($terendah, $dalam);
        }

        // Setiap direktif pembuka di sumber harus mendapat penandanya.
        $jumlahPembuka = preg_match_all('/\B@(?:'.$pembuka.')(?![a-zA-Z])/', $sumber);

        expect(['berkas' => $jalur, 'penanda pembuka' => count(array_keys($m[1], 'BLOCK')), 'terendah' => $terendah, 'akhir' => $dalam])
            ->toBe(['berkas' => $jalur, 'penanda pembuka' => $jumlahPembuka, 'terendah' => 0, 'akhir' => 0]);
    }
});

it('tidak ada direktif blok yang penanda morph-nya akan dilewati Livewire', function () {
    // Meniru SupportMorphAwareIfStatement (Livewire 3.6): penanda sebuah
    // direktif blok dilewati bila teks sesudahnya, sampai tanda "<" berikutnya,
    // memuat ">" yang tidak didahului ?, = atau - (misal "@if ($n > 0)" tepat
    // sesudah "@endforeach"). Pesan gagalnya menunjuk baris yang harus diubah;
    // perbandingan sebaiknya dirakit jadi boolean di blok @php paling atas.
    $blok = 'if|unless|error|isset|empty|auth|guest|switch|foreach|forelse|while|for';
    $pelanggar = [];

    foreach (['views/livewire/components/product-reviews.blade.php', 'views/components/kepala-bagian.blade.php'] as $jalur) {
        $t = file_get_contents(resource_path($jalur));
        preg_match_all('/\B@((?:end)?(?:'.$blok.'))(?![a-zA-Z])([ \t]*)(\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\))?/', $t, $m, PREG_OFFSET_CAPTURE);

        foreach ($m[0] as [$teks, $pos]) {
            $sampaiTag = explode('<', substr($t, $pos + strlen($teks)), 2)[0];

            if (preg_match('/(?<![?=-])>/', $sampaiTag)) {
                $pelanggar[] = basename($jalur).':'.(substr_count(substr($t, 0, $pos), "\n") + 1).' '.trim($teks);
            }
        }
    }

    expect($pelanggar)->toBe([]);
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
