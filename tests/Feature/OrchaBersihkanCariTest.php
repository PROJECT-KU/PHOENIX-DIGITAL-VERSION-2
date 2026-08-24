<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranList;
use App\Livewire\Pages\Admin\Orcha\Pesan\OrchaPesanList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function adminCari(): User
{
    $role = Role::create(['name' => 'uji-cari-'.uniqid(), 'description' => 'Peran untuk uji cari']);

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

    Http::fake([
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['baru' => 'Baru', 'lunas' => 'Lunas'],
            'keperluan_kontak' => ['umum' => 'Umum'],
            'paket_wisata' => [['id' => 1, 'nama' => 'Open Trip Banyuwangi',
                'kategori' => 'open_trip', 'tanggal_berangkat' => '2026-09-10']],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);
});

test('tombol pengosong hanya muncul saat kotak cari ada isinya', function () {
    $halaman = Livewire::actingAs(adminCari())->test(OrchaPendaftaranList::class);

    // Tombol yang selalu ada tetapi tidak selalu berguna hanya menambah benda
    // yang harus diabaikan mata.
    $halaman->assertDontSee('Kosongkan pencarian')
        ->set('cari', 'budi')
        ->assertSee('Kosongkan pencarian');
});

test('mengosongkan pencarian mengembalikan daftar ke halaman satu', function () {
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('cari', 'budi')
        ->call('keHalaman', 3)
        ->assertSet('halaman', 3)
        ->call('bersihkanCari')
        ->assertSet('cari', '')
        ->assertSet('halaman', 1);
});

test('bersihkan saringan mengosongkan ketiganya sekaligus', function () {
    Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('cari', 'budi')
        ->set('filterStatus', 'lunas')
        ->set('filterPaket', '1')
        ->assertSee('Bersihkan saringan')
        ->call('bersihkanSaringan')
        ->assertSet('cari', '')
        ->assertSet('filterStatus', '')
        // Saringan ketiga milik halaman ini ikut — tombol yang menyisakan satu
        // saringan hidup justru membingungkan.
        ->assertSet('filterPaket', '')
        ->assertDontSee('Bersihkan saringan');
});

test('kotak cari yang sama tersedia di daftar orcha lainnya', function () {
    // Partial bersama: sekali diperbaiki, seluruh daftar ikut.
    Livewire::actingAs(adminCari())
        ->test(OrchaPesanList::class)
        ->set('cari', 'budi')
        ->assertSee('Kosongkan pencarian')
        ->call('bersihkanCari')
        ->assertSet('cari', '');
});

test('pemilih saringan menandai pilihannya di markup, bukan hanya di ingatan komponen', function () {
    $halaman = Livewire::actingAs(adminCari())
        ->test(OrchaPendaftaranList::class)
        ->set('filterPaket', '1')
        ->set('filterStatus', 'lunas');

    // Tanpa penanda ini, "Bersihkan saringan" mengosongkan keadaan komponen
    // tetapi kotak pilihannya tetap memajang paket lama — layar yang berbohong
    // tentang keadaannya sendiri.
    $halaman->assertSeeHtml('value="1" selected')
        ->assertSeeHtml('value="lunas" selected')
        ->call('bersihkanSaringan')
        ->assertDontSeeHtml('value="1" selected')
        ->assertDontSeeHtml('value="lunas" selected');
});
