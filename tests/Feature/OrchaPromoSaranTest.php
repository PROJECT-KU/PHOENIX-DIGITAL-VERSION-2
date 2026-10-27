<?php

use App\Livewire\Pages\Admin\Orcha\Promo\OrchaPromoList;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Batas aman promo rombongan, dihitung Orcha dan ditampilkan saat mengisi.
 *
 * Angka promo selama ini ditebak — "lima persen terdengar wajar". Tebakan
 * itu aman pada paket bermargin tebal dan diam-diam merugikan pada yang
 * tipis, padahal tingkatnya berlaku untuk SELURUH paket.
 */
function palsukanSaranPromo(array $saran = []): void
{
    Http::fake(function (Request $r) use ($saran) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_ends_with($jalur, '/promo-rombongan/saran')) {
            return Http::response(['data' => array_merge([
                'bisa_dihitung' => true,
                'min_peserta' => 6,
                'jumlah_rekan' => 5,
                'paket' => 'Open Trip Banyuwangi',
                'jual' => 1_000_000,
                'jual_teks' => 'Rp 1.000.000',
                'margin_per_orang' => 100_000,
                'margin_per_orang_teks' => 'Rp 100.000',
                'untung_tambahan' => 500_000,
                'untung_tambahan_teks' => 'Rp 500.000',
                'persen_maksimal' => 50,
                'persen_saran' => 25,
                'potongan_saran_teks' => 'Rp 250.000',
                'gratis_aman' => false,
                'untung_sesudah_gratis' => -500_000,
                'untung_sesudah_gratis_teks' => 'Rp -500.000',
                'alasan' => 'Dihitung dari paket bermargin paling tipis yang ikut promo: Open Trip Banyuwangi.',
            ], $saran)]);
        }

        return Http::response(['data' => [], 'meta' => []]);
    });
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
});

test('batas aman diminta ke Orcha begitu minimal pesertanya diisi', function () {
    palsukanSaranPromo();

    Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '6');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/promo-rombongan/saran')
        && str_contains($r->url(), 'min_peserta=6'));
});

test('angka di bawah dua tidak dimintakan sarannya', function () {
    /*
     | Tingkat satu orang tidak punya arti — dan memanggil Orcha untuk tiap
     | ketikan di tengah jalan ("1" sebelum jadi "11") membebani jalur yang
     | sama dengan yang dipakai memuat daftarnya.
     */
    palsukanSaranPromo();

    Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '1');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/promo-rombongan/saran'));
});

test('tombol pakai saran mengisi angkanya sekaligus memilih bentuknya', function () {
    palsukanSaranPromo();

    Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '6')
        ->call('pakaiSaranPersen')
        ->assertSet('jenis', 'persen')
        ->assertSet('isian.potongan_persen', '25')
        // Kotak yang tidak relevan dikosongkan, supaya tidak ada sisa yang
        // ikut terkirim saat bentuknya berganti.
        ->assertSet('isian.gratis_orang', '');
});

test('gratis satu orang tidak bisa dipakai saat sistem bilang belum aman', function () {
    /*
     | Tombolnya memang tidak digambar; penjagaan di komponen ini untuk
     | jalur lain — panggilan langsung, atau markah yang tertinggal setelah
     | sarannya berubah.
     */
    palsukanSaranPromo(['gratis_aman' => false]);

    Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '6')
        ->call('pakaiSaranGratis')
        ->assertSet('jenis', 'persen')
        ->assertSet('isian.gratis_orang', '');
});

test('saran yang aman menawarkan gratis satu orang', function () {
    palsukanSaranPromo(['gratis_aman' => true, 'untung_sesudah_gratis_teks' => 'Rp 4.000.000']);

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '51');

    expect($layar->html())->toContain('Pakai gratis 1 orang');

    $layar->call('pakaiSaranGratis')
        ->assertSet('jenis', 'gratis')
        ->assertSet('isian.gratis_orang', '1')
        ->assertSet('isian.potongan_persen', '');
});

test('Orcha yang tidak terjangkau tidak menghentikan admin mengisi formulir', function () {
    /*
     | Saran yang gagal dimuat bukan galat yang perlu menghentikan
     | pekerjaan — formulirnya tetap bisa diisi tangan.
     */
    Http::fake(fn () => Http::response([], 500));

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaPromoList::class)
        ->call('bukaTambah')
        ->set('isian.min_peserta', '6');

    $layar->assertSet('saran', [])->assertHasNoErrors();
});
