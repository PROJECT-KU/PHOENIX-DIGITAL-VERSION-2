<?php

use App\Livewire\Pages\Admin\Orcha\Pelanggan\OrchaPelangganList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Layar Pelanggan: orang, bukan pesanan.
 *
 * Tombol WhatsApp-nya sengaja TIDAK langsung membuka WhatsApp. Ia membuka
 * jendela dulu, dan di jendela itu kode rujukannya disiapkan beserta pesan
 * yang tinggal dikirim. Langsung membuka WhatsApp berarti admin mengetik
 * sendiri kodenya dari ingatan — dan kode yang salah satu huruf adalah komisi
 * yang tidak pernah sampai ke pemiliknya.
 */
function adminPelanggan(): User
{
    $role = Role::create(['name' => 'uji-plg-'.uniqid(), 'description' => 'Peran untuk uji pelanggan']);

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

function barisPelanggan(array $ubah = []): array
{
    return array_merge([
        'whatsapp' => '0812-3456-7890', 'whatsapp_angka' => '081234567890',
        'nama' => 'Budi Santoso', 'email' => null,
        'jumlah_trip' => 2, 'jumlah_sewa' => 1, 'jumlah_batal' => 0,
        'terakhir_pada' => now()->subDays(4)->toIso8601String(),
        'terakhir_kode' => 'OT-0109-ABCD', 'terakhir_jenis' => 'trip',
        'kode_rujukan' => null, 'rujukan_aktif' => null,
        'rujukan_dipakai' => 0, 'komisi_belum_dibayar' => 0,
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
     | dan yang terdaftar lebih dulu menang. Uji yang memasang tiruannya sendiri
     | di tengah jalan lalu tidak berlaku, tanpa galat apa pun.
     */
    Http::fake(function ($permintaan) {
        $alamat = $permintaan->url();

        if (str_contains($alamat, '/pelanggan/kode-rujukan')) {
            return Http::response(['data' => ['kode' => 'BUDI-K7QM', 'baru' => true]], 201);
        }

        if (str_contains($alamat, '/pelanggan')) {
            return Http::response([
                'data' => [barisPelanggan(config('uji.baris', []))],
                'meta' => [
                    'halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1,
                    'rujukan_potongan' => 50000, 'rujukan_imbalan' => 75000,
                ],
            ]);
        }

        return Http::response(['data' => ['status_pendaftaran' => [], 'paket_wisata' => []]]);
    });
});

test('daftar menampilkan orang beserta jumlah pesanannya', function () {
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->assertSee('Budi Santoso')
        ->assertSee('2 trip')
        ->assertSee('1 sewa');
});

test('yang tanpa email ditandai, karena merekalah yang tidak terjangkau surat', function () {
    /*
     | Orang inilah yang tidak pernah menerima surat otomatis apa pun — bukan
     | pengingat, bukan ajakan testimoni, bukan kode rujukan. Satu-satunya
     | jalan ke mereka tombol WhatsApp di baris ini.
     */
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->assertSee('Tanpa email');
});

test('yang punya email tidak ikut ditandai', function () {
    config()->set('uji.baris', ['email' => 'budi@contoh.test']);

    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->assertDontSee('Tanpa email')
        ->assertSee('budi@contoh.test');
});

test('tombol WhatsApp membuka jendela, bukan langsung ke WhatsApp', function () {
    /*
     | Langsung membuka WhatsApp berarti admin mengetik sendiri kodenya dari
     | ingatan — dan kode yang salah satu huruf adalah komisi yang tidak pernah
     | sampai ke pemiliknya.
     */
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->assertSet('buka', '')
        ->call('bukaPesan', barisPelanggan())
        ->assertSet('buka', '081234567890')
        ->assertSee('Pesan yang akan dikirim');
});

test('jendela menyusun pesannya sendiri, lengkap dengan angkanya', function () {
    // Yang diketik ulang berbeda-beda tiap admin dan tiap hari, dan angka yang
    // diingat dari percakapan bulan lalu adalah angka yang salah.
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->call('bukaPesan', barisPelanggan(['kode_rujukan' => 'BUDI-K7QM']))
        ->assertSee('BUDI-K7QM')
        ->assertSee('Rp 50.000')
        ->assertSee('Rp 75.000')
        // Menyapa nama depannya saja: "Halo Kak Budi Santoso" tidak pernah
        // ditulis orang sungguhan.
        ->assertSee('Halo Kak Budi,');
});

test('membuka jendela TIDAK membuat kode apa pun', function () {
    /*
     | Kalau kodenya dibuat sendiri saat jendelanya terbuka, setiap orang yang
     | pernah dilihat admin mendapat kode — termasuk yang seluruh pesanannya
     | batal.
     */
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->call('bukaPesan', barisPelanggan());

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/pelanggan/kode-rujukan'));
});

test('tombol buatkan kode mengirimnya ke Orcha lalu memakainya di pesan', function () {
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->call('bukaPesan', barisPelanggan())
        ->assertSee('Belum punya kode rujukan')
        ->call('buatkanKode')
        ->assertSet('kodeBaru', 'BUDI-K7QM')
        ->assertSee('BUDI-K7QM');

    Http::assertSent(fn ($p) => str_contains($p->url(), '/pelanggan/kode-rujukan')
        && $p->method() === 'POST'
        && $p['whatsapp'] === '081234567890');
});

test('menutup jendela membuang pilihannya, bukan menyisakannya', function () {
    // Jendela yang tertutup tetapi isinya tersisa akan tergambar dengan data
    // orang sebelumnya saat dibuka lagi — dan pesan yang terkirim ke orang yang
    // salah tidak bisa ditarik.
    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->call('bukaPesan', barisPelanggan())
        ->call('tutupPesan')
        ->assertSet('buka', '')
        ->assertSet('terpilih', [])
        ->assertSet('kodeBaru', '');
});

test('komisi yang belum dibayar terlihat di daftar', function () {
    config()->set('uji.baris', [
        'kode_rujukan' => 'BUDI-K7QM', 'rujukan_dipakai' => 3,
        'komisi_belum_dibayar' => 225000,
    ]);

    Livewire::actingAs(adminPelanggan())
        ->test(OrchaPelangganList::class)
        ->assertSee('BUDI-K7QM')
        ->assertSee('Rp 225.000');
});
