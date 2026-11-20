<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabPenanda;
use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Dua admin menyusun satu RAB bersamaan.
 *
 * Sebelum ini, yang menyimpan belakangan menang tanpa suara. A mengubah biaya
 * per orang dari Rp 900.000 jadi Rp 1.000.000; B — yang layarnya masih
 * memegang angka lama — menyentuh apa pun, dan seluruh angka A kembali ke
 * Rp 900.000. Tidak ada galat di layar siapa pun, dan yang menemukannya
 * biasanya pelanggan yang sudah menerima penawaran.
 *
 * Penjagaannya dua lapis, dan keduanya diuji di sini:
 *
 *   1. SETIAP perubahan dari lemon membawa nomor versi yang sedang terbaca
 *      di layar. Orcha menolak dengan 409 kalau nomornya sudah bukan yang
 *      terbaru. Ini lapisan yang menjamin datanya; ia bekerja walau
 *      JavaScript mati sama sekali.
 *   2. Pengintip kecil menanyakan nomor versinya tiap 10 detik, supaya layar
 *      yang basi ketahuan SEBELUM admin mengetik di atasnya.
 */
/**
 * Rekaman RAB, dibaca sendiri di berkas ini.
 *
 * rekamanRab() yang sudah ada hidup di OrchaRabTest.php, dan pembantu yang
 * hidup di satu berkas uji hanya terbaca berkas lain bila kebetulan dimuat
 * lebih dulu. Menjalankan berkas ini sendirian lalu gagal dengan "Call to
 * undefined function" adalah kegagalan yang tidak menyebut sebabnya — sudah
 * terjadi, dan tests/Pest.php memperingatkannya.
 */
function rekamanRabBersama(string $nama): array
{
    return json_decode(file_get_contents(base_path("tests/Fixtures/orcha-rab/{$nama}.json")), true);
}

function palsukanRabVersi(int $versi = 4, array $penanda = []): void
{
    $rab = rekamanRabBersama('show');
    $rab['data']['versi'] = $versi;
    $rab['data']['versi_oleh'] = 'Berto';

    Http::fake(function (Request $r) use ($rab, $penanda) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_ends_with($jalur, '/penanda') => Http::response(['data' => $penanda ?: [
                'versi' => 4, 'oleh' => 'Berto', 'pada_teks' => 'baru saja',
            ]]),
            str_contains($jalur, '/rab/katalog') => Http::response(rekamanRabBersama('katalog')),
            default => Http::response($rab),
        };
    });
}

test('setiap perubahan membawa nomor versi yang sedang terbaca', function () {
    palsukanRabVersi(4);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaBaris.21', '50.000');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && (int) $r['versi'] === 4);
});

test('penghapusan membawa versinya lewat query, karena DELETE tak punya badan', function () {
    palsukanRabVersi(4);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('hapusBiaya', 21);

    Http::assertSent(fn (Request $r) => $r->method() === 'DELETE'
        && str_contains($r->url(), '/biaya/21')
        && str_contains($r->url(), 'versi=4'));
});

test('Orcha yang belum dideploy tidak dikirimi versi sama sekali', function () {
    /*
     | Penandanya hanya dikirim kalau Orcha sendiri yang menyebutkannya.
     |
     | Server yang belum naik tidak punya kolom versi dan tidak mengirim
     | kuncinya. Mengirimkan `versi` ke sana tetap aman untuk PATCH, tetapi
     | nilainya pasti tidak cocok dengan apa pun — dan kalau suatu hari Orcha
     | memvalidasi medan yang tak dikenalnya, seluruh layar RAB mati di
     | server lama sementara di komputer pengembang berjalan mulus.
     */
    // Rekaman apa adanya: TANPA kunci versi, seperti Orcha di produksi hari ini.
    $rab = rekamanRabBersama('show');

    Http::fake(function (Request $r) use ($rab) {
        return str_contains((string) parse_url($r->url(), PHP_URL_PATH), '/rab/katalog')
            ? Http::response(rekamanRabBersama('katalog'))
            : Http::response($rab);
    });

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaBaris.21', '50.000');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        // array_key_exists, bukan $r['versi']: Request::offsetGet melempar
        // "Undefined array key" untuk kunci yang memang TIDAK ADA, dan itulah
        // justru yang dijaga di sini.
        && ! array_key_exists('versi', $r->data()));
});

test('penolakan 409 menyampaikan ALASANNYA, bukan nomor kodenya', function () {
    $pesan = 'RAB ini baru saja diubah oleh Rina (2 menit yang lalu). '
        .'Layar Anda dimuat ulang supaya perubahannya ikut terbaca.';

    $rab = rekamanRabBersama('show');
    $rab['data']['versi'] = 4;

    Http::fake(function (Request $r) use ($rab, $pesan) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_contains($jalur, '/rab/katalog') => Http::response(rekamanRabBersama('katalog')),
            $r->method() === 'PATCH' => Http::response(['pesan' => $pesan, 'versi' => 7], 409),
            default => Http::response($rab),
        };
    });

    /*
     | Kalimat Orcha diteruskan apa adanya. Sebelum 409 ikut diloloskan di
     | OrchaClient, yang sampai ke admin cuma "Orcha membalas dengan kode
     | 409" — dan admin yang tidak tahu artinya akan menekan Simpan lagi,
     | ditolak lagi, tanpa pernah tahu ada orang lain di RAB yang sama.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->set('hargaBaris.21', '50.000')
        ->assertDispatched('toast-error', message: $pesan);
});

test('pengintip mengabarkan perubahan dari ORANG LAIN', function () {
    palsukanRabVersi(4, ['versi' => 5, 'oleh' => 'Rina', 'pada_teks' => 'baru saja']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabPenanda::class, ['rabId' => 3, 'versi' => 4])
        ->call('periksa')
        ->assertSet('berubah', true)
        ->assertSet('oleh', 'Rina')
        ->assertSee('Diubah oleh Rina')
        ->assertDispatched('rab-diubah-orang-lain');
});

test('perubahan SAYA SENDIRI tidak memunculkan bilah', function () {
    $saya = adminRab();

    palsukanRabVersi(4, ['versi' => 5, 'oleh' => $saya->name, 'pada_teks' => 'baru saja']);

    /*
     | Menyimpan apa pun menaikkan versinya, jadi pengintip di tab saya
     | sendiri pasti melihat nomornya berubah. Layar saya sudah memegang
     | data terbaru dari jawaban simpanan itu; bilah "Diubah oleh Berto" di
     | layar Berto cuma membuatnya ragu apakah simpanannya jadi atau tidak.
     |
     | Nomornya tetap WAJIB dicatat: kalau tidak, pengintipan berikutnya
     | masih membandingkan dengan nomor lama dan akan mengabarkannya lagi
     | dan lagi, tiap 10 detik.
     */
    Livewire::actingAs($saya)
        ->test(OrchaRabPenanda::class, ['rabId' => 3, 'versi' => 4])
        ->call('periksa')
        ->assertSet('berubah', false)
        ->assertSet('versi', 5)
        ->assertNotDispatched('rab-diubah-orang-lain');
});

test('pengintipan BERHENTI selama bilahnya berdiri', function () {
    palsukanRabVersi(4, ['versi' => 5, 'oleh' => 'Rina', 'pada_teks' => 'baru saja']);

    /*
     | Sesudah tahu datanya berubah, menanyakannya lagi tiap 10 detik tidak
     | menambah apa pun. Hosting ini pernah menjatuhkan seluruh aplikasi
     | karena poller yang tak pernah diam di banyak tab sekaligus.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabPenanda::class, ['rabId' => 3, 'versi' => 4])
        ->assertSeeHtml('wire:poll.10s')
        ->call('periksa')
        ->assertDontSeeHtml('wire:poll.10s');
});

test('Orcha tanpa jalur penanda tidak membuat layar mengintip terus', function () {
    Http::fake(['*' => Http::response(['message' => 'Not Found'], 404)]);

    /*
     | Versi 0 berarti Orcha belum mengirimkan penandanya — server yang
     | belum dideploy. Fiturnya cuma belum ada di sana; itu bukan kegagalan
     | yang perlu dikabarkan ke admin, dan bukan alasan untuk menanyakan
     | jalur yang pasti 404 tiap 10 detik.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabPenanda::class, ['rabId' => 3, 'versi' => 0])
        ->assertDontSeeHtml('wire:poll.10s')
        ->call('periksa')
        ->assertSet('berubah', false)
        ->assertNotDispatched('rab-diubah-orang-lain');
});

test('bilahnya TETAP berdiri kalau pemuatan ulangnya gagal', function () {
    $rab = rekamanRabBersama('show');
    $rab['data']['versi'] = 4;

    // Pemuatan ulangnya yang gagal, BUKAN pemuatan pertamanya: layarnya harus
    // sempat berdiri dulu dengan data yang baik, seperti keadaan nyatanya.
    $pengambilan = 0;

    Http::fake(function (Request $r) use ($rab, &$pengambilan) {
        $jalur = (string) parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rab/katalog')) {
            return Http::response(rekamanRabBersama('katalog'));
        }

        if (str_ends_with($jalur, '/rab/3') && $r->method() === 'GET') {
            $pengambilan++;

            if ($pengambilan > 1) {
                return Http::response([], 500);
            }
        }

        return Http::response($rab);
    });

    /*
     | Bilah yang hilang adalah pernyataan bahwa layar ini sudah mutakhir.
     | Kalau Orcha sedang tidak bisa dihubungi, angkanya masih yang lama —
     | dan admin yang melihat bilahnya hilang akan menyunting di atas data
     | basi dengan yakin. Itu justru keadaan yang paling berbahaya di sini.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('muatUlangDariPenanda')
        ->assertNotDispatched('rab-sudah-dimuat');
});

test('pemuatan ulang yang berhasil membereskan bilahnya', function () {
    palsukanRabVersi(7);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->call('muatUlangDariPenanda')
        ->assertDispatched('rab-sudah-dimuat', versi: 7);
});

test('kabar rab-sudah-dimuat menurunkan bilahnya', function () {
    palsukanRabVersi(4, ['versi' => 5, 'oleh' => 'Rina', 'pada_teks' => 'baru saja']);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabPenanda::class, ['rabId' => 3, 'versi' => 4])
        ->call('periksa')
        ->assertSet('berubah', true)
        ->dispatch('rab-sudah-dimuat', versi: 6)
        ->assertSet('berubah', false)
        ->assertSet('versi', 6)
        ->assertDontSee('Diubah oleh');
});

test('layar susun memasang pengintipnya dengan versi dari Orcha', function () {
    palsukanRabVersi(4);

    /*
     | Komponennya WAJIB benar-benar terpasang di layar susun.
     |
     | Seluruh penjagaan sisi peramban bergantung pada ini. Komponen yang
     | ada tetapi tidak dipasang tidak memberi gejala apa pun: layarnya
     | tampil normal, ujinya sendiri hijau, dan satu-satunya yang hilang
     | adalah kabar bahwa orang lain sedang menyunting RAB yang sama.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 3])
        ->assertSeeHtml('wire:poll.10s');
});
