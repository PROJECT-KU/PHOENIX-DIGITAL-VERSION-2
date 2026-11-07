<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabRekap;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar rekap pengeluaran sesudah trip jalan.
 *
 * Hitungannya milik Orcha. Yang diuji di sini: lemon mengirim ke medan yang
 * benar, membedakan kosong dari nol, melampirkan bukti dengan NAMA MEDAN
 * yang diterima Orcha, dan MENGATAKAN bedanya hemat dengan kurang bayar —
 * dua hal berlawanan yang angkanya sama.
 */
function barisRekap(array $ubah = []): array
{
    return array_merge([
        'biaya_id' => 31,
        'realisasi_id' => null,
        'kategori' => 'akomodasi',
        'kategori_label' => 'Akomodasi',
        'nama' => 'Hotel Fave',
        'vendor' => 'Hotel Fave Malioboro',
        'vendor_kontak' => '0274-555000',
        'penjelasan' => 'Rp 8.000.000 × 1 rombongan',
        'rab' => 8_000_000,
        'rab_teks' => 'Rp 8.000.000',
        'tagihan' => null,
        'tagihan_teks' => null,
        'dibayar' => 0,
        'dibayar_teks' => 'Rp 0',
        'hemat' => null,
        'kurang_bayar' => null,
        'lunas' => false,
        'sudah_direkap' => false,
        'pic' => null,
        'catatan' => null,
        'tanpa_bukti' => 0,
        'bayar' => [],
    ], $ubah);
}

function palsukanRekap(array $baris, array $ringkas = [], string $status = 'selesai'): void
{
    Http::fake(function (Request $r) use ($baris, $ringkas, $status) {
        $jalur = parse_url($r->url(), PHP_URL_PATH);

        if (str_contains($jalur, '/rekap')) {
            return Http::response(['data' => array_merge([
                'baris' => $baris,
                'per_kategori' => [],
                'rab_total' => 8_000_000,
                'tagihan_total' => 0,
                'dibayar_total' => 0,
                'hemat' => 0,
                'kurang_bayar' => 0,
                'harga_jual' => 10_000_000,
                'untung_rencana' => 2_000_000,
                'untung_nyata' => 2_000_000,
                'jumlah_baris' => count($baris),
                'belum_direkap' => [],
                'bayar_tanpa_bukti' => 0,
                'lengkap' => true,
                'rab' => [
                    'id' => 7, 'kode' => 'RAB-2611-J2UX', 'judul' => 'Private Trip Dieng',
                    'status' => $status, 'status_label' => ucfirst($status),
                    'tanggal_mulai' => '2026-11-01', 'jumlah_peserta' => 11,
                ],
                'jenis_bayar' => [
                    'dp' => 'DP / Uang muka', 'pelunasan' => 'Pelunasan',
                    'cicilan' => 'Cicilan', 'refund' => 'Refund / Uang kembali',
                ],
            ], $ringkas)], str_contains($jalur, '/bayar') && $r->method() === 'POST' ? 201 : 200);
        }

        return Http::response(['data' => [], 'meta' => []]);
    });
}

/* ========================= KERANGKA & KALIMAT ========================= */

test('layar rekap memakai kerangka dasbor lemon', function () {
    palsukanRekap([barisRekap()]);

    $isi = $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()->getContent();

    foreach (['dsb-hero', 'dsb-rak', 'dsb-kartu', 'dsb-isian'] as $k) {
        expect($isi)->toContain($k);
    }

    expect($isi)->not->toContain('card border-0 shadow-sm rounded-4');
});

test('kurang bayar disebut utang, bukan untung', function () {
    palsukanRekap([barisRekap(['tagihan' => 8_000_000, 'dibayar' => 7_000_000, 'hemat' => 0,
        'kurang_bayar' => 1_000_000, 'sudah_direkap' => true])],
        ['tagihan_total' => 8_000_000, 'dibayar_total' => 7_000_000,
            'hemat' => 0, 'kurang_bayar' => 1_000_000]);

    /*
     | Inilah seluruh gunanya layar ini. "RAB − dibayar" pada kasus ini dan
     | pada kasus cashback sama persis: 1 juta. Yang satu utang, yang satu
     | untung.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('Masih kurang bayar')
        ->assertSee('Ini utang, bukan untung.', false);
});

test('cashback disebut hemat', function () {
    palsukanRekap([barisRekap(['tagihan' => 7_000_000, 'dibayar' => 7_000_000, 'hemat' => 1_000_000,
        'kurang_bayar' => 0, 'lunas' => true, 'sudah_direkap' => true])],
        ['tagihan_total' => 7_000_000, 'dibayar_total' => 7_000_000,
            'hemat' => 1_000_000, 'kurang_bayar' => 0, 'untung_nyata' => 3_000_000]);

    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('Hemat')
        ->assertSee('vendor menagih lebih murah daripada RAB', false);
});

test('baris yang belum direkap disebut namanya', function () {
    palsukanRekap([barisRekap()], ['belum_direkap' => ['Hotel Fave'], 'lengkap' => false]);

    /*
     | Satu hotel yang terlewat membuat seluruh laporannya salah, dan
     | angkanya tetap terlihat masuk akal.
     */
    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('belum diisi tagihannya')
        ->assertSee('Hotel Fave');
});

test('pembayaran tanpa bukti disebut', function () {
    palsukanRekap([barisRekap(['tanpa_bukti' => 1])], ['bayar_tanpa_bukti' => 1]);

    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('belum ada buktinya');
});

test('trip yang belum selesai ditawari ditandai selesai', function () {
    palsukanRekap([barisRekap()], [], 'disetujui');

    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('belum ditandai selesai', false)
        ->assertSee('Tandai Selesai');
});

test('trip selesai tidak menampilkan ajakan itu', function () {
    palsukanRekap([barisRekap()], [], 'selesai');

    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertDontSee('Tandai Selesai');
});

/* ============================== PENGIRIMAN ============================== */

test('tagihan kosong dikirim null, bukan nol', function () {
    palsukanRekap([barisRekap(['tagihan' => 7_000_000, 'sudah_direkap' => true])]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->set('tagihan.31', '')
        ->call('simpanTagihan', 31);

    /*
     | null mengembalikannya ke "belum direkap"; nol berarti tagihannya
     | memang nol — destinasi yang ternyata gratis. Mengirim 0 untuk kotak
     | yang dikosongkan menyembunyikan baris itu dari daftar tunggakan.
     */
    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_contains($r->url(), '/rekap/biaya/31')
        && $r['tagihan'] === null);
});

test('tagihan nol dikirim sebagai nol', function () {
    palsukanRekap([barisRekap()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->set('tagihan.31', '0')
        ->call('simpanTagihan', 31);

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_contains($r->url(), '/rekap/biaya/31')
        && $r['tagihan'] === 0);
});

test('tagihan bertitik dikirim sebagai angka bersih', function () {
    palsukanRekap([barisRekap()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->set('tagihan.31', '7.000.000')
        ->set('pic.31', 'Berto')
        ->call('simpanTagihan', 31);

    // Disaring ke POST dulu: permintaan GET /rekap yang mendahuluinya tidak
    // punya kunci 'tagihan' sama sekali, dan membacanya melempar galat yang
    // menunjuk ke berkas uji alih-alih ke fiturnya.
    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_contains($r->url(), '/rekap/biaya/31')
        && $r['tagihan'] === 7000000 && $r['pic'] === 'Berto');
});

test('pembayaran dikirim ke jalur bayar', function () {
    palsukanRekap([barisRekap()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->call('bukaBayar', 31)
        ->set('bayar.jenis', 'dp')
        ->set('bayar.nominal', '500.000')
        ->set('bayar.tanggal', '2026-10-20')
        ->call('simpanBayar')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => $r->method() === 'POST'
        && str_contains($r->url(), '/rekap/biaya/31/bayar')
        && $r['jenis'] === 'dp' && $r['nominal'] === 500000);
});

test('nominal kosong ditolak sebelum menyentuh Orcha', function () {
    palsukanRekap([barisRekap()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->call('bukaBayar', 31)
        ->set('bayar.nominal', '')
        ->call('simpanBayar')
        ->assertHasErrors('bayar.nominal');

    Http::assertNotSent(fn (Request $r) => str_contains($r->url(), '/bayar'));
});

test('bukti dilampirkan dengan nama medan bukti, bukan gambar', function () {
    palsukanRekap([barisRekap()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->call('bukaBayar', 31)
        ->set('bayar.nominal', '500.000')
        ->set('bukti', UploadedFile::fake()->image('transfer.jpg'))
        ->call('simpanBayar')
        ->assertHasNoErrors();

    /*
     | Orcha memvalidasi medan bernama `bukti`. Berkas yang sampai dengan
     | nama lain membuat validasinya berkata "bukti harus berupa gambar"
     | untuk berkas yang sebenarnya ADA — galat yang menunjuk ke arah yang
     | salah.
     */
    Http::assertSent(function (Request $r) {
        if (! str_contains($r->url(), '/bayar')) {
            return false;
        }

        $nama = collect($r->data())->pluck('name')->all();

        return in_array('bukti', $nama, true) && ! in_array('gambar', $nama, true);
    });
});

test('mengganti bukti dikirim ke jalur bukti', function () {
    palsukanRekap([barisRekap(['bayar' => [[
        'id' => 55, 'jenis' => 'dp', 'jenis_label' => 'DP / Uang muka',
        'nominal' => 500_000, 'nominal_teks' => 'Rp 500.000', 'arah' => 1,
        'tanggal' => '2026-10-20', 'tanggal_teks' => '20 Okt 2026',
        'bukti' => '/storage/bukti-bayar/a.webp', 'bukti_diganti' => 0,
        'oleh' => 'Berto', 'catatan' => null,
    ]]])]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabRekap::class, ['rab' => 7])
        ->call('bukaGanti', 55)
        ->set('buktiBaru', UploadedFile::fake()->image('baru.jpg'))
        ->call('simpanGantiBukti')
        ->assertHasNoErrors();

    Http::assertSent(fn (Request $r) => str_contains($r->url(), '/rekap/bayar/55/bukti'));
});

test('pembayaran yang sudah tercatat tampil beserta penanda buktinya', function () {
    palsukanRekap([barisRekap(['bayar' => [
        [
            'id' => 55, 'jenis' => 'dp', 'jenis_label' => 'DP / Uang muka',
            'nominal' => 500_000, 'nominal_teks' => 'Rp 500.000', 'arah' => 1,
            'tanggal' => '2026-10-20', 'tanggal_teks' => '20 Okt 2026',
            'bukti' => null, 'bukti_diganti' => 0, 'oleh' => 'Berto', 'catatan' => null,
        ],
        [
            'id' => 56, 'jenis' => 'refund', 'jenis_label' => 'Refund / Uang kembali',
            'nominal' => 1_000_000, 'nominal_teks' => 'Rp 1.000.000', 'arah' => -1,
            'tanggal' => '2026-11-02', 'tanggal_teks' => '2 Nov 2026',
            'bukti' => '/storage/bukti-bayar/b.webp', 'bukti_diganti' => 2,
            'oleh' => 'Berto', 'catatan' => null,
        ],
    ]])]);

    $this->actingAs(adminRab())->get('/admin/orcha/rab/7/rekap')->assertOk()
        ->assertSee('DP / Uang muka')
        ->assertSee('belum ada bukti')
        // Uang yang KEMBALI ditandai minus, bukan ikut terbaca sebagai keluar.
        ->assertSee('− Rp 1.000.000', false)
        // Berapa kali buktinya diganti — pertanyaan yang muncul saat catatan
        // uang dipersoalkan.
        ->assertSee('(2×)', false);
});

/* ============================== PENJAGA ============================== */

test('layar rekap memasang penangan toast-error', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/rekap.blade.php'));

    expect($isi)->toContain("@include('livewire.pages.admin.orcha.partials.skrip')");
    expect($isi)->toContain('<style>');
    expect($isi)->not->toContain("@push('gaya')");
});

test('layar RAB menautkan ke rekap', function () {
    $isi = file_get_contents(resource_path('views/livewire/pages/admin/orcha/rab/susun.blade.php'));

    // Layar yang tidak bisa ditemukan sama dengan layar yang tidak ada.
    expect($isi)->toContain("route('admin.orcha.rab.rekap'");
});
