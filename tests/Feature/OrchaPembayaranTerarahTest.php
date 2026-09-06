<?php

use App\Livewire\Pages\Admin\Orcha\Pembayaran\OrchaPembayaranList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Tautan "Kelola pembayaran ini" mendarat langsung pada pesanannya.
 *
 * Admin yang menekannya sedang melihat SATU pesanan. Menjatuhkannya ke daftar
 * penuh berarti menyuruhnya mengetik ulang kode yang barusan ada di layarnya,
 * lalu mencarinya lagi di antara pembayaran orang lain — dua langkah yang
 * seluruhnya bisa dihapus oleh satu parameter di alamatnya.
 */
function adminPembayaran(): User
{
    $role = Role::create(['name' => 'uji-bayar-'.uniqid(), 'description' => 'Peran uji pembayaran']);

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
            'status_pembayaran' => ['menunggu' => 'Menunggu Dicek', 'diterima' => 'Diterima'],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 0, 'halaman_terakhir' => 1]]),
    ]);
});

test('kode pesanan dari alamat langsung mengisi kotak cari', function () {
    Livewire::actingAs(adminPembayaran())
        ->withQueryParams(['cari' => 'OT-1608-1CSS'])
        ->test(OrchaPembayaranList::class)
        ->assertSet('cari', 'OT-1608-1CSS')
        // Penanda saringan ikut menyala, supaya admin tahu KENAPA daftarnya
        // pendek — bukan mengira datanya hilang.
        ->assertSee('Kosongkan pencarian');
});

test('kodenya diteruskan ke Orcha sebagai kata cari', function () {
    Livewire::actingAs(adminPembayaran())
        ->withQueryParams(['cari' => 'OT-1608-1CSS'])
        ->test(OrchaPembayaranList::class);

    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pembayaran')
        && str_contains(urldecode($permintaan->url()), 'cari=OT-1608-1CSS'));
});

test('tanpa parameter, daftarnya tetap utuh seperti biasa', function () {
    /*
     | Tautan lama dan penanda halaman yang sudah tersimpan tidak boleh
     | berubah artinya. Yang membuka daftar pembayaran tanpa maksud khusus
     | tetap melihat semuanya.
     */
    Livewire::actingAs(adminPembayaran())
        ->test(OrchaPembayaranList::class)
        ->assertSet('cari', '')
        ->assertDontSee('Kosongkan pencarian');
});
