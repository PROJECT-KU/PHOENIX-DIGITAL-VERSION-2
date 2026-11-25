<?php

use App\Livewire\Pages\Admin\Kegiatan\KegiatanKalender;
use App\Models\EmployeeDetail;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Agenda Orcha yang ditumpangkan ke Kalender Kegiatan.
 *
 * Keberangkatan rombongan dan penyewaan kendaraan tidak ada di basis data
 * lemon; keduanya diambil dari API Orcha saat halaman digambar. Yang dijaga
 * di sini tiga hal yang tidak akan terlihat salah di layar sampai keadaannya
 * kebetulan pas: siapa yang boleh melihatnya, apa yang terjadi saat Orcha
 * mati, dan bahwa baris Orcha tidak bisa disunting dari kalender.
 */

/*
 | Nama penolong diawali 'kalOrcha'. Penolong Pest hidup di ruang nama GLOBAL:
 | dua berkas uji yang sama-sama punya fungsi bernama orang() akan saling
 | menimpa, dan --filter tidak akan pernah menangkapnya karena sendirian
 | keduanya hijau.
 */
function kalOrchaOrang(array $izin = ['view_kegiatan']): User
{
    $peran = Role::firstOrCreate(['name' => 'uji-kalender-orcha'], ['description' => 'Peran uji']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'kegiatan', 'description' => 'uji']
        );

        if (! $peran->permissions()->where('permissions.id', $p->id)->exists()) {
            $peran->permissions()->attach($p->id);
        }
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function kalOrchaSetel(): void
{
    /*
     | Alamatnya LENGKAP sampai /api/v1, sama seperti ORCHA_API_URL sungguhan
     | dan sama dengan uji Orcha lain di berkas ini.
     |
     | Versi pertama uji ini menyetelnya 'https://orcha.uji' saja, dan
     | OrchaClient merakit 'https://orcha.uji/kalender' — yang tidak pernah
     | cocok dengan pola Http::fake('*​/api/v1/kalender*'). Permintaannya lalu
     | lolos keluar dan gagal, sehingga lima uji merah seolah-olah kodenya
     | yang salah.
     */
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    // Hasil API disimpan semenit. Tanpa dibersihkan, uji kedua membaca
    // jawaban uji pertama dan lulus karena alasan yang salah.
    Cache::flush();
}

function kalOrchaJawab(array $data): void
{
    Http::fake(['*/api/v1/kalender*' => Http::response(['data' => $data], 200)]);
}

function kalOrchaBaris(array $ubah = []): array
{
    return array_merge([
        'sumber' => 'pendaftaran',
        'kategori' => 'private_trip',
        'kategori_label' => 'Private Trip',
        'id' => '9a1f0000-0000-4000-8000-000000000001',
        'kode' => 'OT-2612-ABC123',
        'judul' => 'Private Trip Bromo',
        'mulai' => now()->startOfMonth()->addDays(9)->toDateString(),
        'selesai' => now()->startOfMonth()->addDays(11)->toDateString(),
        'pemesan' => 'Bu Rina',
        'jumlah_peserta' => 18,
        'titik_jemput' => 'Terminal Giwangan',
        'status' => 'dp_masuk',
        'status_label' => 'DP Masuk',
    ], $ubah);
}

/* ------------------------------------------------------------- Hak akses */

test('agenda Orcha hanya terlihat oleh pemegang izin akses_orcha', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    $tanpaIzin = kalOrchaOrang(['view_kegiatan']);

    Livewire::actingAs($tanpaIzin)->test(KegiatanKalender::class)
        ->assertDontSee('Private Trip Bromo');

    // Tanpa izin, API-nya bahkan tidak boleh dipanggil: data pelanggan tidak
    // perlu melintas hanya untuk kemudian disembunyikan.
    Http::assertNothingSent();
});

test('pemegang izin akses_orcha melihat keberangkatan beserta jumlah pesertanya', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        // Jumlah peserta ikut di judul balok: balok di kisi hanya menampilkan
        // judul, dan "berapa orang" adalah pertanyaan pertamanya.
        ->assertSee('Private Trip Bromo — 18 orang');
});

/* --------------------------------------------------- Orcha tidak terjangkau */

test('Orcha yang mati tidak menjatuhkan kalender, dan dikatakan apa adanya', function () {
    kalOrchaSetel();
    Http::fake(['*/api/v1/kalender*' => Http::response(['message' => 'meledak'], 500)]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Kegiatan::create([
        'judul' => 'Rapat Mingguan Tim',
        'jenis' => 'rapat',
        'mulai' => now()->startOfMonth()->addDays(5)->setTime(9, 0),
        'seharian' => false,
        'dibuat_oleh' => $boleh->id,
    ]);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->assertOk()
        // Kegiatan lemon sendiri tetap lengkap.
        ->assertSee('Rapat Mingguan Tim')
        // Dan diamnya dilarang: bulan tanpa keberangkatan dan bulan yang
        // keberangkatannya gagal diambil tampak persis sama.
        ->assertSee('Agenda Orcha belum bisa ditampilkan');
});

test('kegagalan sesaat tidak dibekukan satu menit di cache', function () {
    kalOrchaSetel();

    /*
     | fakeSequence, BUKAN dua kali Http::fake().
     |
     | Http::fake() yang kedua MENAMBAH stub, tidak mengganti yang pertama —
     | dan yang pertama cocoklah yang menang. Versi pertama uji ini memanggil
     | fake() dua kali, jadi jawaban 500 tetap dipakai pada giliran kedua dan
     | ujinya merah seolah-olah cache-nya yang salah.
     |
     | Dengan sequence, satu pola punya dua jawaban berurutan: gagal dulu,
     | lalu berhasil — persis keadaan yang diuji.
     */
    Http::fakeSequence('*/api/v1/kalender*')
        ->pushStatus(500)
        ->push(['data' => [kalOrchaBaris()]], 200);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);
    Livewire::actingAs($boleh)->test(KegiatanKalender::class)->assertSee('Agenda Orcha belum');

    // Orcha hidup lagi. Kalau kegagalan tadi tersimpan sebagai larik kosong,
    // admin yang menyegarkan halaman akan tetap melihat kalender tanpa
    // keberangkatan, tanpa tahu kenapa.
    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->assertSee('Private Trip Bromo')
        ->assertDontSee('Agenda Orcha belum bisa ditampilkan');
});

/* --------------------------------------------------------- Hanya bisa dibaca */

test('baris Orcha tidak bisa disunting atau dihapus dari kalender', function () {
    kalOrchaSetel();
    $tanggal = now()->startOfMonth()->addDays(9)->toDateString();
    kalOrchaJawab([kalOrchaBaris()]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha', 'edit_kegiatan', 'delete_kegiatan']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->call('pilihTanggal', $tanggal)
        ->assertSee('Buka di Orcha')
        // Rinciannya tetap terbaca di daftar hari terpilih.
        ->assertSee('Terminal Giwangan')
        ->assertSee('OT-2612-ABC123');
});

test('sewa kendaraan ikut, dan tidak mengaku punya jumlah peserta', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris([
        'sumber' => 'penyewaan',
        'kategori' => 'sewa_kendaraan',
        'id' => '9a1f0000-0000-4000-8000-000000000002',
        'kode' => 'SK-2612-XYZ789',
        'judul' => 'HiAce Premio',
        'jumlah_peserta' => null,
        'tujuan' => 'Bromo',
        'titik_jemput' => 'Bandara YIA',
        'status_label' => 'DP Masuk',
    ])]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->assertSee('HiAce Premio')
        // Yang disewa kendaraannya, bukan kursinya — judulnya tidak boleh
        // menempelkan "0 orang".
        ->assertDontSee('HiAce Premio — 0 orang');
});

/* ----------------------------------------------------------------- Saringan */

test('saringan jenis memisahkan keberangkatan dari sewa kendaraan', function () {
    kalOrchaSetel();
    kalOrchaJawab([
        kalOrchaBaris(),
        kalOrchaBaris([
            'sumber' => 'penyewaan',
            'id' => '9a1f0000-0000-4000-8000-000000000002',
            'judul' => 'HiAce Premio',
            'jumlah_peserta' => null,
        ]),
    ]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->set('saringJenis', 'orcha_sewa')
        ->assertSee('HiAce Premio')
        ->assertDontSee('Private Trip Bromo');
});

test('saringan "Saya saja" menyembunyikan agenda Orcha', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    // Keberangkatan Orcha bukan milik siapa pun di lemon — ia tidak punya
    // peserta maupun pembuat, jadi menampilkannya di "agenda saya" berarti
    // mengaku itu urusan orang yang sedang membuka halaman.
    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->set('hanyaSaya', true)
        ->assertDontSee('Private Trip Bromo');
});

/* ----------------------------------------------- RAB yang sudah deal */

test('RAB yang sudah deal tampil sebagai pita tersendiri', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris([
        'sumber' => 'rab',
        'kategori' => 'private_trip',
        'id' => 25,
        'kode' => 'RAB-2611-MZBF',
        'judul' => 'Private Trip Dieng 2H1M',
        'jumlah_peserta' => 15,
        'titik_jemput' => null,
        'tujuan' => 'Dieng, Jawa Tengah',
        'status' => 'disetujui',
        'status_label' => 'Disetujui',
    ])]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->assertSee('Private Trip Dieng 2H1M — 15 orang')
        // Warnanya sendiri: "sudah deal" dan "sudah bayar" tidak boleh
        // terbaca sama, karena yang satu belum mengeluarkan uang.
        ->assertSee('Sudah Deal');
});

test('saringan memisahkan yang sudah deal dari yang sudah berangkat', function () {
    kalOrchaSetel();
    kalOrchaJawab([
        kalOrchaBaris(),
        kalOrchaBaris([
            'sumber' => 'rab', 'id' => 25, 'judul' => 'RAB Dieng',
            'jumlah_peserta' => 15, 'status_label' => 'Disetujui',
        ]),
    ]);

    $boleh = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($boleh)->test(KegiatanKalender::class)
        ->set('saringJenis', 'orcha_deal')
        ->assertSee('RAB Dieng')
        ->assertDontSee('Private Trip Bromo');
});

/* --------------------------------- Kalender bukan tempat membuat data */

test('kalender tidak menawarkan jalan membuat data Orcha', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    /*
     | Sempat ada laci "Tambah" berisi jalan pintas ke borang pendaftaran
     | Orcha dan catat sewa. Dicabut atas keputusan pemilik: kalender ini
     | MEMBACA perjalanan Orcha, bukan tempat membuatnya.
     |
     | Dijaga lewat uji supaya tidak diam-diam kembali — pintasan semacam itu
     | mudah sekali ditambahkan lagi oleh orang yang merasa "sekalian saja".
     */
    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'create_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->assertDontSee('Daftarkan rombongan')
        ->assertDontSee('Catat sewa')
        // Tombol Tambah tetap ada — untuk kegiatan lemon, yang memang di sini.
        ->assertSee('Tambah');
});

test('dikatakan bahwa perjalanan Orcha muncul sendiri', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    // Tanpa kalimat ini orang memasukkannya manual sebagai kegiatan lemon,
    // dan tanggalnya terbaca terisi dua kali.
    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->assertSee('muncul sendiri');
});

test('yang tanpa izin Orcha tidak dibingungkan kalimat itu', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris()]);

    // Ia tidak pernah melihat satu pun pita Orcha, jadi keterangan tentang
    // pita itu cuma menambah kalimat yang tidak ada rujukannya di layarnya.
    Livewire::actingAs(kalOrchaOrang(['view_kegiatan']))
        ->test(KegiatanKalender::class)
        ->assertDontSee('muncul sendiri');
});

/* ------------------------------- Jadwal yang sedang dijual (belum deal) */

/*
 | Sumber keempat Orcha: tanggal yang sudah dibuka untuk dijual, belum ada
 | deal, dan belum ada yang bayar.
 |
 | Yang dijaga di sini bahwa ia terbaca BERBEDA dari komitmen. Sebelum pita
 | ini ada, tanggal yang open trip-nya sudah tayang terlihat kosong di
 | kalender, dan private trip ditawarkan di tanggal yang armadanya sudah
 | dialokasikan.
 */
function kalOrchaJual(array $ubah = []): array
{
    return array_merge(kalOrchaBaris(), [
        'sumber' => 'katalog',
        'kategori' => 'open_trip',
        'kategori_label' => 'Open Trip',
        'id' => 77,
        'paket_id' => 12,
        'kode' => null,
        'judul' => 'Open Trip Banyuwangi',
        'pemesan' => null,
        'jumlah_peserta' => null,
        'titik_jemput' => null,
        'tujuan' => null,
        'status' => 'dijual',
        'status_label' => 'Dijual — belum ada pendaftar',
    ], $ubah);
}

test('jadwal yang sedang dijual terbaca berbeda dari rombongan yang berangkat', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaJual()]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        /*
         | BUKAN "— 0 orang" dan bukan "— 1 orang".
         |
         | Angka pada sumber ini bukan rombongan yang berangkat melainkan yang
         | mendaftar tanpa membayar; dikalimatkan supaya tidak terbaca sebagai
         | trip yang pesertanya sedikit.
         */
        ->assertSee('Open Trip Banyuwangi — belum ada pendaftar')
        ->assertDontSee('Open Trip Banyuwangi — 0 orang');
});

test('pendaftar yang belum bayar disebut sebagai belum bayar, bukan sebagai peserta', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaJual([
        'jumlah_peserta' => 3,
        'status_label' => 'Dijual — 3 pendaftar belum bayar',
    ])]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->assertSee('Open Trip Banyuwangi — 3 belum bayar')
        ->assertDontSee('Open Trip Banyuwangi — 3 orang');
});

test('jadwal dijual menaut ke borang PAKETNYA, bukan ke id keberangkatannya', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaJual()]);

    // Tautannya hanya tergambar di panel hari terpilih; balok di kisi cuma
    // berisi judul.
    $halaman = Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->call('pilihTanggal', now()->startOfMonth()->addDays(9)->toDateString());

    // paket_id 12, bukan id 77: menaut dengan id keberangkatan akan membuka
    // paket milik rombongan lain, diam-diam dan tanpa galat.
    $halaman->assertSee(route('admin.orcha.paket.ubah', 12), escape: false)
        ->assertDontSee(route('admin.orcha.paket.ubah', 77), escape: false);
});

test('Orcha versi lama tanpa paket_id jatuh ke daftar paket, bukan ke tautan yang salah', function () {
    kalOrchaSetel();
    $baris = kalOrchaJual();
    unset($baris['paket_id']);
    kalOrchaJawab([$baris]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->call('pilihTanggal', now()->startOfMonth()->addDays(9)->toDateString())
        ->assertSee(route('admin.orcha.paket'), escape: false);
});

test('jadwal dijual tidak bisa disunting dari kalender', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaJual()]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha', 'edit_kegiatan']))
        ->test(KegiatanKalender::class)
        ->call('pilihTanggal', now()->startOfMonth()->addDays(9)->toDateString())
        // Tombol Hapus TIDAK diperiksa lewat nama kelasnya: kelas itu juga
        // hidup di blok skrip SweetAlert halaman ini, jadi ia selalu ada di
        // HTML entah tombolnya tergambar atau tidak.
        ->assertSee('Buka di Orcha');
});

test('saringan memisahkan yang dijual dari yang sudah deal', function () {
    kalOrchaSetel();
    kalOrchaJawab([
        kalOrchaJual(),
        kalOrchaBaris(['sumber' => 'rab', 'judul' => 'Open Trip Dieng', 'kode' => 'RAB-2612-ZZZ']),
    ]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->set('saringJenis', 'orcha_jual')
        ->assertSee('Open Trip Banyuwangi')
        ->assertDontSee('Open Trip Dieng');
});

/*
 | RAB open trip yang belum punya tanggal jualan: rencana, bukan deal.
 |
 | Sebelumnya ia digambar ungu "Sudah Deal — 13 orang" di tanggal yang
 | pendaftarnya NOL, sementara tanggal lain yang keadaannya persis sama
 | digambar abu "Dijual". Angka 13 itu TARGET di RAB, bukan orang yang ikut.
 */
test('rencana open trip tidak digambar sebagai deal, dan angkanya disebut target', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris([
        'sumber' => 'rab_jual',
        'kode' => 'RAB-2612-UMUM',
        'judul' => 'Open Trip Dieng',
        'pemesan' => null,
        'jumlah_peserta' => null,
        'target_peserta' => 13,
        'paket_id' => 10,
        'status' => 'dijual',
        'status_label' => 'Harga disetujui — belum ada tanggal keberangkatan yang dijual',
    ])]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->assertSee('Open Trip Dieng — rencana 13 orang, belum ada tanggal dijual')
        // Kalimat yang dulu menyesatkan.
        ->assertDontSee('Open Trip Dieng — 13 orang');
});

test('rencana open trip ikut saringan Dijual, bukan saringan Sudah Deal', function () {
    kalOrchaSetel();
    kalOrchaJawab([
        kalOrchaBaris([
            'sumber' => 'rab_jual', 'judul' => 'Open Trip Dieng', 'kode' => 'RAB-2612-UMUM',
            'jumlah_peserta' => null, 'target_peserta' => 13, 'paket_id' => 10,
        ]),
        kalOrchaBaris(['sumber' => 'rab', 'judul' => 'Private Trip Bromo Sari', 'kode' => 'RAB-2612-SARI']),
    ]);

    $orang = kalOrchaOrang(['view_kegiatan', 'akses_orcha']);

    Livewire::actingAs($orang)->test(KegiatanKalender::class)
        ->set('saringJenis', 'orcha_jual')
        ->assertSee('Open Trip Dieng')
        ->assertDontSee('Private Trip Bromo Sari');

    Livewire::actingAs($orang)->test(KegiatanKalender::class)
        ->set('saringJenis', 'orcha_deal')
        ->assertSee('Private Trip Bromo Sari')
        ->assertDontSee('Open Trip Dieng');
});

test('rencana open trip menaut ke penyusun RAB, bukan ke borang paket', function () {
    kalOrchaSetel();
    kalOrchaJawab([kalOrchaBaris([
        'sumber' => 'rab_jual', 'id' => 55, 'paket_id' => 10,
        'judul' => 'Open Trip Dieng', 'jumlah_peserta' => null, 'target_peserta' => 13,
    ])]);

    Livewire::actingAs(kalOrchaOrang(['view_kegiatan', 'akses_orcha']))
        ->test(KegiatanKalender::class)
        ->call('pilihTanggal', now()->startOfMonth()->addDays(9)->toDateString())
        // Yang ingin dilihat rencana perjalanannya, bukan borang paketnya.
        ->assertSee(route('admin.orcha.rab.susun', 55), escape: false)
        ->assertDontSee(route('admin.orcha.paket.ubah', 10), escape: false);
});
