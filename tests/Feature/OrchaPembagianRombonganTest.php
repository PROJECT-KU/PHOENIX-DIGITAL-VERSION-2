<?php

/**
 * Layar pembagian kursi kendaraan & kamar satu rombongan.
 *
 * Satuannya rombongan, bukan pendaftaran. Satu bus mengangkut peserta dari
 * banyak pemesanan sekaligus, dan satu kamar twin diisi dua orang yang bisa
 * saja mendaftar terpisah — dikerjakan per pendaftaran, dua admin sama-sama
 * menaruh orangnya di "Bus 1 kursi 5" tanpa bisa melihat bentrokannya.
 */

use App\Livewire\Pages\Admin\Orcha\Pembagian\OrchaPembagianRombongan;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function adminPembagian(): User
{
    $role = Role::create(['name' => 'uji-pbg-'.uniqid(), 'description' => 'Peran uji pembagian']);

    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function orangPembagian(string $kunci, string $nama, array $ubah = []): array
{
    return array_merge([
        'kunci' => $kunci,
        'pendaftaran_id' => (int) explode(':', $kunci)[0],
        'urutan' => (int) explode(':', $kunci)[1],
        'kode' => 'OT-1711-R'.explode(':', $kunci)[0],
        'pemesan' => 'Pemesan '.explode(':', $kunci)[0],
        'status' => 'lunas',
        'nama' => $nama,
        'titik_jemput' => 'Terminal',
        'bus' => null, 'kursi' => null, 'kamar' => null,
        'jenis_kelamin' => null, 'kelamin_dari_kesehatan' => false,
    ], $ubah);
}

function keadaanPembagian(array $ubah = []): array
{
    $peserta = $ubah['peserta'] ?? [
        orangPembagian('7:0', 'Budi', ['jenis_kelamin' => 'Laki-laki']),
        orangPembagian('7:1', 'Siti', ['jenis_kelamin' => 'Perempuan']),
        orangPembagian('8:0', 'Sri'),
    ];

    $kendaraan = $ubah['kendaraan'] ?? [['nama' => 'Bus 1', 'tata' => '2-2', 'jumlah_kursi' => 8]];
    $kamar = $ubah['kamar'] ?? [
        ['nama' => '201', 'kapasitas' => 2],
        ['nama' => '202', 'kapasitas' => 2],
    ];

    $petak = $ubah['petak'] ?? [[
        'nama' => 'Bus 1', 'tata' => '2-2', 'lebar' => 5, 'jumlah_kursi' => 8,
        'baris' => [['1', '2', null, '3', '4'], ['5', '6', null, '7', '8']],
        'kursi' => ['1', '2', '3', '4', '5', '6', '7', '8'],
    ]];

    return [
        'keberangkatan' => [
            'id' => 11, 'rentang_tanggal' => '4 – 6 Des 2026',
            'tanggal_berangkat' => '2026-12-04', 'kuota' => 40,
            'paket' => ['id' => 5, 'nama' => 'Open Trip Bromo'],
        ],
        'kendaraan' => $kendaraan,
        'kamar' => $kamar,
        'petak' => $petak,
        'pilihan_tata' => ['2-2' => 'Bus sedang / big bus 2-2', '2-1' => 'Hiace / elf 2-1'],
        'peserta' => $peserta,
        'ringkasan' => [],
        'peringatan' => [],
        'riwayat' => $ubah['riwayat'] ?? [],
    ];
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
    config()->set('uji.pbg.keadaan', []);

    Http::fake(function ($permintaan) {
        if (str_contains($permintaan->url(), '/rujukan')) {
            return Http::response(['data' => []]);
        }

        if (str_contains($permintaan->url(), '/pembagian/otomatis')) {
            return Http::response([
                'pesan' => '3 peserta ditempatkan.',
                'data' => [
                    'peserta' => keadaanPembagian()['peserta'],
                    'sisa' => [['kunci' => '8:0', 'nama' => 'Sri', 'sebab' => 'Jenis kelaminnya belum diisi — tidak ditebak.']],
                    'catatan' => ['1 peserta belum diisi jenis kelaminnya, jadi belum bisa dimasukkan ke kamar.'],
                ],
            ]);
        }

        if (str_contains($permintaan->url(), '/fasilitas')) {
            return Http::response([
                'pesan' => 'Kendaraan dan kamar rombongan tersimpan.',
                'data' => [
                    'kendaraan' => [['nama' => 'Bus 1', 'tata' => '2-2', 'jumlah_kursi' => 8]],
                    'kamar' => [['nama' => '201', 'kapasitas' => 2]],
                    'petak' => keadaanPembagian()['petak'],
                ],
            ]);
        }

        if (str_contains($permintaan->url(), '/pembagian') && $permintaan->method() === 'PATCH') {
            return Http::response([
                'pesan' => 'Pembagian tersimpan.',
                'data' => ['peserta' => keadaanPembagian()['peserta'], 'peringatan' => []],
            ]);
        }

        return Http::response(['data' => keadaanPembagian(config('uji.pbg.keadaan') ?: [])]);
    });
});

test('layar memuat peserta dari semua pemesanan rombongan, dan petak kursinya tergambar', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Open Trip Bromo')
        ->assertSee('4 – 6 Des 2026')
        ->assertSee('Budi')
        ->assertSee('Siti')
        ->assertSee('Sri')
        // Petaknya: delapan kursi, dengan lorong di tengah.
        ->assertSee('Tempat Duduk')
        ->assertSee('8 kursi');
});

test('ketuk kursi tanpa memilih orang menolak dengan jalan keluarnya disebut', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->assertDispatched('toast-error')
        ->assertSet('ubahan', []);
});

test('pilih lalu ketuk kursi menempatkan orangnya, dan ditandai belum tersimpan', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->assertSet('ubahan.7:0.bus', 'Bus 1')
        ->assertSet('ubahan.7:0.kursi', '3')
        // Pilihannya dilepas, supaya ketukan berikutnya tidak memindahkan orang
        // yang admin sudah lupa sedang dipegangnya.
        ->assertSet('terpilih', '')
        ->assertSee('BELUM tersimpan');
});

test('menekan nama yang sudah terpilih membatalkan pilihannya', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->assertSet('terpilih', '7:0')
        ->call('pilih', '7:0')
        ->assertSet('terpilih', '');
});

test('kursi berpenghuni DITUKAR, bukan ditumpuk', function () {
    /*
     | Menumpuk menghasilkan dua orang di satu kursi — keadaan yang tidak
     | pernah sah, dan yang akibatnya baru terasa setelah bus berjalan.
     | Menolak juga salah: admin yang menukar dua orang pasti melewati keadaan
     | itu di tengah jalan.
     */
    config()->set('uji.pbg.keadaan', ['peserta' => [
        orangPembagian('7:0', 'Budi', ['jenis_kelamin' => 'Laki-laki', 'bus' => 'Bus 1', 'kursi' => '1']),
        orangPembagian('7:1', 'Siti', ['jenis_kelamin' => 'Perempuan', 'bus' => 'Bus 1', 'kursi' => '2']),
    ]]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '2')
        // Budi pindah ke 2, dan Siti mengambil kursi 1 milik Budi.
        ->assertSet('ubahan.7:0.kursi', '2')
        ->assertSet('ubahan.7:1.kursi', '1');
});

test('kamar yang sudah penuh menolak, dan menyebut angkanya', function () {
    /*
     | Berbeda dari kursi. Kapasitas kamar adalah janji ke hotel, bukan tata
     | letak yang bisa dipadatkan: kamar twin berisi tiga orang berarti satu
     | orang tidur di lantai, dan yang mengetahuinya resepsionis hotel pada jam
     | sebelas malam.
     */
    config()->set('uji.pbg.keadaan', ['peserta' => [
        orangPembagian('7:0', 'Budi', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201']),
        orangPembagian('7:1', 'Agus', ['jenis_kelamin' => 'Laki-laki', 'kamar' => '201']),
        orangPembagian('8:0', 'Doni', ['jenis_kelamin' => 'Laki-laki']),
    ]]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '8:0')
        ->call('tempatkanKamar', '201')
        ->assertDispatched('toast-error')
        ->assertSet('ubahan', []);
});

test('kamar yang masih ada tempatnya menerima orangnya', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:1')
        ->call('tempatkanKamar', '202')
        ->assertSet('ubahan.7:1.kamar', '202');
});

test('kamar tercampur ditandai, bukan ditolak', function () {
    /*
     | Pembagian OTOMATIS tidak pernah mencampur. Yang dibuat tangan boleh,
     | karena pasangan suami-istri memang satu kamar — yang dibutuhkan admin
     | bukan larangan, melainkan supaya campuran yang TIDAK disengaja tidak
     | lolos tanpa terlihat.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKamar', '201')
        ->call('pilih', '7:1')
        ->call('tempatkanKamar', '201')
        ->assertSee('Perlu Diperiksa')
        ->assertSee('Kalau ini pasangan, biarkan');
});

test('kursi kembar yang sudah tersimpan dilaporkan sebagai bentrok', function () {
    /*
     | Bentrokan TIDAK bisa dibuat dari layar ini — menempatkan orang ke kursi
     | berpenghuni menukar keduanya. Yang bisa bentrok adalah data yang sudah
     | tersimpan begitu: nomor kursi diisi sebagai teks bebas di layar Peserta,
     | satu per pendaftaran, tanpa ada yang melihat seluruh rombongan.
     |
     | Itulah sebab peringatan ini ada, dan sebab ia dihitung dari keadaan yang
     | DIMUAT, bukan hanya dari perubahan yang sedang disusun.
     */
    config()->set('uji.pbg.keadaan', ['peserta' => [
        orangPembagian('7:0', 'Budi', ['jenis_kelamin' => 'Laki-laki', 'bus' => 'Bus 1', 'kursi' => '1']),
        orangPembagian('8:0', 'Agus', ['jenis_kelamin' => 'Laki-laki', 'bus' => 'Bus 1', 'kursi' => '1']),
    ]]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Perlu Diperiksa')
        ->assertSee('Bus 1 kursi 1 dipakai 2 orang')
        ->assertSee('Budi, Agus');
});

test('peringatan ikut berubah oleh susunan yang BELUM disimpan', function () {
    /*
     | Dihitung di lemon, bukan dibaca dari Orcha. Peringatan yang terlambat
     | satu langkah menyala setelah kesalahannya tersimpan — dan pada layar yang
     | menahan perubahan sampai Simpan ditekan, "satu langkah" bisa berarti satu
     | jam kerja.
     */
    $layar = Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        // Sri belum diisi jenis kelaminnya; menaruhnya di kamar memunculkan
        // peringatan SEBELUM apa pun dikirim ke Orcha.
        ->assertDontSee('jenis kelaminnya belum diisi.')
        ->call('pilih', '8:0')
        ->call('tempatkanKamar', '201');

    $layar->assertSee('Perlu Diperiksa')
        ->assertSee('Kamar 201 berisi 1 peserta yang jenis kelaminnya belum diisi.');
});

test('jenis kelamin bisa dilengkapi dari layar ini', function () {
    /*
     | Pembagian kamar yang berhenti karena tiga orang belum diisi memaksa
     | admin berpindah ke tiga pendaftaran berbeda lalu kembali — dan yang
     | terjadi berikutnya bukan ia melengkapi data, melainkan ia membagi
     | kamarnya dengan tangan.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('belum diisi jenis kelaminnya')
        ->call('setKelamin', '8:0', 'Perempuan')
        ->assertSet('ubahan.8:0.jenis_kelamin', 'Perempuan');
});

test('simpan mengirim seluruh medan baris yang berubah', function () {
    /*
     | Tiap baris dikirim LENGKAP keempat medannya. Jalur Orcha memperbarui
     | medan yang disebut saja — mengirim separuh berarti mengosongkan kamar
     | seseorang tidak pernah sampai: terbaca sebagai "kamar tidak disebut",
     | bukan "kamar dikosongkan".
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->call('simpan')
        ->assertDispatched('order-updated')
        ->assertSet('ubahan', []);

    $terkirim = collect(Http::recorded())
        ->map(fn ($pasang) => $pasang[0])
        ->first(fn ($p) => $p->method() === 'PATCH' && str_contains($p->url(), '/pembagian'));

    $baris = $terkirim->data()['peserta'][0];

    expect(array_keys($baris))->toBe(['kunci', 'bus', 'kursi', 'kamar', 'jenis_kelamin']);
    expect($baris['kunci'])->toBe('7:0');
    expect($baris['kursi'])->toBe('3');
});

test('simpan tanpa perubahan menolak, bukan mengirim daftar kosong', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('simpan')
        ->assertDispatched('toast-error');
});

test('buang perubahan mengembalikan keadaan semula', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->call('batalkanUbahan')
        ->assertSet('ubahan', [])
        ->assertSet('terpilih', '');
});

test('bagi otomatis ditahan selama masih ada perubahan yang belum disimpan', function () {
    /*
     | Kalau tidak, hasil otomatis bercampur dengan susunan tangan yang belum
     | tersimpan — dan admin tidak punya cara memisahkan keduanya lagi.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->call('bagiOtomatis', 'kamar')
        ->assertDispatched('toast-error');
});

test('bagi otomatis melaporkan siapa yang belum kebagian dan sebabnya', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('bagiOtomatis', 'kamar')
        ->assertDispatched('order-updated')
        ->assertSee('Belum kebagian')
        ->assertSee('Sri');
});

test('bawaan bagi otomatis TIDAK menimpa yang sudah diatur tangan', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSet('timpa', false);
});

test('deret kamar bernomor dibuat sekaligus, nol di depan dipertahankan', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->set('deretAwal', '010')
        ->set('deretJumlah', 3)
        ->set('deretKapasitas', 2)
        ->call('tambahDeretKamar')
        // Dua kamar bawaan (201, 202) ditambah tiga yang baru.
        ->assertSet('kamar.2.nama', '010')
        ->assertSet('kamar.3.nama', '011')
        ->assertSet('kamar.4.nama', '012');
});

test('nomor awal yang tidak berakhiran angka ditolak', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->set('deretAwal', 'Lantai Dua')
        ->call('tambahDeretKamar')
        ->assertDispatched('toast-error');
});

test('deret tidak membuat kamar yang namanya sudah ada', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->set('deretAwal', '201')
        ->set('deretJumlah', 3)
        ->call('tambahDeretKamar')
        // 201 dan 202 sudah ada; hanya 203 yang ditambahkan.
        ->assertSet('kamar.2.nama', '203')
        ->assertCount('kamar', 3);
});

test('tanpa kendaraan, layar menyebutkan jalan keluarnya', function () {
    config()->set('uji.pbg.keadaan', ['kendaraan' => [], 'petak' => []]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Belum ada kendaraan yang didaftarkan')
        ->assertSee('Kendaraan &amp; Kamar yang Disiapkan', false);
});

test('tanpa peserta, layar menyebut sebab yang mungkin', function () {
    config()->set('uji.pbg.keadaan', ['peserta' => []]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Belum ada peserta di rombongan ini')
        ->assertSee('nama pesertanya belum didata');
});

/*
|--------------------------------------------------------------------------
| Riwayat, cetak, dan rupa petak
|--------------------------------------------------------------------------
*/

test('panel riwayat menawarkan pengembalian beserta sebab tiap potret', function () {
    config()->set('uji.pbg.keadaan', ['riwayat' => [
        ['urutan' => 0, 'pada' => now()->subMinutes(5)->toIso8601String(), 'oleh' => 'Admin Uji',
            'sebab' => 'bagi otomatis (menimpa)', 'jumlah_peserta' => 6, 'sudah_kursi' => 4, 'sudah_kamar' => 4],
    ]]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Kembalikan ke Pembagian Sebelumnya')
        ->assertSee('bagi otomatis (menimpa)')
        ->assertSee('4 dapat kursi');
});

test('pengembalian ditahan selama masih ada perubahan yang belum disimpan', function () {
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->call('kembalikan', 0)
        ->assertDispatched('toast-error');
});

test('tombol cetak denah kursi dan daftar kamar ada di kepala layar', function () {
    /*
     | Tanpa keduanya, seluruh pembagian ini berakhir di satu tab peramban —
     | dan tour leader tetap berdiri di parkiran dengan catatan tangan.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Cetak Denah Kursi')
        ->assertSee('Cetak Daftar Kamar')
        ->assertSee('/rombongan/11/denah-kursi', false)
        ->assertSee('/rombongan/11/daftar-kamar', false);
});

test('petak dibungkus wadah bergulir dan menandai kursi jendela', function () {
    /*
     | Tata 2-3 selebar enam kolom tidak muat di ponsel; tanpa wadah bergulir
     | yang bergeser SELURUH halaman, dan tombol di tepi kanan hilang dari
     | layar. Penanda jendela menjawab permintaan yang paling sering datang.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('pbg-petak-gulir')
        ->assertSee('is-jendela')
        ->assertSee('kursi jendela');
});

test('titik jemput ikut terbaca di kepingan peserta', function () {
    // Yang dibutuhkan sopir justru keduanya bersamaan: siapa naik di mana,
    // duduk di mana.
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->assertSee('Terminal');
});

test('peringatan kamar yang banyak diringkas jadi satu baris yang bisa dibuka', function () {
    /*
     | Rombongan enam puluh orang dengan tiga puluh kamar tercampur
     | menghasilkan tiga puluh baris kuning, dan daftar sepanjang itu berhenti
     | dibaca pada baris kelima — termasuk baris merah yang terselip.
     */
    $peserta = [];

    foreach (range(0, 7) as $i) {
        $peserta[] = orangPembagian('7:'.$i, 'Orang '.$i, [
            'jenis_kelamin' => $i % 2 ? 'Perempuan' : 'Laki-laki',
            'kamar' => (string) (201 + intdiv($i, 2)),
        ]);
    }

    config()->set('uji.pbg.keadaan', ['peserta' => $peserta, 'kamar' => [
        ['nama' => '201', 'kapasitas' => 2], ['nama' => '202', 'kapasitas' => 2],
        ['nama' => '203', 'kapasitas' => 2], ['nama' => '204', 'kapasitas' => 2],
    ]]);

    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        // Empat kamar tercampur → diringkas, bukan dibentangkan.
        ->assertSee('4 kamar perlu diperiksa');
});

test('bilah simpan menempel di dasar layar', function () {
    /*
     | Papan kamar ada di bagian paling bawah halaman. Dengan tombol Simpan
     | yang tinggal di kepala, admin yang baru memindahkan orang harus
     | menggulir balik ke atas — dan di situlah ia paling mudah lupa.
     */
    Livewire::actingAs(adminPembagian())
        ->test(OrchaPembagianRombongan::class, ['keberangkatan' => 11])
        ->call('pilih', '7:0')
        ->call('tempatkanKursi', 'Bus 1', '3')
        ->assertSee('pbg-simpan-tempel');
});
