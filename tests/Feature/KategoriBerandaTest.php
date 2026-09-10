<?php

use App\Models\Product;
use App\Support\KategoriBeranda;
use App\Support\MerekDipercaya;
use Illuminate\Support\Facades\Cache;

/**
 * Kategori & merek di beranda.
 *
 * Yang dijaga di sini satu hal: HALAMAN TIDAK BOLEH MENJANJIKAN ISI YANG TIDAK
 * ADA. Chip kategori yang membawa pengunjung ke halaman kosong, atau logo merek
 * yang produknya sudah berhenti dijual, adalah kebohongan kecil yang menggerus
 * kepercayaan justru di bagian halaman yang tugasnya membangun kepercayaan.
 */
beforeEach(function () {
    Cache::flush();
});

it('kategori tanpa produk tidak ditampilkan', function () {
    // Katalog hanya berisi satu produk AI; kategori lain tidak punya apa pun.
    Product::query()->delete();
    Product::create(['nama_akun' => 'Chat Gpt Plus Sharing', 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);

    $label = collect(KategoriBeranda::tersedia())->pluck('label');

    expect($label)->toContain('AI Tools')
        ->and($label)->not->toContain('Jurnal & Riset')
        ->and($label)->not->toContain('Desain & Kreatif');
});

it('produk yang dijeda tidak ikut dihitung', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000, 'dijeda' => true]);

    expect(collect(KategoriBeranda::tersedia())->pluck('label'))->not->toContain('Desain & Kreatif');
});

it('jumlah yang dijanjikan chip sama dengan isi halaman tujuannya', function () {
    Product::query()->delete();

    foreach (['Chat Gpt Plus Sharing', 'Chat Gpt Plus Private', 'Gemini Advance'] as $nama) {
        Product::create(['nama_akun' => $nama, 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);
    }

    $ai = collect(KategoriBeranda::tersedia())->firstWhere('kunci', 'ai-tools');

    // Angka pada chip dan hasil penyaring halaman /shop berasal dari satu
    // sumber yang sama; kalau keduanya berpisah, salah satunya pasti berbohong.
    $isiHalaman = KategoriBeranda::saring(Product::query(), KategoriBeranda::kata('ai-tools'))->count();

    expect($ai['jumlah'])->toBe(3)->and($isiHalaman)->toBe(3);
});

it('kunci kategori yang tidak dikenal tidak menyaring apa pun', function () {
    expect(KategoriBeranda::kata('kategori-karangan'))->toBeNull();
});

it('nama merek dipendekkan jadi nama yang dikenali pengunjung', function () {
    expect(MerekDipercaya::merek('Chat Gpt Plus Sharing'))->toBe('ChatGPT')
        ->and(MerekDipercaya::merek('Chat Gpt Plus Private'))->toBe('ChatGPT')
        ->and(MerekDipercaya::merek('Scopus Lisensi + Scopus AI Private'))->toBe('Scopus')
        ->and(MerekDipercaya::merek('QuillBot Premium'))->toBe('QuillBot')
        ->and(MerekDipercaya::merek('Grammarly Premium'))->toBe('Grammarly');
});

it('produk yang namanya hanya imbuhan tidak menghasilkan merek kosong', function () {
    expect(MerekDipercaya::merek('Akun Premium'))->toBe('');
});

it('logo merek tidak mengulang nama yang sudah tercetak di sebelahnya', function () {
    // Diuji dari SUMBER, bukan dari halaman: pita merek hanya dirender bila
    // berkas gambar produknya benar-benar ada di penyimpanan, dan basis data
    // uji tidak punya berkas apa pun.
    $blade = file_get_contents(
        resource_path('views/livewire/pages/public/homepage/partials/dipercaya.blade.php')
    );

    // Nama merek sudah tercetak dalam <span> tepat di sebelah gambarnya.
    // Mengisi alt dengan nama yang sama membuat pembaca layar melafalkannya
    // dua kali ("ChatGPT ChatGPT") dan teks yang disalin ikut terbawa dobel.
    expect($blade)->toContain('alt="" aria-hidden="true"')
        ->and($blade)->not->toContain("alt=\"{{ \$m['nama'] }}\"");
});

it('tembolok kategori tidak menyimpan alamat lengkap', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Chat Gpt Plus Sharing', 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);

    KategoriBeranda::tersedia();

    // route() menghasilkan alamat lengkap berikut host dan portnya. Ikut
    // disimpan di tembolok, alamat itu membeku: tembolok yang terisi saat
    // aplikasi berjalan di localhost:8000 terus mengirim pengunjung ke sana
    // meski aplikasinya sudah pindah — dan tautan kategori berujung 404.
    //
    // Bahayanya sama di server: tembolok yang dihangatkan lewat CLI atau lewat
    // permintaan dengan host berbeda mengunci seluruh tautan ke host yang
    // salah, dan tak ada yang menyadarinya sampai ada yang mengklik.
    foreach (Cache::get('beranda.kategori') as $baris) {
        expect($baris)->not->toHaveKey('url');
    }
});

it('alamat kategori mengikuti host permintaan saat itu', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Chat Gpt Plus Sharing', 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);

    $ai = collect(KategoriBeranda::tersedia())->firstWhere('kunci', 'ai-tools');

    expect($ai['url'])->toStartWith(url('/'))->toContain('kategori=ai-tools');
});

it('halaman shop menampilkan kategori yang sedang menyaring', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Chat Gpt Plus Sharing', 'tipe_akun' => 'sharing', 'harga_perbulan' => 35000]);
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    // Penyaring yang bekerja diam-diam sama saja dengan halaman yang kehilangan
    // barang: daftar menyusut tanpa keterangan, dan satu-satunya jalan keluar
    // adalah menyunting alamat sendiri.
    $this->get('/shop?kategori=ai-tools')
        // Dicari lewat atribut class yang lengkap: nama kelasnya juga muncul
        // di blok <style> yang selalu dirender, jadi mencarinya begitu saja
        // akan selalu ketemu dan ujinya tidak menguji apa pun.
        ->assertSee('class="shop-aktif-chip"', false)
        ->assertSee('AI Tools', false)
        ->assertSee('Chat Gpt Plus Sharing', false)
        ->assertDontSee('Canva Premium', false);
});

it('tanpa kategori, chip penyaring tidak muncul', function () {
    Product::query()->delete();
    Product::create(['nama_akun' => 'Canva Premium', 'tipe_akun' => 'sharing', 'harga_perbulan' => 15000]);

    $this->get('/shop')->assertDontSee('class="shop-aktif-chip"', false);
});

it('kategori produk dikenali dari namanya untuk mewarnai kartunya', function () {
    expect(KategoriBeranda::untukProduk('Chat Gpt Plus Sharing')['label'])->toBe('AI Tools')
        ->and(KategoriBeranda::untukProduk('Canva Premium')['label'])->toBe('Desain & Kreatif')
        ->and(KategoriBeranda::untukProduk('Microsoft Office 365')['label'])->toBe('Produktivitas');
});

it('produk yang tidak masuk kategori mana pun tidak dipaksakan', function () {
    // Dipaksa masuk kategori terdekat, kartunya akan berwarna dan berlabel
    // salah — dan label yang salah lebih merugikan daripada tidak ada label.
    expect(KategoriBeranda::untukProduk('Produk Tanpa Nama Merek'))->toBeNull()
        ->and(KategoriBeranda::untukProduk(''))->toBeNull()
        ->and(KategoriBeranda::untukProduk(null))->toBeNull();
});

it('label kategori berada di dalam area gambar, bukan menimpa lencana promo', function () {
    // Lencana promo menempati pojok kiri ATAS area gambar (top:12 left:12 di
    // public-custom-styles.css). Label kategori harus berada di dalam area
    // gambar yang sama supaya bisa menempel tepi bawahnya — di luar itu, ia
    // kembali ke pojok kartu dan menimpa lencana promo begitu produknya
    // sedang berpromo.
    $blade = file_get_contents(
        resource_path('views/livewire/pages/public/shop-page/index.blade.php')
    );

    $media = mb_strpos($blade, 'class="fs-card-media"');
    $kat = mb_strpos($blade, 'class="shop-kat"');
    $badge = mb_strpos($blade, 'class="fs-badge fs-badge-flash"');

    expect($kat)->toBeGreaterThan($media)
        ->and($kat)->toBeLessThan($badge)
        // Berseberangan dengan lencana promo yang di pojok KIRI atas.
        ->and($blade)->toContain('.shop-kat {
            position: absolute; top: 10px; right: 10px;');
});
