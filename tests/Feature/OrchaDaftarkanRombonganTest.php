<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaDaftarkanRombongan;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Mendaftarkan rombongan private trip dan study tour dari lemon.
 *
 * Keduanya tidak pernah mendaftar lewat website, dan itu bukan kekurangan
 * melainkan bentuk jualannya. Tetapi begitu disepakati, rombongannya HARUS
 * masuk sistem — tanpa itu ia tidak punya kode pemesanan, tidak bisa mengisi
 * riwayat kesehatan, tidak masuk manifes, dan tidak terhitung di laporan
 * keuntungan.
 */
function adminRombongan(): User
{
    $role = Role::create(['name' => 'uji-romb-'.uniqid(), 'description' => 'Peran untuk uji rombongan']);

    $permission = Permission::firstOrCreate(
        ['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']
    );
    $role->permissions()->attach($permission->id);

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/pendaftaran') && $permintaan->method() === 'POST') {
            return Http::response(['data' => [
                'id' => 12, 'kode' => 'OT-0309-K7QMXV', 'nama' => 'Panitia Sekolah',
                'whatsapp' => '081234567890', 'jumlah_peserta' => 3,
                'paket' => ['id' => 5, 'nama' => 'Study Tour SMA Uji', 'titik_jemput' => []],
                /*
                 | Alamatnya disetel lewat config supaya uji bisa menggantinya.
                 |
                 | Http::fake yang dipanggil dua kali MENAMBAH tiruan, bukan
                 | menggantinya — dan yang terdaftar lebih dulu menang. Uji yang
                 | memasang tiruannya sendiri di tengah jalan lalu tidak berlaku,
                 | tanpa galat apa pun.
                 */
                'tautan_kesehatan' => config('uji.tautan_kesehatan',
                    'https://orchajourney.com/riwayat-kesehatan?kode=OT-0309-K7QMXV'),
            ]], 201);
        }

        return Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru'],
            'paket_wisata' => [
                ['id' => 5, 'nama' => 'Study Tour SMA Uji', 'kategori' => 'study_tour',
                    'tanggal_berangkat' => now()->addDays(30)->toDateString()],
                ['id' => 6, 'nama' => 'Open Trip Banyuwangi', 'kategori' => 'open_trip',
                    'tanggal_berangkat' => now()->addDays(10)->toDateString()],
            ],
        ]]);
    });
});

function isiRombongan($halaman)
{
    return $halaman
        ->set('paketId', '5')
        ->set('nama', 'Panitia Sekolah')
        ->set('whatsapp', '081234567890')
        ->set('jumlahPeserta', 3)
        ->set('peserta', [
            ['nama' => 'Budi', 'titik_jemput' => 'Sekolah'],
            ['nama' => 'Sari', 'titik_jemput' => 'Sekolah'],
            ['nama' => 'Rian', 'titik_jemput' => 'Sekolah'],
        ]);
}

test('paketnya dikelompokkan, private trip dan study tour didahulukan', function () {
    // Merekalah yang mengisi layar ini. Open trip tetap ada — sebagian
    // pemesanannya pun datang lewat telepon.
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->assertSeeInOrder(['Study Tour', 'Open Trip']);
});

test('menempel daftar nama membuang nomor urutnya', function () {
    /*
     | Daftar peserta study tour datang sebagai satu blok teks, kadang
     | bernomor. Mengetiknya ulang untuk empat puluh siswa adalah pekerjaan
     | yang membuat layar ini tidak dipakai sama sekali, dan rombongannya
     | kembali dicatat di kertas.
     */
    $halaman = Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->call('tempel', "1. Budi Santoso\n2) Sari Dewi\n3 - Rian Pratama\n\n");

    expect($halaman->get('peserta'))->toHaveCount(3)
        ->and($halaman->get('peserta')[0]['nama'])->toBe('Budi Santoso')
        ->and($halaman->get('peserta')[2]['nama'])->toBe('Rian Pratama');
});

test('menempel daftar ikut menyesuaikan jumlah pesertanya', function () {
    /*
     | Angka itulah yang mengalikan harga jadi tagihan. Membiarkannya
     | tertinggal di angka lama setelah empat puluh nama ditempel berarti
     | rombongan empat puluh orang ditagih untuk satu orang.
     */
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->call('tempel', "Budi\nSari\nRian\nDewi")
        ->assertSet('jumlahPeserta', 4);
});

test('titik jemput utama menular ke tiap baris yang ditempel', function () {
    // Rombongan sekolah berangkat dari satu titik yang sama. Mengetiknya empat
    // puluh kali adalah pekerjaan yang akhirnya dilewati, dan manifes sopirnya
    // kosong.
    $halaman = Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('titikJemput', 'Halaman sekolah')
        ->call('tempel', "Budi\nSari");

    expect($halaman->get('peserta')[0]['titik_jemput'])->toBe('Halaman sekolah');
});

test('paket, nama, dan nomor wajib', function () {
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->call('simpan')
        ->assertHasErrors(['paketId', 'nama', 'whatsapp']);
});

test('nama peserta yang lebih banyak daripada jumlahnya ditahan', function () {
    /*
     | Selisihnya hampir selalu berarti admin menempel daftar lalu lupa
     | menyesuaikan angkanya — dan akibatnya tagihan yang tidak sesuai dengan
     | orang yang berangkat. Ditahan sebelum siapa pun menerima kode pemesanan
     | yang salah.
     */
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->set('jumlahPeserta', 2)
        ->call('simpan')
        ->assertHasErrors('jumlahPeserta');

    Http::assertNotSent(fn ($p) => $p->method() === 'POST');
});

test('nama peserta boleh menyusul, jumlahnya saja yang harus benar', function () {
    /*
     | Panitia sering menyepakati jumlahnya lebih dulu dan menyusul daftar
     | namanya seminggu kemudian. Menahan pendaftarannya sampai daftar nama
     | lengkap berarti rombongannya tidak punya kode pemesanan justru pada masa
     | ia paling perlu dikonfirmasi.
     */
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('paketId', '5')
        ->set('nama', 'Panitia Sekolah')
        ->set('whatsapp', '081234567890')
        ->set('jumlahPeserta', 40)
        ->call('simpan')
        ->assertHasNoErrors();
});

test('yang tersimpan berpindah ke layar serah terima, bukan formulir lagi', function () {
    /*
     | Formulir yang tetap terbuka dengan pesan hijau kecil membuat admin ragu
     | apakah tersimpan, lalu menekan Simpan lagi — dan rombongan yang sama
     | masuk dua kali dengan dua kode berbeda.
     */
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->assertSee('Rombongan terdaftar')
        ->assertSee('OT-0309-K7QMXV')
        ->assertDontSee('Daftarkan rombongan</span>');
});

test('serah terima memuat tautan riwayat kesehatan apa adanya dari Orcha', function () {
    /*
     | Alamat publiknya milik Orcha. Merakitnya di lemon berarti nama rutenya
     | ditebak dari sini, dan tebakan itu diam saat rutenya berubah — tautan
     | yang salah membawa panitia ke halaman galat, dan yang menyalahkan
     | dirinya sendiri panitianya.
     */
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->assertSee('riwayat-kesehatan?kode=OT-0309-K7QMXV');
});

test('serah terima memperingatkan bahwa tautannya belum jalan sebelum dibayar', function () {
    /*
     | Formulir riwayat kesehatan menolak kode yang pesanannya belum membayar —
     | penjagaan yang memang diminta. Akibatnya tautan ini belum berguna sampai
     | statusnya dimajukan, dan yang menemukan penolakannya bukan admin
     | melainkan panitia yang sudah menyebarkannya ke empat puluh siswa.
     */
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->assertSee('belum bisa dipakai sebelum pembayaran tercatat');
});

test('tombol daftarkan lain mengosongkan layar', function () {
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->call('lagi')
        ->assertSet('hasil', [])
        ->assertSet('nama', '')
        ->assertSet('jumlahPeserta', 1)
        ->assertSee('Untuk rombongan yang sudah disepakati');
});

test('halamannya bisa dibuka, tidak tertelan rute berparameter', function () {
    /*
     | /admin/orcha/pendaftaran/{pendaftaran} terdaftar lebih dulu, jadi tanpa
     | penempatan yang benar "daftarkan" terbaca sebagai nomor pendaftaran dan
     | halaman ini tidak pernah bisa dibuka. Jebakan yang sama sudah pernah
     | kena di jalur API, pada "perhatian".
     */
    $this->actingAs(adminRombongan())
        ->get(route('admin.orcha.pendaftaran.daftarkan'))
        ->assertOk()
        ->assertSee('Daftarkan Rombongan');
});

test('nama yang ditempel BENAR-BENAR tergambar di kotaknya', function () {
    /*
     | Markup dari server tidak memuat nilai isian yang diikat wire:model; yang
     | mengisinya skrip Livewire setelah halaman hidup. Akibatnya empat puluh
     | nama yang baru ditempel tergambar sebagai empat puluh kotak kosong — dan
     | yang melihatnya menyimpulkan tempelannya gagal, lalu mengetiknya satu
     | per satu.
     |
     | Uji keadaan tidak menangkap ini: $peserta memang sudah terisi. Yang
     | perlu diperiksa MARKUP-nya, karena itu yang dilihat admin.
     */
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('titikJemput', 'Halaman sekolah')
        ->call('tempel', "Budi Santoso\nSari Dewi")
        ->assertSee('value="Budi Santoso"', false)
        ->assertSee('value="Sari Dewi"', false)
        ->assertSee('value="Halaman sekolah"', false);
});

test('jumlah peserta hasil tempelan juga tergambar, bukan kosong', function () {
    // Angka yang tergambar kosong membuat admin mengetiknya ulang — dan angka
    // itulah yang mengalikan harga jadi tagihan.
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->call('tempel', "Budi\nSari\nRian\nDewi")
        ->assertSee('value="4"', false);
});

test('SELURUH isian menulis nilainya ke markup, bukan cuma baris peserta', function () {
    /*
     | Bagian atas formulir mengidap hal yang sama: markup dari server tidak
     | memuat nilai yang diikat wire:model.
     |
     | Terasanya saat Livewire menggambar ulang halaman — misalnya sesudah
     | menempel daftar nama, atau sesudah validasi gagal. Isian yang sudah
     | diketik tergambar kosong, dan admin mengetiknya ulang seluruhnya.
     */
    Livewire::actingAs(adminRombongan())
        ->test(OrchaDaftarkanRombongan::class)
        ->set('nama', 'Panitia SMA 3')
        ->set('whatsapp', '081234567890')
        ->set('email', 'panitia@sekolah.test')
        ->set('titikJemput', 'Halaman sekolah')
        ->set('hargaJual', '750000')
        ->set('catatan', 'Sudah termasuk konsumsi')
        ->assertSee('value="Panitia SMA 3"', false)
        ->assertSee('value="081234567890"', false)
        ->assertSee('value="panitia@sekolah.test"', false)
        ->assertSee('value="Halaman sekolah"', false)
        ->assertSee('value="750000"', false)
        // Textarea menyimpan nilainya sebagai isi elemen, bukan atribut.
        ->assertSee('>Sudah termasuk konsumsi</textarea>', false);
});

test('tautan yang menunjuk mesin lokal ditahan sebelum terkirim', function () {
    /*
     | route() merakit alamatnya dari APP_URL di server Orcha. Bila kunci itu
     | masih berisi localhost — hal yang lolos diam-diam karena seluruh sisi
     | lain aplikasi tetap jalan — tautannya terkirim ke empat puluh siswa dan
     | tidak bisa dibuka siapa pun kecuali orang yang duduk di server itu.
     |
     | Kegagalannya baru ketahuan lewat keluhan, dan yang menanggung malunya
     | panitia yang menyebarkannya.
     */
    config()->set('uji.tautan_kesehatan', 'http://127.0.0.1:8000/riwayat-kesehatan?kode=OT-0309-K7QMXV');

    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->assertSee('Jangan kirim tautan ini');
});

test('tautan yang sudah benar tidak ikut diperingatkan', function () {
    // Peringatan yang muncul terus akhirnya diabaikan, termasuk saat ia benar.
    isiRombongan(Livewire::actingAs(adminRombongan())->test(OrchaDaftarkanRombongan::class))
        ->call('simpan')
        ->assertDontSee('Jangan kirim tautan ini');
});
