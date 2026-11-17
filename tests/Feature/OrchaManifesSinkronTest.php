<?php

/**
 * Manifes tour leader: PDF dan Excel harus SAMA isinya.
 *
 * Dua berkas yang menjawab pertanyaan yang sama tetapi berbeda isinya adalah
 * asal perselisihan di parkiran: yang satu dipegang tour leader, yang satu
 * dibuka panitia di laptop, dan tidak ada yang bisa menjelaskan mengapa
 * jumlahnya beda. Yang boleh berbeda cuma bentuknya.
 */

use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/*
 | Nama penolong berawalan "snk".
 |
 | Penolong Pest hidup di ruang nama GLOBAL: nama yang sama di dua berkas uji
 | adalah fatal error yang mematikan SELURUH rangkaian, bukan satu berkas —
 | dan pesannya menunjuk berkas yang kebetulan dimuat belakangan, bukan yang
 | salah. adminManifes() sudah dipakai OrchaManifesPaketTest.
 */

function snkAdmin(bool $kesehatan = true): User
{
    $role = Role::create(['name' => 'uji-manifes-'.uniqid(), 'description' => 'uji manifes']);

    $izin = ['akses_orcha'];

    if ($kesehatan) {
        $izin[] = 'view_orcha_kesehatan';
    }

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']);
        $role->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);
    EmployeeDetail::create(['user_id' => $user->id, 'jabatan' => 'Admin', 'nomor_rekening' => '1',
        'tanggal_lahir' => '1995-01-01', 'phone' => '0812', 'alamat' => 'Yogyakarta']);

    return $user->fresh();
}

function snkBaris(): array
{
    return [[
        'id' => 7, 'kode' => 'OT-1711-M7', 'nama' => 'Bu Sri',
        'whatsapp' => '081234567890', 'email' => null,
        'jumlah_peserta' => 3, 'pendamping_gratis' => 0, 'peserta_dibayar' => 3,
        'peserta' => [
            ['nama' => 'Budi Santoso', 'titik_jemput' => 'Terminal Giwangan',
                'bus' => 'Bus 1', 'kursi' => '1', 'kamar' => '201', 'jenis_kelamin' => 'Laki-laki'],
            ['nama' => 'Siti Aminah', 'titik_jemput' => 'Terminal Giwangan',
                'bus' => 'Bus 1', 'kursi' => '2', 'kamar' => '201', 'jenis_kelamin' => 'Perempuan'],
            ['nama' => 'Agus Priyanto', 'titik_jemput' => 'Janti',
                'bus' => 'Bus 1', 'kursi' => '3', 'kamar' => '202', 'jenis_kelamin' => 'Laki-laki'],
        ],
        'jemput_per_titik' => [
            'Terminal Giwangan' => ['Budi Santoso', 'Siti Aminah'],
            'Janti' => ['Agus Priyanto'],
        ],
        'bus_per_kelompok' => [], 'kamar_per_kelompok' => [],
        'kesehatan_terisi' => 1, 'kesehatan_lengkap' => false,
        'peserta_belum_isi' => ['Siti Aminah', 'Agus Priyanto'],
        'paket' => ['id' => 5, 'nama' => 'Open Trip Bromo', 'titik_jemput' => []],
        'keberangkatan_id' => 11,
        'keberangkatan' => ['id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026', 'aktif' => true],
        'tanggal_berangkat' => now()->addDays(10)->toDateString(),
        'hari_ke_berangkat' => 10, 'pengingat_pelunasan_pada' => null,
        'titik_jemput' => 'Terminal Giwangan', 'catatan' => null, 'catatan_internal' => null,
        'status' => 'lunas', 'status_label' => 'Lunas',
        'riwayat_penggantian' => [], 'surat_penggantian' => null, 'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orchajourney.com/riwayat-kesehatan?kode=OT-1711-M7',
        'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [], 'pembayaran' => [], 'pembatalan' => null,
    ]];
}

function snkKesehatan(): array
{
    return [[
        'nama_peserta' => 'Budi Santoso', 'usia' => 34, 'jenis_kelamin' => 'Laki-laki',
        'golongan_darah' => 'O', 'riwayat_penyakit' => 'Asma ringan', 'alergi' => 'Udang',
        'pantangan_makanan' => null, 'tingkat_perhatian' => 'tinggi',
        'kontak_darurat' => ['nama' => 'Sari', 'hp' => '08987654321', 'hubungan' => 'Istri'],
    ]];
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake(function ($p) {
        if (str_contains($p->url(), 'riwayat-kesehatan')) {
            return Http::response(['data' => snkKesehatan()]);
        }

        if (str_contains($p->url(), '/rujukan')) {
            return Http::response(['data' => []]);
        }

        return Http::response(['data' => snkBaris(), 'meta' => ['total' => 1]]);
    });
});

test('manifes PDF dan Excel sama-sama terbit', function () {
    $admin = snkAdmin();

    $pdf = test()->actingAs($admin)->get('/admin/orcha/pendaftaran-manifes')->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');

    $xls = test()->actingAs($admin)->get('/admin/orcha/pendaftaran-manifes-excel')->assertOk();
    expect($xls->headers->get('content-disposition'))->toContain('MANIFES-ROMBONGAN');
});

test('keduanya memuat peserta, kursi, kamar, dan titik jemput yang SAMA', function () {
    /*
     | Diperiksa dari markahnya, bukan dari berkas jadinya: PDF dan XLSX
     | sama-sama biner, dan membandingkan dua biner tidak memberi tahu apa pun
     | saat salah satunya berbeda.
     */
    $excel = view('exports.orcha-manifes-excel', [
        'daftar' => snkBaris(),
        'kesehatan' => [7 => snkKesehatan()],
        'saringan' => [],
    ])->render();

    $pdf = view('exports.orcha-manifest-daftar-pdf', [
        'daftar' => snkBaris(),
        'kesehatan' => [7 => snkKesehatan()],
        'saringan' => [],
    ])->render();

    foreach (['Budi Santoso', 'Siti Aminah', 'Agus Priyanto', 'Terminal Giwangan', 'Janti'] as $cari) {
        expect($excel)->toContain($cari);
        expect($pdf)->toContain($cari);
    }

    // Nomor kursi ikut di KEDUANYA — dulu hanya bus & kamar yang tercetak.
    expect($excel)->toContain('Bus 1 · 1');
    expect($pdf)->toContain('Bus 1 k1');
});

test('manifes Excel menandai peserta yang perlu perhatian', function () {
    $excel = view('exports.orcha-manifes-excel', [
        'daftar' => snkBaris(),
        'kesehatan' => [7 => snkKesehatan()],
        'saringan' => [],
    ])->render();

    expect($excel)->toContain('PERLU PERHATIAN');
    expect($excel)->toContain('Asma ringan');
    // Kontak darurat ikut: yang menelepon saat terjadi sesuatu tour leader.
    expect($excel)->toContain('08987654321');
    // Yang belum mengisi disebut apa adanya, bukan dikosongkan diam-diam.
    expect($excel)->toContain('belum diisi');
});

test('manifes Excel dikelompokkan per titik jemput, sama seperti PDF-nya', function () {
    /*
     | Itulah urutan kerja tour leader di lapangan: berhenti, panggil nama,
     | lanjut. Daftar yang urut nama memaksanya memindai seluruh lembar di
     | setiap pemberhentian.
     */
    $excel = view('exports.orcha-manifes-excel', [
        'daftar' => snkBaris(),
        'kesehatan' => [7 => snkKesehatan()],
        'saringan' => [],
    ])->render();

    expect($excel)->toContain('Janti — 1 orang');
    expect($excel)->toContain('Terminal Giwangan — 2 orang');
});

test('manifes Excel ditolak untuk akun tanpa izin data kesehatan', function () {
    test()->actingAs(snkAdmin(kesehatan: false))
        ->get('/admin/orcha/pendaftaran-manifes-excel')
        ->assertForbidden();
});

test('lembar untuk satu pendaftaran juga memuat kursi dan kamarnya', function () {
    /*
     | Dulu lembar untuk tour leader ini tidak memuat pembagian sama sekali:
     | ada di layar admin, dan yang berdiri di parkiran tetap mengatur tempat
     | duduk dengan tangan.
     */
    $html = view('exports.orcha-manifest-pdf', [
        'pendaftaran' => snkBaris()[0],
        'riwayat' => snkKesehatan(),
    ])->render();

    expect($html)->toContain('Bus &amp; kursi');
    expect($html)->toContain('Kamar');
    expect($html)->toContain('201');
    // Jenis kelamin ikut sebagai huruf tunggal di sebelah namanya.
    expect($html)->toContain('(L)');
});

test('manifes ditutup ringkasan bus & kamar supaya satu lembar cukup', function () {
    /*
     | Daftar di atas menjawab "siapa naik di mana" — urutan kerja di titik
     | jemput. Dua pertanyaan berikutnya datang belakangan dan di tempat lain:
     | berapa orang per bus (menghitung kepala sebelum berangkat) dan siapa
     | sekamar dengan siapa (menyerahkan kunci di hotel).
     */
    $html = view('exports.orcha-manifest-daftar-pdf', [
        'daftar' => snkBaris(),
        'kesehatan' => [7 => snkKesehatan()],
        'saringan' => [],
    ])->render();

    expect($html)->toContain('Ringkasan Bus &amp; Kamar');
    expect($html)->toContain('Nomor kursi yang terisi');
    // Kamar 201 berisi Budi (L) dan Siti (P) → ditandai untuk ditanyakan.
    expect($html)->toContain('L &amp; P satu kamar, mohon dicek');
    expect($html)->toContain('Budi Santoso, Siti Aminah');
});
