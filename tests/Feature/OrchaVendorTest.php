<?php

use App\Livewire\Pages\Admin\Orcha\Vendor\OrchaVendorDetail;
use App\Livewire\Pages\Admin\Orcha\Vendor\OrchaVendorList;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Daftar vendor di lemon.
 *
 * Yang dijaga di sini bukan rupa layarnya, melainkan satu janji: nomor vendor
 * hidup di SATU tempat. Dulu ia cuma muat di kolom catatan master harga —
 * teks bebas, tidak bisa dicari, dan tidak ikut tersalin ke RAB.
 */
function rekamanVendor(): array
{
    return [
        'data' => [
            ['id' => 1, 'nama' => 'PO Sumber Jaya', 'kontak' => '081234000111 (Pak Budi)',
                'jenis' => 'Armada', 'provinsi' => 'DI Yogyakarta', 'catatan' => 'Pool di Maguwo', 'aktif' => true,
                'jumlah_harga' => 3, 'diubah_pada' => '2026-10-27T08:00:00+07:00'],
            ['id' => 3, 'nama' => 'Vendor Tanpa Nomor', 'kontak' => null,
                'jenis' => null, 'provinsi' => null, 'catatan' => null, 'aktif' => true,
                'jumlah_harga' => 0, 'diubah_pada' => '2026-10-27T08:00:00+07:00'],
        ],
        'meta' => [
            'halaman' => 1, 'halaman_terakhir' => 1, 'total' => 2, 'per_halaman' => 15,
            'ringkas' => [
                'tanpa_kontak' => 1, 'nonaktif' => 2,
                'jenis' => ['Armada', 'Akomodasi'], 'provinsi' => ['Bali', 'DI Yogyakarta'],
            ],
        ],
    ];
}

function rekamanVendorBaris(): array
{
    return rekamanVendor()['data'][0];
}

function palsukanVendor(): void
{
    Http::fake(function (Request $r) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        return match (true) {
            str_ends_with($jalur, '/rincian') => Http::response(['data' => [
                'vendor' => rekamanVendorBaris(),
                'harga' => [], 'rab' => [], 'belanja' => 0, 'belanja_teks' => 'Rp 0',
            ]]),
            str_ends_with($jalur, '/kontak-tercecer') => Http::response(['data' => [
                ['id' => 7, 'nama' => 'Sewa Hiace', 'catatan' => 'PO Sumber Jaya 081234000111'],
            ], 'meta' => ['total' => 1]]),
            str_ends_with($jalur, '/gabung') => Http::response(
                ['data' => rekamanVendorBaris(), 'pesan' => '3 harga dipindahkan ke PO Sumber Jaya.']),
            str_contains($jalur, '/vendor') && $r->method() === 'POST' => Http::response(
                ['data' => rekamanVendorBaris(), 'pesan' => 'Vendor ditambahkan.'], 201),
            str_contains($jalur, '/vendor') && in_array($r->method(), ['PATCH', 'PUT'], true) => Http::response(
                ['data' => rekamanVendorBaris(), 'pesan' => 'Vendor diperbarui.']),
            str_contains($jalur, '/vendor') && $r->method() === 'DELETE' => Http::response(
                ['pesan' => 'Vendor dihapus. Harga yang memakainya tetap ada.']),
            str_contains($jalur, '/vendor') => Http::response(rekamanVendor()),
            default => Http::response(['data' => [], 'meta' => []]),
        };
    });
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rab.katalog');
});

test('halaman vendor tergambar dengan nomornya', function () {
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('PO Sumber Jaya')
        ->assertSee('081234000111 (Pak Budi)')
        // Berapa harga yang akan kehilangan kontaknya bila dihapus — angka
        // yang perlu dilihat SEBELUM menekan hapus.
        ->assertSee('3 harga');
});

test('vendor tanpa nomor ditandai, bukan dibiarkan kosong', function () {
    /*
     | Vendor tanpa nomor adalah vendor yang tetap harus dicari saat
     | dibutuhkan. Sel kosong terbaca seperti "tidak apa-apa"; yang benar
     | adalah "ini belum selesai".
     */
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('belum ada nomor');
});

test('vendor baru terkirim beserta kontaknya', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'PO Sumber Jaya')
        ->set('isian.kontak', '081234000111 (Pak Budi)')
        ->call('pakaiJenis', 'Armada')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor')
        && $r['nama'] === 'PO Sumber Jaya'
        && $r['kontak'] === '081234000111 (Pak Budi)'
        && $r['jenis'] === 'Armada');
});

test('nomor tidak dipaksa berformat angka', function () {
    /*
     | "0812xxx (Pak Budi)" justru bentuk yang paling berguna saat menelepon.
     | Memaksa format angka membuat yang paling berguna ditolak aplikasi.
     */
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Hotel Mawar')
        ->set('isian.kontak', '0274-555000 ext. 12, WA 0812-3456 (Mbak Rina)')
        ->call('simpan')
        ->assertHasNoErrors();
});

test('nama vendor tetap wajib', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.kontak', '08123456789')
        ->call('simpan')
        ->assertHasErrors('isian.nama');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor'));
});

test('jenis yang ditekan dua kali terlepas lagi', function () {
    // Tanpa ini, jenis yang salah tekan tidak punya cara dibatalkan.
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->call('pakaiJenis', 'Armada')
        ->assertSet('isian.jenis', 'Armada')
        ->call('pakaiJenis', 'Armada')
        ->assertSet('isian.jenis', '');
});

test('menyimpan vendor melupakan katalog RAB', function () {
    /*
     | Daftar vendor ikut disimpan di katalog RAB. Tanpa dilupakan, vendor
     | yang baru dibuat tidak muncul di kotak pilih master harga dan tampak
     | seperti gagal tersimpan.
     */
    palsukanVendor();
    cache()->put('orcha.rab.katalog', ['vendor' => [], 'destinasi' => []], now()->addMinutes(10));

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'PO Sumber Jaya')
        ->call('simpan');

    expect(cache()->get('orcha.rab.katalog'))->toBeNull();
});

test('vendor yang masih dipakai ditawari dimatikan, bukan dihapus', function () {
    /*
     | Yang hampir selalu dimaksud admin adalah "jangan tawarkan lagi", dan
     | itu namanya mematikan. Menghapusnya menghilangkan kontak dari seluruh
     | RAB yang memakainya tanpa bisa dibatalkan.
     */
    palsukanVendor();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/vendor')->assertOk()->getContent();

    // PO Sumber Jaya: 3 harga, aktif -> tombolnya "matikan".
    expect($isi)->toContain('data-action="matikan"')
        ->and($isi)->toContain('Matikan PO Sumber Jaya?')
        ->and($isi)->toContain('tetap terbaca di 3 harga')
        // Vendor Tanpa Nomor: 0 harga -> hapus biasa, tanpa peringatan apa pun.
        ->and($isi)->toContain('data-action="hapus"')
        ->and($isi)->toContain('belum dipakai harga mana pun');
});

test('vendor nonaktif yang masih dipakai dihapus dengan paksa', function () {
    // Baris salah ketik memang pantas dibuang — tetapi dipaksa dengan sadar.
    Http::fake(fn () => Http::response(['data' => [[
        'id' => 9, 'nama' => 'Salah Ketik', 'kontak' => null, 'jenis' => null,
        'provinsi' => null, 'catatan' => null, 'aktif' => false, 'jumlah_harga' => 2,
    ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1, 'per_halaman' => 15]]));

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/vendor')->assertOk()->getContent();

    expect($isi)->toContain('data-action="hapusPaksa"')
        ->and($isi)->toContain('tidak bisa dibatalkan');
});

test('mematikan membaca barisnya dari Orcha, bukan dari markah', function () {
    /*
     | Dialog konfirmasi bersama hanya membawa SATU argumen, dan merakit
     | payload dari markah berarti nilai apa pun di sana bisa ikut tersimpan.
     */
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('matikan', 1);

    Http::assertSent(fn (Request $r) => $r->method() === 'GET'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/1/rincian'));

    Http::assertSent(fn (Request $r) => in_array($r->method(), ['PATCH', 'PUT'], true)
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/1')
        && $r['aktif'] === false
        && $r['nama'] === 'PO Sumber Jaya');
});

test('layar vendor memakai kerangka dasbor lemon', function () {
    palsukanVendor();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/vendor')->assertOk()->getContent();

    foreach (['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-tabel', 'dsb-lencana'] as $k) {
        expect(str_contains($isi, $k))->toBeTrue("layar vendor tidak memakai {$k}");
    }
});

test('ringkasan vendor terlihat tanpa memindai baris', function () {
    /*
     | Vendor tanpa nomor adalah pekerjaan yang belum selesai. Angkanya
     | dihitung Orcha dari SELURUH tabel — memindainya halaman demi halaman
     | berarti tidak akan pernah dipindai.
     */
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('1 tanpa nomor')
        ->assertSee('2 nonaktif');
});

test('vendor bisa disaring menurut jenis dan status', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->set('filterJenis', 'Armada')
        ->set('filterStatus2', 'nonaktif');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/vendor')
        && $r->method() === 'GET'
        && str_contains($r->url(), 'jenis=Armada')
        && str_contains($r->url(), 'status=nonaktif'));
});

test('nomor diambil dari kontak yang ditulis bebas', function () {
    /*
     | Kontaknya memang teks bebas — "081234000111 (Pak Budi)" justru bentuk
     | yang paling berguna. Tombol yang selalu ada lalu menelepon entah ke
     | mana lebih buruk daripada tidak ada tombol.
     */
    palsukanVendor();
    $k = Livewire::actingAs(adminRab())->test(OrchaVendorList::class)->instance();

    expect($k->nomorDari('081234000111 (Pak Budi)'))->toBe('081234000111')
        // Berhenti di huruf: "ext. 12" bukan bagian nomor yang ditelepon, dan
        // menempelkannya menghasilkan nomor yang tidak pernah tersambung.
        ->and($k->nomorDari('0274-555000 ext. 12'))->toBe('0274555000')
        // 0 di depan DIGANTI 62, bukan ditempeli — 62081… bukan nomor.
        ->and($k->nomorWa('081234000111 (Pak Budi)'))->toBe('6281234000111')
        ->and($k->nomorWa('+6281234000111'))->toBe('6281234000111')
        // Yang bukan nomor tidak dipaksa jadi nomor.
        ->and($k->nomorDari('tanya Pak Budi langsung'))->toBeNull()
        ->and($k->nomorDari('DP 30%'))->toBeNull()
        ->and($k->nomorDari(null))->toBeNull();
});

test('tombol WhatsApp hanya muncul bila ada nomornya', function () {
    palsukanVendor();

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/vendor')->assertOk()->getContent();

    expect($isi)->toContain('https://wa.me/6281234000111')
        ->and($isi)->toContain('tel:081234000111')
        // Baris "Vendor Tanpa Nomor" tidak boleh membawa tombol apa pun.
        ->and(substr_count($isi, 'https://wa.me/'))->toBe(1);
});

test('vendor nonaktif yang masih dipakai diberi tahu', function () {
    // Kontaknya tetap terbaca di RAB, hanya tidak bisa dipilih untuk harga
    // baru. Bukan salah, tetapi pantas diketahui.
    Http::fake(fn () => Http::response(['data' => [[
        'id' => 9, 'nama' => 'Jip Lama', 'kontak' => null, 'jenis' => null, 'catatan' => null,
        'aktif' => false, 'jumlah_harga' => 2,
    ]], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1, 'per_halaman' => 15]]));

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee('masih dipakai');
});

test('jumlah harga membawa ke daftar harga vendor itu', function () {
    // Angka yang menunjuk sesuatu tetapi tidak membawa ke sana cuma
    // menambah beban.
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee(route('admin.orcha.master-harga', ['vendor' => 1]), false)
        // Yang belum dipakai tidak jadi tautan buntu.
        ->assertSee('belum dipakai');
});

test('dua vendor kembar bisa digabungkan dari layarnya', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaGabung', 3)
        ->set('gabungKe', '1')
        ->call('gabungkan')
        ->assertHasNoErrors()
        ->assertSet('gabungDari', null);

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/3/gabung')
        && $r['ke'] === 1);
});

test('vendor tidak bisa digabungkan ke dirinya sendiri', function () {
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaGabung', 1)
        ->set('gabungKe', '1')
        ->call('gabungkan')
        ->assertHasErrors('gabungKe');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/gabung'));
});

test('kontak lama di catatan bisa dijadikan vendor sekali tekan', function () {
    /*
     | Sebelum ada daftar vendor, nomor ditulis di catatan — dan catatan itu
     | tidak pernah ikut ke RAB. Sisa data lama tidak akan pindah sendiri.
     */
    palsukanVendor();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('lihatTercecer');

    expect($layar->html())->toContain('PO Sumber Jaya 081234000111');

    // Namanya dipisahkan dari nomornya, bukan diketik ulang seluruhnya.
    $layar->call('dariCatatan', 'PO Sumber Jaya 081234000111')
        ->assertSet('isian.nama', 'PO Sumber Jaya')
        ->assertSet('isian.kontak', 'PO Sumber Jaya 081234000111')
        ->assertSet('tambah', true);
});

test('jenis yang diketik sendiri ditandai di luar daftar', function () {
    // Sama seperti destinasi di luar katalog pada master harga: nilai di luar
    // daftar harus terlihat sebagai di luar daftar.
    palsukanVendor();

    $isi = Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.jenis', 'Sewa Perahu')
        ->html();

    expect($isi)->toContain('di luar daftar baku');
});

test('rincian vendor menyebut belanja, harga, dan RAB-nya', function () {
    /*
     | Daftar vendor menjawab "siapa saja"; halaman ini menjawab "sudah
     | sejauh apa dengan vendor ini" — angka yang dipakai menawar harga
     | tahun depan.
     */
    Http::fake(fn () => Http::response(['data' => [
        'vendor' => ['id' => 1, 'nama' => 'PO Sumber Jaya', 'kontak' => '081234000111 (Pak Budi)',
            'jenis' => 'Armada', 'provinsi' => 'DI Yogyakarta', 'catatan' => null,
            'aktif' => true, 'jumlah_harga' => 2],
        'harga' => [
            ['id' => 5, 'nama' => 'Sewa Hiace', 'kategori_label' => 'Transportasi',
                'satuan_label' => 'per unit per hari', 'harga' => 1100000,
                'harga_teks' => 'Rp 1.100.000', 'aktif' => true],
        ],
        'rab' => [
            ['id' => 3, 'kode' => 'RAB-001', 'judul' => 'Private Trip Jogja', 'status' => 'disetujui',
                'status_label' => 'Disetujui', 'tanggal_mulai' => '2026-11-01',
                'jumlah_baris' => 1, 'nilai' => 6600000, 'nilai_teks' => 'Rp 6.600.000'],
        ],
        'belanja' => 6600000, 'belanja_teks' => 'Rp 6.600.000',
    ]]));

    $this->actingAs(adminRab())->get('/admin/orcha/vendor/1')
        ->assertOk()
        ->assertSee('PO Sumber Jaya')
        ->assertSee('Rp 6.600.000')
        ->assertSee('Sewa Hiace')
        ->assertSee('RAB-001')
        // Tombol hubungi ikut, dengan aturan nomor yang sama.
        ->assertSee('https://wa.me/6281234000111', false);
});

test('nama vendor membawa ke halaman rinciannya', function () {
    palsukanVendor();

    $this->actingAs(adminRab())->get('/admin/orcha/vendor')
        ->assertOk()
        ->assertSee(route('admin.orcha.vendor.detail', 1), false);
});

test('wilayah vendor bisa disaring dan ikut terkirim', function () {
    // Dengan empat puluh vendor, yang benar tenggelam di antara yang tidak
    // mungkin dipakai.
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->set('filterProvinsi', 'Bali');

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/vendor')
        && $r->method() === 'GET' && str_contains($r->url(), 'provinsi=Bali'));

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'PO Bali Indah')
        ->set('isian.provinsi', 'Bali')
        ->call('simpan');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor')
        && $r['provinsi'] === 'Bali');
});

test('nomor yang panjangnya janggal ditandai, bukan ditolak', function () {
    /*
     | Kontaknya memang teks bebas, dan nomor kantor berbeda panjang dari
     | seluler. Tetapi salah ketik satu digit tersimpan dengan yakin, dan
     | tombol WhatsApp-nya mengarah ke nomor orang lain tanpa satu pun tanda.
     */
    palsukanVendor();
    $k = Livewire::actingAs(adminRab())->test(OrchaVendorList::class)->instance();

    expect($k->nomorJanggal('081234000111'))->toBeFalse()        // 12 digit, wajar
        ->and($k->nomorJanggal('0274555000'))->toBeFalse()        // 10 digit, nomor kantor
        ->and($k->nomorJanggal('08123400'))->toBeTrue()           // 8 digit, ada yang terlewat
        ->and($k->nomorJanggal('0812340001112233'))->toBeTrue()   // 16 digit, kelebihan
        // Yang terlalu pendek untuk dianggap nomor sama sekali tidak
        // ditandai — di sana yang muncul "belum ada nomor", bukan "periksa
        // lagi", dan dua tanda untuk satu keadaan hanya membingungkan.
        ->and($k->nomorJanggal('08123'))->toBeFalse()
        ->and($k->nomorJanggal('tanya Pak Budi'))->toBeFalse();
});

test('ekspor vendor mengikuti saringan yang sedang aktif', function () {
    // Berkas yang isinya berbeda dari yang terlihat di layar adalah berkas
    // yang menyesatkan.
    palsukanVendor();

    Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->set('filterJenis', 'Armada')
        ->call('unduhExcel');

    Http::assertSent(fn (Request $r) => $r->method() === 'GET'
        && str_contains($r->url(), '/vendor')
        && str_contains($r->url(), 'jenis=Armada')
        // Tanpa halaman: yang terunduh seluruhnya, bukan lima belas baris.
        && str_contains($r->url(), 'per_halaman=500'));
});

test('halaman rincian bisa menyunting dan mematikan di tempat', function () {
    /*
     | Halaman ini satu-satunya tempat yang menyebut sudah dibelanjakan
     | berapa — dan keputusan mengubah kontaknya justru lahir di sini, saat
     | angkanya terlihat. Melempar admin ke daftar berarti menyuruhnya
     | mencari baris yang baru saja ia buka.
     */
    palsukanVendor();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaVendorDetail::class, ['vendor' => 1])
        ->call('bukaUbah')
        ->assertSet('sunting', 1)
        ->assertSet('isian.nama', 'PO Sumber Jaya');

    $layar->set('isian.kontak', '082199900022')->call('simpan')->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => in_array($r->method(), ['PATCH', 'PUT'], true)
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/1')
        && $r['kontak'] === '082199900022');

    // Formulirnya menutup sendiri dan rinciannya dibaca ulang.
    $layar->assertSet('sunting', null);
});

test('daftar RAB di rincian berhalaman, belanjanya tidak ikut terpotong', function () {
    Http::fake(fn () => Http::response(['data' => [
        'vendor' => ['id' => 1, 'nama' => 'PO Sumber Jaya', 'kontak' => '081234000111',
            'jenis' => 'Armada', 'provinsi' => null, 'catatan' => null, 'aktif' => true, 'jumlah_harga' => 1],
        'harga' => [],
        'rab' => [['id' => 9, 'kode' => 'RAB-009', 'judul' => 'Trip A', 'status' => 'draf',
            'status_label' => 'Draf', 'tanggal_mulai' => null, 'jumlah_baris' => 1,
            'nilai' => 850000, 'nilai_teks' => 'Rp 850.000']],
        'rab_meta' => ['halaman' => 1, 'halaman_terakhir' => 3, 'total' => 5, 'per_halaman' => 2],
        'belanja' => 4250000, 'belanja_teks' => 'Rp 4.250.000',
    ]]));

    $layar = Livewire::actingAs(adminRab())->test(OrchaVendorDetail::class, ['vendor' => 1]);

    expect($layar->html())->toContain('Halaman 1 dari 3')
        ->and($layar->html())->toContain('5 RAB')
        // Belanjanya dari seluruh RAB, bukan dari satu yang tampil.
        ->and($layar->html())->toContain('Rp 4.250.000');

    $layar->call('keHalamanRab', 2)->assertSet('halamanRab', 2);

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/vendor/1/rincian')
        && str_contains($r->url(), 'halaman=2'));
});

test('beberapa catatan tercecer jadi satu vendor sekali jalan', function () {
    /*
     | Hiace, Elf, dan bus besar biasanya dari pemilik armada yang sama.
     | Menjadikannya satu per satu berarti mengulang keputusan yang sama
     | lalu menggabungkannya belakangan.
     */
    palsukanVendor();

    $layar = Livewire::actingAs(adminRab())
        ->test(OrchaVendorList::class)
        ->call('lihatTercecer')
        ->set('pilihTercecer', ['7'])
        ->call('dariBeberapaCatatan')
        ->assertSet('tambah', true)
        ->assertSet('isian.nama', 'PO Sumber Jaya')
        ->assertSet('pasangHarga', [7]);

    $layar->call('simpan')->assertHasNoErrors()->assertSet('pasangHarga', []);

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/vendor/1/pasang')
        && $r['harga'] === [7]);
});
