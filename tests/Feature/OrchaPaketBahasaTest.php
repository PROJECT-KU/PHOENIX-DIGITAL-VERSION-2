<?php

use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

/**
 * Bahasa layar Paket Wisata harus terbaca admin yang bukan orang teknis.
 *
 * Yang dijaga bukan selera kalimat, melainkan tiga cara layar ini berhenti
 * bisa dipakai tanpa satu pun galat: istilah Inggris yang tidak punya padanan
 * di kepala pemakainya, contoh isian berbahasa Inggris yang lalu ditiru
 * mentah-mentah, dan pesan galat bawaan Laravel yang keluar dalam Bahasa
 * Inggris justru pada saat orangnya sedang salah.
 */
function bukaBorangPaket(): string
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'kategori_paket' => ['open_trip' => 'Open Trip'],
            'status_paket' => ['terbit' => 'Terbit', 'draf' => 'Draf'],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    return test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/tambah')->assertOk()->getContent();
}

/**
 * Hanya BORANGNYA, bukan seluruh halaman.
 *
 * Bilah samping admin berdiri di setiap halaman dan memuat menu "RAB &
 * Itinerary" — nama fitur lain. Memeriksa seluruh respons karena itu selalu
 * merah, dan merahnya menunjuk ke kata yang tidak ada hubungannya dengan
 * layar ini.
 */
function isiBorangPaket(): string
{
    $isi = bukaBorangPaket();
    $mulai = strpos($isi, '<form wire:submit="simpan"');

    expect($mulai)->not->toBeFalse('Borang paket tidak ditemukan di halaman.');

    return substr($isi, $mulai, strrpos($isi, '</form>') - $mulai);
}

test('tidak ada istilah asing yang tertinggal di borang paket', function () {
    $markah = isiBorangPaket();

    foreach ([
        'Itinerary' => 'judul kartu jadwal kegiatan',
        'Day 1' => 'contoh isian nama hari',
        'meeting point' => 'contoh isian kegiatan',
    ] as $asing => $di) {
        // assertStringNotContainsString, BUKAN expect()->not->toContain():
        // argumen kedua toContain() adalah nilai kedua yang ikut dicari,
        // bukan pesan galat — kalimat penjelasnya sendiri lalu ikut diuji.
        test()->assertStringNotContainsString(
            $asing, $markah, "Istilah asing '$asing' masih tertulis di $di."
        );
    }

    // Padanannya memang ada, bukan sekadar istilah asingnya yang dibuang.
    expect($markah)
        ->toContain('Jadwal Kegiatan')
        ->toContain('Hari 1')
        ->toContain('titik kumpul');
});

test('isian memakai kata sehari-hari, bukan istilah sistem', function () {
    /*
     | Diperiksa atas SELURUH halaman, tidak dikupas blok gayanya.
     |
     | Label seperti "Lama trip" tidak mungkin muncul di dalam CSS, jadi
     | pengupasan tidak diperlukan — dan pengupasan itu sendiri rapuh di
     | halaman ini: ada 9 pembuka <style> tetapi hanya 8 penutup, sehingga
     | regex pasangannya bisa ikut menelan markah yang sah.
     */
    $markah = isiBorangPaket();

    foreach ([
        'Lama trip' => 'dulu "Durasi"',
        'Jumlah kursi' => 'dulu "Kuota kursi"',
        'Tampil di Website' => 'dulu "Penayangan"',
        'Mulai tampil' => 'dulu "Mulai tayang"',
        'Berhenti tampil' => 'dulu "Berhenti tayang"',
        'Tempat yang Dikunjungi' => 'dulu "Destinasi yang Dikunjungi"',
    ] as $kata => $asal) {
        test()->assertStringContainsString(
            $kata, $markah, "Label '$kata' ($asal) hilang dari borang."
        );
    }
});

test('pesan galat keluar dalam Bahasa Indonesia, bukan bawaan Laravel', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    /*
     | APP_LOCALE=en di server dan proyek ini tidak punya folder lang/, jadi
     | tanpa messages() yang terbaca admin adalah "The nama paket field is
     | required." — kalimat yang muncul tepat ketika orangnya sedang bingung.
     */
    $galat = Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class)
        ->set('nama', '')
        ->set('harga', '')
        // minimalPeserta bertipe int di komponennya, jadi ia tidak bisa
        // dikosongkan — yang bisa dilanggar batas bawahnya.
        ->set('minimalPeserta', 0)
        ->call('simpan')
        ->assertHasErrors(['nama', 'harga', 'minimalPeserta'])
        ->errors()->all();

    $semua = implode(' | ', $galat);

    expect($semua)
        ->not->toContain('The ')
        ->not->toContain('field is required')
        ->not->toContain('must be')
        ->and($semua)->toContain('belum diisi');
});

test('kuota lebih kecil dari minimal peserta dijelaskan akibatnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    // Pesan yang cuma bilang "kuota gte minimal peserta" tidak memberi tahu
    // apa pun. Yang perlu diketahui admin: tripnya tidak akan pernah berangkat.
    $galat = Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class)
        ->set('nama', 'Open Trip Uji')
        ->set('harga', 500000)
        ->set('minimalPeserta', 10)
        ->set('kuota', 4)
        ->call('simpan')
        ->assertHasErrors(['kuota'])
        ->errors()->get('kuota');

    expect(implode(' ', $galat))->toContain('tidak akan pernah bisa berangkat');
});

test('daftar paket memakai kata yang sama dengan borangnya', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*/paket-wisata*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    // Satu layar menyebut "tayang" dan layar sebelahnya "tampil" membuat admin
    // mengira keduanya dua hal yang berbeda.
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata')->assertOk()->getContent();

    $markah = preg_replace('/<style\b[^>]*>.*?<\/style>/s', '', $isi);

    expect($markah)
        ->toContain('tampil di website')
        ->toContain('Belum ada paket wisata');
});

test('tanggal yang harga atau kuotanya sendiri terlihat berbeda', function () {
    Http::fake([
        '*/rujukan' => Http::response(['data' => ['kategori_paket' => ['open_trip' => 'Open Trip']]]),
        '*/paket-wisata/7' => Http::response(['data' => [
            'id' => 7, 'nama' => 'Open Trip Uji', 'kategori' => 'open_trip', 'status' => 'terbit',
            'harga' => 1300000, 'minimal_peserta' => 4, 'kuota' => 20,
            'destinasi' => [], 'fasilitas' => [], 'itinerary' => [], 'itinerary_teks' => '',
            'keberangkatan' => [
                // Ikut paket: tidak ada angka sendiri.
                ['id' => 1, 'rentang_tanggal' => '27 – 29 November 2026',
                 'tanggal_berangkat' => '2026-11-27', 'tanggal_pulang' => '2026-11-29',
                 'kuota' => null, 'price' => null, 'harga_dipakai' => 1300000, 'kuota_dipakai' => 20,
                 'kursi_terpakai' => 0, 'sisa_kursi' => 20, 'penuh' => false, 'aktif' => true],
                // Punya harga & kuota sendiri.
                ['id' => 2, 'rentang_tanggal' => '4 – 6 Desember 2026',
                 'tanggal_berangkat' => '2026-12-04', 'tanggal_pulang' => '2026-12-06',
                 'kuota' => 12, 'price' => 1450000, 'harga_dipakai' => 1450000, 'kuota_dipakai' => 12,
                 'kursi_terpakai' => 12, 'sisa_kursi' => 0, 'penuh' => true, 'aktif' => true],
            ],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');

    /*
     | Kuncinya 'price', bukan 'harga' — dan 'harga' tidak pernah dikirim Orcha.
     | Selama borangnya membaca kunci yang salah, lencana ini TIDAK PERNAH
     | tergambar: tanggal yang harganya memang berbeda terlihat persis sama
     | dengan yang ikut harga paket, tanpa satu pun galat.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    // Diiris dari kartunya sendiri. Batasnya nama kartu berikutnya, bukan
    // label isian — label bisa pindah kartu, judul kartu tidak.
    $mulai = strpos($isi, 'Tanggal Keberangkatan');

    expect($mulai)->not->toBeFalse('Kartu Tanggal Keberangkatan tidak ditemukan.');

    $kotak = substr($isi, $mulai, strpos($isi, 'Harga</h3>', $mulai) - $mulai);

    expect($kotak)
        ->toContain('1.450.000')
        ->toContain('harga sendiri')
        ->toContain('12 kursi')
        ->toContain('kuota sendiri')
        // Terjual habis itu kabar baik — hijau, sama dengan daftar paketnya.
        ->toContain('Terisi penuh')
        ->not->toContain('bg-danger');

    // Yang ikut paket TIDAK diberi lencana angka sendiri.
    expect(substr_count($kotak, 'harga sendiri'))->toBe(1)
        ->and(substr_count($kotak, 'kuota sendiri'))->toBe(1);
});
