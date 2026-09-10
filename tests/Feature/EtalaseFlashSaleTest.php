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

it('etalase promo membawa kepala bagian rekomendasi', function () {
    produkPromo(promoBerjalan());

    $this->get('/')
        ->assertSee('Rekomendasi Hari Ini', false)
        ->assertSee('Berakhir dalam', false);
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
