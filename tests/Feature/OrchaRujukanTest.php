<?php

use App\Livewire\Pages\Admin\Orcha\Rujukan\OrchaRujukanList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar Kode Rujukan di lemon.
 *
 * Dua pekerjaan, dan yang kedua yang paling sering dibuka: membuatkan kode,
 * dan membayarkan komisi. Tanpa layar ini, satu-satunya cara mengetahui komisi
 * mana yang sudah dibayar adalah mengingatnya — dan yang menagih nanti orang
 * yang merasa haknya belum diberikan, sambil kita tidak punya cara
 * membuktikan sebaliknya.
 */
function adminRujukan(): User
{
    $role = Role::create(['name' => 'uji-rujukan-'.uniqid(), 'description' => 'Peran untuk uji rujukan']);

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

function barisRujukan(array $ubah = []): array
{
    return array_merge([
        'id' => 3, 'kode' => 'BUDI-K7QM', 'nama' => 'Budi', 'whatsapp' => '0812-3456-7890',
        'email' => null, 'kode_pendaftaran_asal' => 'OT-0109-ABCD', 'aktif' => true,
        'catatan' => null, 'jumlah_dipakai' => 2,
        'imbalan_total' => 150000, 'imbalan_belum_dibayar' => 75000,
        'dibuat_pada' => now()->toIso8601String(),
    ], $ubah);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    /*
     | Satu tiruan berupa fungsi, dipasang sekali.
     |
     | Http::fake yang dipanggil dua kali MENAMBAH tiruan, bukan menggantinya —
     | dan yang terdaftar lebih dulu menang. Uji yang memasang tiruannya
     | sendiri di tengah jalan lalu tidak berlaku, tanpa galat apa pun: ia
     | cuma diam-diam menerima balasan dari tiruan di sini.
     |
     | Dibedakan menurut METODE juga, karena layar ini memanggil alamat yang
     | sama dua kali dengan maksud berbeda: GET menggambar daftarnya, POST
     | menyimpan.
     */
    Http::fake(function ($permintaan) {
        $alamat = $permintaan->url();

        if (str_contains($alamat, '/kode-rujukan') && $permintaan->method() === 'POST') {
            return config('uji.tolak_simpan')
                ? Http::response(
                    ['message' => 'Nomor ini sudah punya kode rujukan: BUDI-K7QM. Sunting yang itu saja.'],
                    422
                )
                : Http::response(['data' => barisRujukan()], 201);
        }

        if (str_contains($alamat, '/pemakaian')) {
            return Http::response(['data' => [[
                'id' => 11, 'kode' => 'OT-0309-A1B2', 'nama' => 'Teman Budi',
                'nama_paket' => 'Trip Uji', 'tanggal_berangkat' => now()->addDays(9)->toDateString(),
                'status' => 'dp_masuk', 'imbalan' => 75000, 'dibayar_pada' => null,
            ]]]);
        }

        if (str_contains($alamat, '/kode-rujukan')) {
            return Http::response(['data' => [barisRujukan()], 'meta' => [
                'halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1,
                'potongan' => 50000, 'imbalan' => 75000, 'aktif' => true,
            ]]);
        }

        return Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru'],
            'paket_wisata' => [],
        ]]);
    });
});

test('daftar menampilkan kode, pemilik, dan komisi yang belum dibayar', function () {
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->assertSee('BUDI-K7QM')
        ->assertSee('Budi')
        // Yang belum dibayar ditonjolkan, bukan totalnya: total komisi sepanjang
        // masa enak dibaca tetapi tidak menuntut perbuatan apa pun.
        ->assertSee('Rp 75.000');
});

test('angka yang berlaku disebut apa adanya di layar', function () {
    // Admin yang menjelaskan program ini lewat telepon perlu membaca angkanya
    // tanpa membuka berkas config — dan angka yang diingat dari percakapan
    // bulan lalu adalah angka yang salah.
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->assertSee('Potongan untuk yang memakai')
        ->assertSee('Imbalan untuk pemilik kode')
        ->assertSee('Rp 50.000');
});

test('bedanya dengan promo rombongan diterangkan di layar', function () {
    /*
     | Yang membuka layar ini pertama kali akan menyamakannya dengan promo
     | rombongan — keduanya potongan, keduanya diatur admin. Kalau tidak
     | dijelaskan di sini, ia dijelaskan berulang-ulang lewat WhatsApp.
     */
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->assertSee('satu pendaftaran')
        ->assertSee('lintas pendaftaran');
});

test('membuka pemakaian menampilkan pendaftaran yang memakainya', function () {
    // Saat komisi dibayarkan, pertanyaannya bukan "berapa" melainkan "untuk
    // pendaftaran yang mana".
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->assertDontSee('OT-0309-A1B2')
        ->call('bukaPemakaian', 3)
        ->assertSet('lihatPemakaian', 3)
        ->assertSee('OT-0309-A1B2')
        ->assertSee('Tandai dibayar');
});

test('menandai imbalan dibayar mengirimnya ke Orcha', function () {
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->call('bukaPemakaian', 3)
        ->call('bayar', 11);

    Http::assertSent(fn ($p) => str_contains($p->url(), '/kode-rujukan/bayar/11')
        && $p->method() === 'POST');
});

test('formulir menuntut nama dan nomor', function () {
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->call('bukaTambah')
        ->call('simpan')
        ->assertHasErrors(['isian.nama', 'isian.whatsapp']);
});

test('nomor yang sudah punya kode ditempelkan ke kotaknya, bukan jadi toast', function () {
    /*
     | Sebagai toast melayang, pesannya menyebut kode yang sudah ada tetapi
     | tidak menunjuk kotak mana yang harus diubah — dan admin menekan Simpan
     | berulang kali sambil menduga sambungannya bermasalah.
     |
     | Pesannya datang dari abort(422) di sisi Orcha, yang membungkusnya di
     | kunci "message". OrchaClient dulu hanya membaca "errors" dan "pesan",
     | sehingga penolakan yang paling menjelaskan justru yang paling sering
     | tertelan jadi "Isian ditolak oleh Orcha."
     */
    config()->set('uji.tolak_simpan', true);

    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->call('bukaTambah')
        ->set('isian.nama', 'Budi Lagi')
        ->set('isian.whatsapp', '081234567890')
        ->call('simpan')
        ->assertHasErrors('isian.whatsapp')
        // Formulirnya TETAP terbuka: menutupnya membuang apa yang sudah
        // diketik admin, dan ia harus mengetik ulang semuanya hanya untuk
        // membetulkan satu nomor.
        ->assertSet('tambah', true);
});

test('sakelar aktif dipegang properti tersendiri, bukan anggota array', function () {
    /*
     | Livewire tidak menandai atribut "checked" untuk wire:model yang menunjuk
     | anggota array — penandaannya baru diselaraskan skrip setelah halaman
     | hidup, sehingga sakelarnya tergambar mati padahal keadaannya nyala.
     */
    Livewire::actingAs(adminRujukan())
        ->test(OrchaRujukanList::class)
        ->call('bukaSunting', 3, barisRujukan(['aktif' => false]))
        ->assertSet('aktif', false)
        ->call('bukaTambah')
        ->assertSet('aktif', true);
});
