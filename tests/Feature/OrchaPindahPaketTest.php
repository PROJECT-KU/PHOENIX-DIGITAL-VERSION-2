<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Memindahkan pendaftaran ke paket lain dari lemon.
 *
 * Yang dijaga di sini bukan perpindahannya — itu urusan Orcha — melainkan
 * dua hal yang hanya bisa salah di sisi layar: pilihan tanggal yang
 * tertinggal dari paket sebelumnya, dan kelebihan bayar yang terlanjur
 * dilewati tanpa diputuskan.
 */
function pindahPaketOrang(): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-pindah-paket'], ['description' => 'Peran uji']);

    $izin = Permission::firstOrCreate(
        ['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']
    );

    if (! $peran->permissions()->where('permissions.id', $izin->id)->exists()) {
        $peran->permissions()->attach($izin->id);
    }

    $u = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $u->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $u->fresh();
}

/**
 * Bentuk baris pendaftaran yang dipakai halaman detail.
 *
 * Disalin dari barisUbahPalsu() pada OrchaUbahPendaftaranTest — bukan ditebak
 * medan demi medan dari galat yang muncul. Halaman ini membaca puluhan kunci,
 * dan menambalnya satu per satu setiap kali "Undefined array key" lewat hanya
 * menghasilkan data uji yang bentuknya tidak pernah menyerupai aslinya.
 */
function evaPalsu(array $ubah = []): array
{
    return array_merge([
        'id' => 11, 'kode' => 'OT-2311-MZVT8W', 'nama' => 'Eva',
        'whatsapp' => '081234567890', 'email' => 'eva@contoh.test',
        'jumlah_peserta' => 4, 'pendamping_gratis' => 0, 'peserta_dibayar' => 4,
        'peserta' => [],
        'jemput_per_titik' => [], 'bus_per_kelompok' => [], 'kamar_per_kelompok' => [],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
        'paket' => ['id' => 3, 'nama' => 'Open Trip Dieng', 'titik_jemput' => []],
        'keberangkatan_id' => 11,
        'keberangkatan' => ['id' => 11, 'rentang_tanggal' => '6 Des 2026', 'aktif' => true],
        'tanggal_berangkat' => '2026-12-06',
        'hari_ke_berangkat' => 11, 'pengingat_pelunasan_pada' => null,
        'titik_jemput' => 'Jogja', 'catatan' => null, 'catatan_internal' => null,
        'status' => 'dp_masuk', 'status_label' => 'DP Masuk',
        'riwayat_penggantian' => [], 'surat_penggantian' => null, 'surat_penggantian_pada' => null,
        'tautan_kesehatan' => 'https://orchajourney.com/riwayat-kesehatan?kode=OT-2311-MZVT8W',
        'dibuat_pada' => now()->toIso8601String(),
        'tagihan' => [
            'total' => 1400000, 'total_teks' => 'Rp 1.400.000',
            'sudah' => 420000, 'sudah_teks' => 'Rp 420.000',
            'sisa' => 980000, 'sisa_teks' => 'Rp 980.000',
            'dp' => 420000, 'dp_teks' => 'Rp 420.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'pelunasan',
        ],
        'pembayaran' => [], 'pembatalan' => null,
    ], $ubah);
}

function pindahPaketSetel(?array $balasanPindah = null, int $status = 200): void
{
    // Alamat LENGKAP sampai /api/v1 — tanpa itu pola fake tidak pernah cocok.
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    Cache::flush();

    $kb = fn (int $id, string $tgl, bool $aktif = true) => [
        'id' => $id, 'tanggal_berangkat' => $tgl, 'rentang_tanggal' => $tgl,
        'aktif' => $aktif, 'penuh' => false, 'sisa_kursi' => 10,
    ];

    Http::fake([
        '*/api/v1/rujukan*' => Http::response(['data' => [
            'paket_wisata' => [
                ['id' => 3, 'nama' => 'Open Trip Dieng', 'kategori' => 'open_trip',
                    'keberangkatan' => [$kb(11, '2026-12-06')]],
                ['id' => 4, 'nama' => 'Private Trip Dieng', 'kategori' => 'private_trip',
                    'keberangkatan' => [$kb(21, '2026-12-05'), $kb(22, '2026-12-12')]],
                ['id' => 5, 'nama' => 'Private Trip Bromo', 'kategori' => 'private_trip',
                    'keberangkatan' => [$kb(31, '2026-12-20')]],
            ],
            'status_pendaftaran' => ['baru' => 'Baru', 'dp_masuk' => 'DP Masuk', 'lunas' => 'Lunas', 'batal' => 'Batal'],
            'jenis_pembayaran' => ['dp' => 'Uang Muka (DP)', 'pelunasan' => 'Pelunasan'],
            'pembayaran' => ['pelunasan_hari_sebelum' => 5],
        ]], 200),

        // Harus berdiri SEBELUM pola pendaftaran/11 yang longgar di bawah.
        // Pola itu memulangkan satu baris pendaftaran (larik berkunci), dan
        // pemilih RAB menelusurinya sebagai daftar — galatnya muncul jauh dari
        // sini, di tengah <option>.
        //
        // Ditulis dengan // dan bukan komentar blok: polanya sendiri memuat
        // bintang-garis-miring, yang menutup komentar blok lebih awal.
        '*/api/v1/pendaftaran/11/rab-cocok*' => Http::response(['data' => [
            [
                'id' => 25, 'kode' => 'RAB-2611-MZBF', 'judul' => 'Private Trip Dieng 1 Hari',
                'jenis' => 'private', 'tanggal_mulai' => '2026-12-04', 'tanggal_teks' => '4 Des 2026',
                'jumlah_peserta' => 4, 'harga_jual' => 395000, 'harga_modal' => 0,
                'biaya_tetap' => 1625000, 'modal_total' => 1625000,
                'untung' => -45000, 'punya_paket' => true, 'terpakai' => null, 'peserta_cocok' => true,
            ],
        ]], 200),

        '*/api/v1/pendaftaran/11/pindah-paket' => Http::response(
            $balasanPindah ?? ['pesan' => 'Pendaftaran dipindahkan ke Private Trip Dieng.'], $status
        ),

        '*/api/v1/pendaftaran/11*' => Http::response(['data' => evaPalsu()], 200),

        '*' => Http::response(['data' => []], 200),
    ]);
}

function bukaDetailPindah()
{
    return Livewire::actingAs(pindahPaketOrang())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 11]);
}

test('tombol pindah paket ada di laci Ubah', function () {
    pindahPaketSetel();

    bukaDetailPindah()->assertSee('Pindah paket');
});

test('tanggal yang tertinggal dari paket sebelumnya dibuang', function () {
    pindahPaketSetel();

    /*
     | Tanpa pembuangan ini, berganti paket meninggalkan id tanggal milik
     | paket lama — dan Orcha menolaknya dengan "tanggal itu bukan milik paket
     | ini", galat yang tidak ada hubungannya dengan apa yang baru saja
     | dilakukan admin.
     */
    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('paketTujuan', '4')
        // Private Trip Dieng punya DUA tanggal, jadi tidak ada yang dipilihkan.
        ->assertSet('keberangkatanTujuan', '')
        ->set('keberangkatanTujuan', '21')
        ->set('paketTujuan', '5')
        // Pindah ke paket lain: pilihan tanggal lama tidak boleh tertinggal.
        // Bromo cuma punya satu tanggal, jadi langsung terisi.
        ->assertSet('keberangkatanTujuan', '31');
});

/*
 | Kebalikan dari aturan yang dulu dijaga di sini.
 |
 | Dulu paket yang sedang dipakai dibuang dari daftar. Itu menutup satu-satunya
 | tujuan yang benar pada kasus nyata: Open Trip Dieng yang diubah menjadi
 | Private Trip Dieng tetap paket yang SAMA, sementara pendaftar lamanya masih
 | memegang harga lama yang dibekukan — dan di layar fiturnya terbaca rusak,
 | karena paket yang dicari admin tidak pernah muncul.
 |
 | Perpindahan yang benar-benar tidak mengubah apa pun tetap ditolak Orcha.
 */
test('paket yang sedang dipakai tetap ditawarkan, karena paketnya bisa berubah', function () {
    pindahPaketSetel();

    $k = bukaDetailPindah()->call('bukaFormulirPindahPaket');

    expect(collect($k->instance()->pilihanPaket())->pluck('id')->all())
        ->toBe([3, 4, 5]);
});

test('RAB milik pelanggan yang sama ditawarkan beserta modalnya', function () {
    pindahPaketSetel();

    $k = bukaDetailPindah()->call('bukaFormulirPindahPaket');

    $daftar = $k->instance()->pilihanRab();

    expect($daftar)->toHaveCount(1)
        ->and($daftar[0]['kode'])->toBe('RAB-2611-MZBF')
        // Modal ikut RAB, bukan modal paket katalog — seluruh alasan jalur ini ada.
        ->and($daftar[0]['modal_total'])->toBe(1625000);
});

test('memilih RAB mengosongkan isian manual, supaya layar tidak menampilkan angka lain daripada yang terkirim', function () {
    pindahPaketSetel();

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('paketTujuan', '4')
        ->set('keberangkatanTujuan', '21')
        ->set('hargaJualBaru', '450000')
        ->set('rabTujuan', '25')
        ->assertSet('paketTujuan', '')
        ->assertSet('keberangkatanTujuan', '')
        ->assertSet('hargaJualBaru', '');
});

test('rab_id ikut terkirim, dan paket serta tanggalnya tidak lagi wajib', function () {
    pindahPaketSetel();

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('rabTujuan', '25')
        ->call('pindahPaket', false)
        ->assertDispatched('order-updated');

    Http::assertSent(function ($permintaan) {
        if (! str_contains($permintaan->url(), '/pindah-paket')) {
            return false;
        }

        $muatan = $permintaan->data();

        // Yang dikirim HANYA rab_id: paket dan tanggalnya disusun Orcha dari
        // RAB, dan mengirim tebakan dari sini berarti dua sumber untuk satu
        // angka yang sama.
        return ($muatan['rab_id'] ?? null) === 25
            && ! array_key_exists('travel_package_id', $muatan)
            && ! array_key_exists('keberangkatan_id', $muatan);
    });
});

test('tanpa RAB maupun paket, perpindahan ditahan di layar', function () {
    pindahPaketSetel();

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->call('pindahPaket', false)
        ->assertDispatched('toast-error');
});

test('kelebihan bayar ditahan, dan pilihannya ditawarkan beserta angkanya', function () {
    pindahPaketSetel([
        'pesan' => 'Tagihan barunya Rp 400.000, sedangkan yang sudah dibayar Rp 2.000.000. '
            .'Kelebihan Rp 1.600.000 mau dikembalikan atau disimpan sebagai saldo untuk trip berikutnya?',
        'butuh_keputusan_kelebihan' => true,
        'kelebihan' => 1600000,
    ], 422);

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('paketTujuan', '5')
        ->call('pindahPaket', false)
        // Jendelanya TETAP terbuka: keputusannya diminta di tempat, bukan
        // lewat toast merah yang hilang sebelum sempat dibaca.
        ->assertSet('bukaPindahPaket', true)
        ->assertSet('kelebihanBayar', 1600000)
        ->assertSee('Simpan jadi saldo')
        ->assertSee('Kembalikan ke pelanggan');
});

test('kuota kurang ditawarkan penegasan, bukan pilihan saldo', function () {
    pindahPaketSetel([
        'pesan' => 'Tanggal 2026-12-20 tinggal 2 kursi, sedangkan rombongan ini 4 orang.',
        'butuh_paksa' => true,
    ], 422);

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('paketTujuan', '5')
        ->call('pindahPaket', false)
        ->assertSet('bukaPindahPaket', true)
        // Nol: ini bukan soal uang, jadi pilihan saldo/refund tidak boleh muncul.
        ->assertSet('kelebihanBayar', 0)
        ->assertSee('Lanjutkan, lebihi kuota')
        ->assertDontSee('Simpan jadi saldo');
});

test('pindah tanpa memilih paket ditolak sebelum menyentuh Orcha', function () {
    pindahPaketSetel();

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->call('pindahPaket', false)
        ->assertSet('bukaPindahPaket', true);

    // Tidak ada permintaan pindah yang pernah dikirim.
    Http::assertNotSent(fn ($r) => str_contains($r->url(), 'pindah-paket'));
});

test('perpindahan yang berhasil menutup jendelanya', function () {
    pindahPaketSetel();

    bukaDetailPindah()
        ->call('bukaFormulirPindahPaket')
        ->set('paketTujuan', '5')
        ->call('pindahPaket', false)
        ->assertSet('bukaPindahPaket', false);
});
