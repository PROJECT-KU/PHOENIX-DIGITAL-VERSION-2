<?php

use App\Models\Product;
use App\Models\Promo;

/**
 * Etalase flash sale di beranda.
 *
 * Yang dijaga: promo yang sedang berjalan harus jadi PEMBUKA halaman, dan
 * angka potongannya harus benar. Diskon nominal pernah tampil sebagai
 * "Diskon sampai 25.000%" karena tanda persen ditempelkan ke rupiah — kesalahan
 * yang tidak terlihat di kode tapi langsung terbaca oleh pengunjung.
 */
function promoBerjalan(array $ubah = []): Promo
{
    return Promo::create(array_merge([
        'nama_promo' => 'Pay Day Sale',
        'badge_text' => 'Flash Sale',
        'deskripsi' => 'Promo terbatas, jangan sampai terlewat!',
        'tipe_promo' => 'flash_sale',
        'tipe_diskon' => 'nominal',
        'diskon_member_nominal' => 25000,
        'diskon_non_member_nominal' => 25000,
        'mulai_promo' => now()->subHour(),
        'selesai_promo' => now()->addHours(10),
        'is_active' => true,
        'show_on_homepage' => true,
        'prioritas' => 1,
    ], $ubah));
}

function produkPromo(Promo $promo, string $nama = 'Scopus Lisensi + Scopus AI Sharing'): Product
{
    $p = Product::create([
        'nama_akun' => $nama, 'tipe_akun' => 'sharing', 'harga_perbulan' => 70000,
        'deskripsi' => 'Akses Scopus resmi dengan fitur AI untuk riset dan publikasi ilmiah.',
    ]);

    $promo->products()->attach($p->id);

    return $p;
}

it('potongan nominal ditulis sebagai rupiah, bukan persen', function () {
    produkPromo(promoBerjalan());

    $this->get('/')
        ->assertSee('Hemat', false)
        ->assertSee('Rp25.000', false)
        ->assertDontSee('25.000%', false);
});

it('potongan persen tetap ditulis sebagai persen', function () {
    produkPromo(promoBerjalan([
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => 30,
        'diskon_member_nominal' => 0,
        'diskon_non_member_nominal' => 0,
    ]));

    $this->get('/')->assertSee('30%', false);
});

it('banner tetap pembuka halaman, promo menyusul di bawahnya', function () {
    produkPromo(promoBerjalan());

    $isi = $this->get('/')->getContent();

    // Banner satu-satunya bagian beranda yang isinya diatur admin lewat panel,
    // dan di situlah kabar terpenting hari itu dipasang. Urutan ini diuji
    // karena ia pernah dibalik dua kali; tanpa penjaga, ia akan terbalik lagi
    // pada perubahan berikutnya tanpa ada yang menyadarinya.
    expect(strpos($isi, 'id="hero"'))->toBeLessThan(strpos($isi, 'id="call-to-action"'));
});

it('pengumuman dan produknya berada dalam satu kartu', function () {
    produkPromo(promoBerjalan());

    // Dipisah jadi dua kartu, pembeli tidak otomatis tahu bahwa produk di bawah
    // adalah yang kena promo di atas — hubungan itu lalu harus dijelaskan
    // dengan kalimat, dan kalimat penjelas selalu lebih lemah daripada susunan
    // yang sudah menjelaskan dirinya sendiri.
    $isi = $this->get('/')
        ->assertSee('Berakhir dalam', false)
        ->assertSee('Produk yang ikut promo', false)
        ->getContent();

    // Dicari lewat atribut class yang lengkap, bukan nama kelasnya saja:
    // nama kelas juga muncul di blok <style> yang berada jauh di atas markup,
    // dan mencocokkannya begitu saja membuat urutannya selalu salah.
    //
    // Penanda penutupnya TIDAK bisa berupa komentar Blade — komentar dibuang
    // saat dikompilasi dan tidak pernah sampai ke HTML. Yang dipakai: .fsx-isi
    // hanya dirender di dalam .fsx-hero, jadi keberadaannya sesudah pembuka
    // kartu sudah membuktikan produknya berada di dalam kartu yang sama.
    $kartu = strpos($isi, 'class="fsx-hero"');
    $isiKartu = strpos($isi, 'class="fsx-isi"');
    $produk = strpos($isi, 'featured-products-row fsx-deret-produk');

    expect($isiKartu)->toBeGreaterThan($kartu)
        ->and($produk)->toBeGreaterThan($isiKartu);
});

it('tanpa promo berjalan, beranda tidak menyisakan pita kosong', function () {
    // Tidak ada promo sama sekali: komponennya harus benar-benar tidak
    // merender apa pun, bukan merender wadah kosong berlatar warna.
    $this->get('/')
        ->assertDontSee('Rekomendasi Hari Ini', false)
        ->assertDontSee('fsx-hero', false);
});

it('jaminan di hero dan panel penutup tidak mengulang kalimat yang sama', function () {
    $isi = $this->get('/')->getContent();

    // Empat kalimat identik dua kali dalam satu halaman membuat keduanya
    // berhenti dibaca; itulah keluhan yang pernah muncul soal pita ganda.
    // Jaminan kini hanya hidup di kaki hero, panel penutup memakai kalimat
    // yang berbeda.
    expect(substr_count($isi, 'Garansi uang kembali'))->toBe(1)
        ->and(substr_count($isi, 'Lisensi resmi &amp; legal'))->toBe(1);
});

it('bilah kuota tidak muncul bila kuotanya tidak dipasang', function () {
    produkPromo(promoBerjalan(['kuota' => null, 'total_penggunaan' => 164]));

    // Kelangkaan berangka karangan adalah kebohongan yang paling menggoda
    // dibuat di halaman promo, dan paling merusak begitu ketahuan. Bilahnya
    // hanya boleh ada kalau admin benar-benar memasang kuotanya.
    //
    // Diperiksa lewat atribut class, bukan kata "kuota": kata itu juga muncul
    // di nama kelas CSS dan komentarnya, jadi mencarinya begitu saja akan
    // selalu ketemu dan ujinya tidak pernah benar-benar menguji apa pun.
    $this->get('/')->assertDontSee('class="fsx-kuota"', false);
});

it('bilah kuota menghitung sisa dari kuota yang benar-benar dipasang', function () {
    produkPromo(promoBerjalan(['kuota' => 200, 'total_penggunaan' => 164]));

    $this->get('/')
        ->assertSee('class="fsx-kuota"', false)
        ->assertSee('Sisa', false)
        // 200 - 164 = 36 tersisa, dan bilahnya terisi 82%.
        ->assertSee('36', false)
        ->assertSee('width: 82%', false);
});

it('kaki promo hanya menyebut angka yang ada di basis data', function () {
    produkPromo(promoBerjalan(['total_penggunaan' => 164]));

    $this->get('/')
        ->assertSee('1 produk ikut promo', false)
        ->assertSee('164 kali sudah dipakai', false)
        // min_pembelian 0 tidak disebut: syarat yang tidak ada tidak boleh
        // ditampilkan seolah-olah ada.
        ->assertDontSee('min. belanja', false);
});

it('kata "sampai" hanya dipakai bila potongannya memang berbeda', function () {
    // Member dan non-member sama-sama 17%: setiap pembeli pasti mendapat angka
    // yang tertulis, jadi "sampai" adalah pagar tanpa isi yang membuat tawaran
    // terdengar lebih ragu daripada kenyataannya.
    produkPromo(promoBerjalan([
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => 17,
        'diskon_non_member_persen' => 17,
        'diskon_member_nominal' => 0,
        'diskon_non_member_nominal' => 0,
    ]));

    $this->get('/')->assertDontSee('Diskon sampai', false)->assertSee('17%', false);
});

it('kata "sampai" muncul lagi bila member dapat lebih besar', function () {
    produkPromo(promoBerjalan([
        'tipe_diskon' => 'persen',
        'diskon_member_persen' => 25,
        'diskon_non_member_persen' => 10,
        'diskon_member_nominal' => 0,
        'diskon_non_member_nominal' => 0,
    ]));

    // Di sini "sampai" benar: hanya sebagian pembeli yang mendapat 25%.
    $this->get('/')->assertSee('Diskon sampai', false);
});

it('menyebut siapa yang berhak, tepat di bawah angkanya', function () {
    produkPromo(promoBerjalan(['untuk_member' => 'member_only']));

    // Pertanyaan "saya dapat tidak?" muncul persis setelah orang melihat angka
    // diskon; menjawabnya di tempat itu lebih berguna daripada membiarkannya
    // mencari sendiri di syarat & ketentuan.
    $this->get('/')->assertSee('khusus member', false);
});

it('label di garis atas tetap pendek meski badge_text sepanjang kalimat', function () {
    produkPromo(promoBerjalan(['badge_text' => 'Rayakan Kemerdekaan, Belanja Makin Hemat!']));

    // Label itu legend kartu: tugasnya memberi tahu JENIS kartunya dalam
    // sekali lihat. Kalimat sepanjang itu, dengan huruf kapital berjarak
    // lebar, melebar sampai 478px dan berhenti terbaca sebagai label — ia
    // mulai terbaca sebagai judul kedua yang menyaingi judul aslinya.
    $this->get('/')
        ->assertDontSee('Rayakan Kemerdekaan', false)
        ->assertSee('fsx-pita-atas', false)
        ->assertSee('Flash Sale', false);
});

it('badge_text yang memang sependek label tetap dipakai', function () {
    produkPromo(promoBerjalan(['badge_text' => 'Promo Kilat']));

    $this->get('/')->assertSee('Promo Kilat', false);
});
