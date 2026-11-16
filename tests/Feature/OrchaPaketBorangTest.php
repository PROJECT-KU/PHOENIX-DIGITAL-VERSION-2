<?php

use App\Livewire\Pages\Admin\Orcha\PaketWisata\OrchaPaketForm;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

require_once __DIR__.'/OrchaLengkapiPesertaTest.php';

/**
 * Fitur baru borang Paket Wisata.
 *
 * Yang dijaga di sini bukan bentuk markahnya melainkan apa yang SAMPAI ke
 * Orcha — dan apa yang tidak. Tiga di antaranya adalah cara data hilang
 * tanpa galat: galeri yang dikosongkan tapi tidak terkirim, harga per
 * tanggal yang tidak punya isian, dan titik jemput yang berubah bentuk saat
 * disatukan lagi.
 */
function isiPaketUji(array $ubah = []): array
{
    return array_merge([
        'id' => 7, 'nama' => 'Open Trip Kawah Ijen', 'kategori' => 'open_trip',
        'status' => 'terbit', 'harga' => 1_300_000, 'minimal_peserta' => 4, 'kuota' => 20,
        'titik_jemput' => 'Jogja, Klaten, Surakarta',
        'destinasi' => ['Kawah Ijen'], 'fasilitas' => ['Transport AC'],
        'tidak_termasuk' => ['Tiket masuk wisata'],
        'foto_galeri' => ['/storage/paket/a.webp', '/storage/paket/b.webp'],
        'itinerary' => [], 'itinerary_teks' => '',
        'tautan_publik' => 'https://orchajourney.com/paket/uuid-7',
        'tautan_pratinjau' => 'https://orchajourney.com/paket/uuid-7?signature=xyz',
        'keberangkatan' => [
            ['id' => 1, 'rentang_tanggal' => '27 – 29 November 2026',
             'tanggal_berangkat' => '2026-11-27', 'tanggal_pulang' => '2026-11-29',
             'kuota' => null, 'price' => null, 'harga_dipakai' => 1_300_000, 'kuota_dipakai' => 20,
             'kursi_terpakai' => 0, 'sisa_kursi' => 20, 'penuh' => false, 'aktif' => true],
            ['id' => 2, 'rentang_tanggal' => '4 – 6 Desember 2026',
             'tanggal_berangkat' => '2026-12-04', 'tanggal_pulang' => '2026-12-06',
             'kuota' => 12, 'price' => 1_450_000, 'harga_dipakai' => 1_450_000, 'kuota_dipakai' => 12,
             'kursi_terpakai' => 12, 'sisa_kursi' => 0, 'penuh' => true, 'aktif' => true],
        ],
    ], $ubah);
}

function palsukanBorangPaket(array $isi = []): void
{
    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'kategori_paket' => ['open_trip' => 'Open Trip'],
            'status_paket' => ['terbit' => 'Terbit', 'draf' => 'Draf'],
            'status_tayang' => ['tayang' => 'Tayang', 'draf' => 'Draf'],
        ]]),
        '*/saran*' => Http::response(['data' => [
            'destinasi' => [['id' => 1, 'nama' => 'Kawah Ijen']],
            'fasilitas' => [['id' => 2, 'nama' => 'Transport AC']],
            'tidak_termasuk' => [['id' => 3, 'nama' => 'Tiket masuk wisata']],
            'titik_jemput' => [['id' => 4, 'nama' => 'Jogja'], ['id' => 5, 'nama' => 'Magelang']],
        ]]),
        '*/paket-wisata/7' => Http::response(['data' => $isi ?: isiPaketUji()]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    cache()->forget('orcha.rujukan');
}

/* ===================== HARGA & KUOTA PER TANGGAL ===================== */

test('harga dan kuota per tanggal punya isiannya sendiri', function () {
    palsukanBorangPaket();

    /*
     | Basis data dan API sudah mendukung harga per tanggal, dan keterangan
     | di layar sudah menyebutnya — tetapi sebelum ini tidak ada satu pun
     | kotak untuk mengisinya. Tanggal yang lebih mahal hanya bisa diatur
     | lewat API, bukan dari layar.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    expect($isi)
        ->toContain('wire:model.blur="kbHargaTeks"')
        ->toContain('wire:model="kbKuota"');
});

test('harga dan kuota tanggal ikut terkirim saat disimpan', function () {
    palsukanBorangPaket();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('kbTanggal', '2026-12-11')
        ->set('kbHargaTeks', '1.450.000')
        ->set('kbKuota', '12')
        ->call('simpanKeberangkatan');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/keberangkatan')
        && ($r->data()['price'] ?? null) == 1450000
        && ($r->data()['kuota'] ?? null) == 12);
});

test('harga yang dikosongkan terkirim sebagai kosong, bukan dihilangkan', function () {
    palsukanBorangPaket();

    /*
     | Kosong berarti "ikut paket", dan itu keputusan yang harus SAMPAI ke
     | Orcha. Kunci yang tidak terkirim membuatnya mengira angkanya memang
     | tidak disentuh, sehingga harga sendiri yang baru dihapus admin tetap
     | menempel di tanggal itu.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->call('ubahKeberangkatan', 2)
        ->set('kbHargaTeks', '')
        ->set('kbKuota', '')
        ->call('simpanKeberangkatan');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/keberangkatan/2')
        && array_key_exists('price', $r->data())
        && $r->data()['price'] === ''
        && $r->data()['kuota'] === '');
});

test('menyunting tanggal membaca angka mentahnya, bukan yang terpakai', function () {
    palsukanBorangPaket();

    // Angka mentah membedakan "memang diisi segini" dari "ikut paket"; yang
    // terpakai tidak. Tanggal pertama ikut paket, yang kedua punya angkanya.
    $uji = Livewire::actingAs(adminPeserta())->test(OrchaPaketForm::class, ['paket' => 7]);

    $uji->call('ubahKeberangkatan', 1)
        ->assertSet('kbHargaTeks', '')
        ->assertSet('kbKuota', '');

    $uji->call('ubahKeberangkatan', 2)
        ->assertSet('kbHargaTeks', '1.450.000')
        ->assertSet('kbKuota', '12');
});

/* ========================= TIDAK TERMASUK ========================= */

test('yang tidak termasuk dibaca, disunting, dan dikirim sebagai daftar sendiri', function () {
    palsukanBorangPaket();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->assertSet('tidakTermasuk', ['Tiket masuk wisata'])
        ->call('jungkit', 'tidak_termasuk', 'Pengeluaran pribadi')
        ->assertSet('tidakTermasuk', ['Tiket masuk wisata', 'Pengeluaran pribadi'])
        ->call('simpan');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/paket-wisata/7')
        && $r->method() === 'POST'
        && (($r->data()['tidak_termasuk'] ?? [])[1] ?? null) === 'Pengeluaran pribadi'
        // TIDAK mencemari fasilitas: menggabung keduanya membuat "Tiket
        // masuk" terbaca sebagai yang DIDAPAT.
        && ! in_array('Pengeluaran pribadi', (array) ($r->data()['fasilitas'] ?? []), true));
});

test('empat daftar pilihan tidak saling menimpa', function () {
    palsukanBorangPaket();

    // Dulu jungkit/tambah/buang memakai ternary "destinasi atau fasilitas".
    // Satu ternary yang terlewat membuat isian jatuh ke daftar yang salah
    // tanpa galat.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->call('jungkit', 'destinasi', 'Baluran')
        ->call('jungkit', 'fasilitas', 'Makan 3x')
        ->call('jungkit', 'tidak_termasuk', 'Asuransi')
        ->call('jungkit', 'titik_jemput', 'Magelang')
        ->assertSet('destinasi', ['Kawah Ijen', 'Baluran'])
        ->assertSet('fasilitas', ['Transport AC', 'Makan 3x'])
        ->assertSet('tidakTermasuk', ['Tiket masuk wisata', 'Asuransi'])
        ->assertSet('titikJemputDaftar', ['Jogja', 'Klaten', 'Surakarta', 'Magelang']);
});

/* =========================== TITIK JEMPUT =========================== */

test('titik jemput dipecah jadi daftar dan disatukan lagi saat disimpan', function () {
    palsukanBorangPaket();

    // Bentuk simpanannya dipertahankan karena belasan tempat lain membacanya
    // sebagai satu kalimat berkoma; yang berubah cuma cara mengisinya.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->assertSet('titikJemputDaftar', ['Jogja', 'Klaten', 'Surakarta'])
        ->call('buang', 'titik_jemput', 1)
        ->call('simpan');

    // Diperiksa lewat data(), bukan $r['...']: assertSent menyusuri SEMUA
    // permintaan, termasuk GET rujukan & saran yang tidak punya kunci itu —
    // dan $r['x'] pada permintaan tanpa x melempar, bukan memulangkan null.
    Http::assertSent(fn ($r) => str_contains($r->url(), '/paket-wisata/7')
        && ($r->data()['titik_jemput'] ?? null) === 'Jogja, Surakarta');
});

test('koma menggantung tidak jadi titik jemput bernama kosong', function () {
    palsukanBorangPaket(isiPaketUji(['titik_jemput' => 'Jogja, Klaten, ']));

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->assertSet('titikJemputDaftar', ['Jogja', 'Klaten']);
});

/* ============================== GALERI ============================== */

test('galeri dibaca dan dikirim sebagai daftar yang dipertahankan', function () {
    palsukanBorangPaket();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->assertSet('galeriLama', ['/storage/paket/a.webp', '/storage/paket/b.webp'])
        ->call('buangGaleriLama', 0)
        ->assertSet('galeriLama', ['/storage/paket/b.webp'])
        ->call('simpan');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/paket-wisata/7')
        && (($r->data()['galeri_tetap'] ?? [])[0] ?? null) === '/storage/paket/b.webp'
        // Penanda eksplisit: perataan multipart membuang senarai kosong,
        // jadi borang yang menghapus SEMUA foto akan terbaca di Orcha
        // seperti borang yang tidak menyinggung galeri sama sekali.
        && (bool) ($r->data()['galeri_disentuh'] ?? false));
});

/* ========================= JADWAL KEGIATAN ========================= */

test('hari bisa ditukar dengan tetangganya', function () {
    palsukanBorangPaket();

    $uji = Livewire::actingAs(adminPeserta())->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('hari', [
            ['nama' => 'Hari 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => 'Berangkat']]],
            ['nama' => 'Hari 2', 'agenda' => [['jam' => '08.00', 'kegiatan' => 'Ijen']]],
        ]);

    $uji->call('pindahHari', 0, 1);

    expect($uji->get('hari')[0]['nama'])->toBe('Hari 2');

    // Menukar di ujung TIDAK boleh menghilangkan satu hari.
    $uji->call('pindahHari', 0, -1);
    expect($uji->get('hari'))->toHaveCount(2);
});

test('menyalin hari menaruh salinannya tepat di bawah asalnya', function () {
    palsukanBorangPaket();

    // Yang menyalin hari ke-1 bermaksud menaruhnya jadi hari ke-2, bukan di
    // ujung daftar.
    $uji = Livewire::actingAs(adminPeserta())->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('hari', [
            ['nama' => 'Hari 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => 'Berangkat']]],
            ['nama' => 'Puncak Ijen', 'agenda' => [['jam' => '01.00', 'kegiatan' => 'Pendakian']]],
        ])
        ->call('salinHari', 0);

    $hari = $uji->get('hari');

    expect($hari)->toHaveCount(3)
        ->and($hari[1]['agenda'][0]['kegiatan'])->toBe('Berangkat')
        // Hari yang sudah diberi nama sendiri TIDAK ditimpa jadi "Hari 3".
        ->and($hari[2]['nama'])->toBe('Puncak Ijen');
});

test('jam yang diketik bebas dirapikan jadi satu bentuk', function () {
    palsukanBorangPaket();

    /*
     | Satu paket bisa memuat "7:00", "07:00", dan "07.00" sekaligus tanpa
     | ada yang menegur, dan ketiganya tampil apa adanya di halaman publik.
     */
    $uji = Livewire::actingAs(adminPeserta())->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('hari', [['nama' => 'Hari 1', 'agenda' => [['jam' => '', 'kegiatan' => 'Berangkat']]]]);

    foreach ([['7:00', '07.00'], ['07:30', '07.30'], ['5.5', '5.5'], ['Menyesuaikan', 'Menyesuaikan']] as [$ketik, $jadi]) {
        $uji->set('hari.0.agenda.0.jam', $ketik);

        expect($uji->get('hari')[0]['agenda'][0]['jam'])->toBe($jadi, "'{$ketik}' seharusnya jadi '{$jadi}'");
    }
});

test('mengganti seluruh senarai hari tidak menjatuhkan halaman', function () {
    palsukanBorangPaket();

    /*
     | Livewire memanggil kait updatedHari tanpa kunci ketika SELURUH senarai
     | diganti sekaligus — yang persis terjadi saat borangnya diisi dari
     | Orcha. Tanda tangan yang menuntut string melempar TypeError dan
     | menjatuhkan seluruh halaman: borangnya tidak bisa dibuka sama sekali.
     */
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPaketForm::class, ['paket' => 7])
        ->set('hari', [['nama' => 'Hari 1', 'agenda' => [['jam' => '07.00', 'kegiatan' => 'Berangkat']]]])
        ->assertOk();
});

test('centang tergambar sesuai datanya, bukan selalu kosong', function () {
    palsukanBorangPaket(isiPaketUji([
        'promo_rombongan' => true,
        'berakhir_otomatis' => true,
        'pilihan_terbaik' => true,
    ]));

    /*
     | wire:model TIDAK menulis atribut checked saat halamannya pertama
     | digambar. Kotaknya tampil kosong meski datanya sudah true, dan admin
     | membacanya sebagai "belum ikut promo" lalu mencentangnya — yang justru
     | MEMATIKANNYA. Tidak ada galat di mana pun.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    foreach (['pb-rombongan', 'pb-berakhir', 'pb-terbaik'] as $id) {
        $mulai = strpos($isi, 'id="'.$id.'"');

        expect($mulai)->not->toBeFalse("Centang {$id} tidak ditemukan.");

        $tag = substr($isi, $mulai, strpos($isi, '>', $mulai) - $mulai);

        test()->assertStringContainsString('checked', $tag, "Centang {$id} tidak tergambar padahal datanya true.");
    }
});

test('centang yang datanya false tidak ikut tergambar', function () {
    palsukanBorangPaket(isiPaketUji([
        'promo_rombongan' => false,
        'berakhir_otomatis' => false,
        'pilihan_terbaik' => false,
    ]));

    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    foreach (['pb-rombongan', 'pb-berakhir', 'pb-terbaik'] as $id) {
        $mulai = strpos($isi, 'id="'.$id.'"');
        $tag = substr($isi, $mulai, strpos($isi, '>', $mulai) - $mulai);

        test()->assertStringNotContainsString('checked', $tag, "Centang {$id} tergambar padahal datanya false.");
    }
});

test('empat tombol di baris tanggal sama tingginya', function () {
    palsukanBorangPaket();

    /*
     | Terukur sebelumnya: ikon Ubah & Hapus setinggi 34px, sementara "Salin"
     | dan "Tutup" memakai .dsb-tombol yang 44px — empat tombol bersebelahan
     | dengan dua tinggi berbeda, dan garis bawahnya tidak pernah sejajar.
     | Sewa Kendaraan pernah punya cacat yang sama persis.
     |
     | Tingginya DIPATOK sama lewat satu kelas, bukan diperiksa lewat nama
     | kelasnya: nama yang sama tidak menjamin angka yang sama.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    $mulai = strpos($isi, 'class="pb-kb-aksi"');

    expect($mulai)->not->toBeFalse('Deretan tombol tanggal tidak ditemukan.');

    $deret = substr($isi, $mulai, strpos($isi, '</span>', strpos($isi, '</button>', $mulai)) - $mulai + 2000);
    $deret = substr($deret, 0, strpos($deret, '</div>') ?: strlen($deret));

    // Keempatnya memakai satu kelas yang sama; tidak ada .dsb-tombol yang
    // menyelinap kembali dengan tinggi bawaannya sendiri.
    expect(substr_count($deret, 'pb-kb-btn'))->toBe(4)
        ->and($deret)->not->toContain('dsb-tombol');

    // Dan kelas itu memang memaku tingginya.
    preg_match('/\.pb-kb-btn\s*\{[^}]*?height:\s*(\d+)px/s', $isi, $tinggi);

    expect($tinggi[1] ?? null)->not->toBeNull('Tinggi .pb-kb-btn tidak dipatok.');
});

test('lencana yang banyak tidak mendorong tombolnya turun', function () {
    palsukanBorangPaket();

    /*
     | Sebagai satu baris flex yang melipat, baris yang lencananya banyak
     | mendorong tombolnya ke baris kedua — sementara baris di atas dan di
     | bawahnya tetap sebaris, sehingga deretan tombolnya bertingkat-tingkat.
     | Dua kolom yang dipatok membuat lencananya melipat di dalam kolomnya
     | sendiri; tombolnya tidak pernah pindah tempat.
     */
    $isi = test()->actingAs(adminPeserta())
        ->get('/admin/orcha/paket-wisata/7/ubah')->assertOk()->getContent();

    expect($isi)
        ->toContain('class="pb-kb-isi"')
        ->and($isi)->toMatch('/\.pb-kb-baris\s*\{[^}]*grid-template-columns:\s*minmax\(0, 1fr\) auto/s');
});

test('kotak isian menyebut apa yang sedang dikerjakan', function () {
    palsukanBorangPaket();

    // Menekan "Ubah" tanpa judul yang berganti tidak mengubah apa pun yang
    // terlihat — isinya saja yang terisi — dan yang menekannya tidak tahu
    // bahwa ia sedang menyunting, bukan menambah.
    $uji = Livewire::actingAs(adminPeserta())->test(OrchaPaketForm::class, ['paket' => 7]);

    $uji->assertSee('Tambah tanggal baru');

    $uji->call('ubahKeberangkatan', 2)->assertSee('Ubah tanggal yang dipilih');

    $uji->call('batalKeberangkatan')
        ->call('salinKeberangkatan', 1)
        ->assertSee('Salin jadi tanggal baru');
});
