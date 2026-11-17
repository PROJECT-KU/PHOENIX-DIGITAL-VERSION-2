<?php

/**
 * Dua berkas cetak hasil pembagian: denah kursi dan daftar kamar.
 *
 * Sebelum keduanya ada, seluruh pembagian berakhir di satu tab peramban.
 * Manifes tour leader memang membawa bus dan kamar, tetapi TIDAK membawa nomor
 * kursi — satu-satunya hal yang menentukan siapa duduk di mana; tour leader
 * tetap mengatur tempat duduk dengan tangan di parkiran, dan daftar kamar
 * untuk hotel ditulis ulang dari layar.
 */

use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function adminCetakPbg(): User
{
    $role = Role::create(['name' => 'uji-cetak-'.uniqid(), 'description' => 'uji cetak']);
    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);
    EmployeeDetail::create(['user_id' => $user->id, 'jabatan' => 'Admin', 'nomor_rekening' => '1',
        'tanggal_lahir' => '1995-01-01', 'phone' => '0812', 'alamat' => 'Yogyakarta']);

    return $user->fresh();
}

function keadaanCetak(array $ubah = []): array
{
    return array_merge([
        'keberangkatan' => [
            'id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026',
            'paket' => ['id' => 5, 'nama' => 'Open Trip Bromo'],
        ],
        'kendaraan' => [['nama' => 'Bus 1', 'tata' => '2-2', 'jumlah_kursi' => 8]],
        'kamar' => [['nama' => '201', 'kapasitas' => 2], ['nama' => '202', 'kapasitas' => 2]],
        'petak' => [[
            'nama' => 'Bus 1', 'tata' => '2-2', 'lebar' => 5, 'jumlah_kursi' => 8,
            'baris' => [['1', '2', null, '3', '4'], ['5', '6', null, '7', '8']],
            'kursi' => ['1', '2', '3', '4', '5', '6', '7', '8'],
        ]],
        'peserta' => [
            ['kunci' => '7:0', 'pendaftaran_id' => 7, 'urutan' => 0, 'kode' => 'OT-1711-R7',
                'pemesan' => 'Bu Sri', 'status' => 'lunas', 'nama' => 'Budi Santoso',
                'titik_jemput' => 'Terminal Giwangan', 'bus' => 'Bus 1', 'kursi' => '1',
                'kamar' => '201', 'jenis_kelamin' => 'Laki-laki', 'kelamin_dari_kesehatan' => false],
            ['kunci' => '7:1', 'pendaftaran_id' => 7, 'urutan' => 1, 'kode' => 'OT-1711-R7',
                'pemesan' => 'Bu Sri', 'status' => 'lunas', 'nama' => 'Siti Aminah',
                'titik_jemput' => 'Terminal Giwangan', 'bus' => 'Bus 1', 'kursi' => '2',
                'kamar' => '201', 'jenis_kelamin' => 'Perempuan', 'kelamin_dari_kesehatan' => false],
            ['kunci' => '8:0', 'pendaftaran_id' => 8, 'urutan' => 0, 'kode' => 'OT-1711-R8',
                'pemesan' => 'Pak Agus', 'status' => 'lunas', 'nama' => 'Agus Priyanto',
                'titik_jemput' => 'Janti', 'bus' => null, 'kursi' => null,
                'kamar' => null, 'jenis_kelamin' => 'Laki-laki', 'kelamin_dari_kesehatan' => false],
        ],
    ], $ubah);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    config()->set('uji.cetak.keadaan', []);

    Http::fake(fn ($p) => str_contains($p->url(), '/rujukan')
        ? Http::response(['data' => []])
        : Http::response(['data' => keadaanCetak(config('uji.cetak.keadaan') ?: [])]));
});

test('denah kursi terbit sebagai pdf', function () {
    $balasan = test()->actingAs(adminCetakPbg())
        ->get('/admin/orcha/rombongan/11/denah-kursi')
        ->assertOk();

    expect($balasan->headers->get('content-type'))->toContain('application/pdf');
    expect($balasan->headers->get('content-disposition'))->toContain('DENAH-KURSI');
});

test('denah kursi memuat nama di kursinya dan menyebut yang belum kebagian', function () {
    /*
     | Lembar yang hanya memuat yang sudah ditempatkan terbaca sebagai daftar
     | lengkap, dan yang kurang baru ketahuan saat orangnya berdiri di parkiran
     | tanpa nama di kertas.
     */
    $html = view('exports.orcha-denah-kursi-pdf', [
        'keberangkatan' => keadaanCetak()['keberangkatan'],
        'petak' => keadaanCetak()['petak'],
        'peserta' => keadaanCetak()['peserta'],
    ])->render();

    expect($html)->toContain('Budi Santoso');
    expect($html)->toContain('Siti Aminah');
    // Titik jemput ikut: sopir butuh keduanya bersamaan.
    expect($html)->toContain('Terminal Giwangan');
    // Yang belum dapat kursi disebut namanya.
    expect($html)->toContain('1 peserta belum dapat kursi');
    expect($html)->toContain('Agus Priyanto');
    // Arah bacanya dinyatakan — tour leader berdiri di depan lorong.
    expect($html)->toContain('DEPAN / SOPIR');
});

test('daftar kamar menandai kamar yang tercampur', function () {
    /*
     | Resepsionis yang menyerahkan kunci tidak tahu mana yang disengaja
     | (pasangan suami-istri) dan mana yang salah ketik — yang ia butuhkan cuma
     | tanda untuk bertanya.
     */
    $html = view('exports.orcha-daftar-kamar-pdf', [
        'keberangkatan' => keadaanCetak()['keberangkatan'],
        'kamar' => keadaanCetak()['kamar'],
        'peserta' => keadaanCetak()['peserta'],
    ])->render();

    expect($html)->toContain('201');
    expect($html)->toContain('mohon dikonfirmasi');
    // Jenis kelamin dicetak: itulah yang dicocokkan resepsionis.
    expect($html)->toContain('(L)');
    expect($html)->toContain('(P)');
    // Kamar kosong disebut apa adanya, bukan dihilangkan dari lembarnya.
    expect($html)->toContain('kosong');
    expect($html)->toContain('1 peserta belum dapat kamar');
});

test('daftar kamar terbit sebagai pdf', function () {
    $balasan = test()->actingAs(adminCetakPbg())
        ->get('/admin/orcha/rombongan/11/daftar-kamar')
        ->assertOk();

    expect($balasan->headers->get('content-disposition'))->toContain('DAFTAR-KAMAR');
});

test('rombongan tanpa kendaraan menolak mencetak denah, dengan sebabnya', function () {
    config()->set('uji.cetak.keadaan', ['petak' => [], 'kendaraan' => []]);

    test()->actingAs(adminCetakPbg())
        ->get('/admin/orcha/rombongan/11/denah-kursi')
        ->assertNotFound();
});

test('rombongan tanpa kamar menolak mencetak daftar kamar', function () {
    config()->set('uji.cetak.keadaan', ['kamar' => []]);

    test()->actingAs(adminCetakPbg())
        ->get('/admin/orcha/rombongan/11/daftar-kamar')
        ->assertNotFound();
});

test('manifes tour leader akhirnya memuat nomor kursinya', function () {
    /*
     | Dulu hanya bus dan kamar yang tercetak, sehingga nomor kursi — satu
     | satunya hal yang menentukan siapa duduk di mana — tidak pernah sampai ke
     | kertas yang dibawa tour leader.
     */
    $isi = file_get_contents(resource_path('views/exports/orcha-manifest-daftar-pdf.blade.php'));

    expect($isi)->toContain("'kursi' => trim((string) (\$orang['kursi'] ?? ''))");
});

test('denah kursi menjawab "di titik ini siapa saja yang naik"', function () {
    /*
     | Pertanyaan yang ditanyakan sopir di titik jemput ketiga. Daftar per
     | kursi tidak bisa menjawabnya — mata harus melompat dua puluh baris
     | sambil berdiri di pinggir jalan.
     */
    $keadaan = keadaanCetak();
    $keadaan['peserta'][2]['bus'] = 'Bus 1';
    $keadaan['peserta'][2]['kursi'] = '3';

    $html = view('exports.orcha-denah-kursi-pdf', [
        'keberangkatan' => $keadaan['keberangkatan'],
        'petak' => $keadaan['petak'],
        'peserta' => $keadaan['peserta'],
    ])->render();

    expect($html)->toContain('Titik jemput — Bus 1');
    expect($html)->toContain('Terminal Giwangan');
    expect($html)->toContain('Janti');
    // Nama beserta nomor kursinya, bukan nama saja.
    expect($html)->toContain('Budi Santoso (1)');
});
