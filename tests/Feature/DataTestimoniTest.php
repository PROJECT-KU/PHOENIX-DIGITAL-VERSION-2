<?php

use App\Livewire\Pages\Admin\Testimoni\TestimoniForm;
use App\Livewire\Pages\Admin\Testimoni\TestimoniList;
use App\Models\Testimoni;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Data Testimoni: kartu status (sekaligus tab moderasi), rak kartu, jendela
 * detail, setujui/tolak — dan celah izin di rute detail & simpan form.
 */
function adminTestimoni(array $izin = ['view_testimoni', 'create_testimoni', 'edit_testimoni', 'delete_testimoni']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-tm-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function testimoni(array $isian = []): Testimoni
{
    return Testimoni::create(array_merge([
        'nama' => 'Pengirim '.Str::random(4),
        'pesan' => 'Pelayanannya cepat dan ramah.',
        'rating' => 5,
        'status' => 'pending',
    ], $isian));
}

it('kartu status menghitung dan menyaring tiap tab moderasi', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Masih Ditinjau']);
    testimoni(['nama' => 'Sudah Tampil', 'status' => 'active']);
    testimoni(['nama' => 'Kena Tolak', 'status' => 'non-active']);

    $t = Livewire::test(TestimoniList::class);
    expect($t->viewData('tabCounts'))->toMatchArray(['all' => 3, 'pending' => 1, 'active' => 1, 'non-active' => 1]);

    // Tab awal = menunggu, supaya kiriman baru tidak terlewat.
    $t->assertSet('filter', 'pending')->assertSee('Masih Ditinjau')->assertDontSee('Sudah Tampil');
    $t->call('setFilter', 'non-active')->assertSee('Kena Tolak')->assertDontSee('Masih Ditinjau');
    $t->call('setFilter', 'all')->assertSee('Sudah Tampil')->assertSee('Kena Tolak');
    $t->call('setFilter', 'ngawur')->assertSet('filter', 'pending');
});

it('rata-rata rating hanya menghitung yang tampil di publik', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['status' => 'active', 'rating' => 5]);
    testimoni(['status' => 'active', 'rating' => 4]);
    testimoni(['status' => 'pending', 'rating' => 1]);   // belum tampil
    testimoni(['status' => 'non-active', 'rating' => 1]); // ditolak

    expect(Livewire::test(TestimoniList::class)->viewData('rataRating'))->toBe(4.5);
});

it('rute detail membuka jendela detail, BUKAN halaman ubah (celah izin lama)', function () {
    $tm = testimoni(['nama' => 'Rina Kusuma', 'pesan' => 'Akun Grammarly-nya cepat sekali dikirim.']);
    $this->actingAs(adminTestimoni(['view_testimoni']));

    expect(\Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.testimoni.show')->getActionName())
        ->toContain('TestimoniList');

    Livewire::test(TestimoniList::class, ['testimoni' => $tm->id])
        ->assertSet('lihatId', $tm->id)
        // Tab dipaksa 'all' supaya detail apa pun statusnya tetap terbuka.
        ->assertSet('filter', 'all')
        ->assertSee('Detail Testimoni')
        ->assertSee('Akun Grammarly-nya cepat sekali dikirim.')
        ->assertDontSee('Simpan Perubahan');
});

it('simpan form menolak pengguna tanpa izin ubah', function () {
    $tm = testimoni(['nama' => 'Rina Kusuma']);
    $this->actingAs(adminTestimoni(['view_testimoni']));

    Livewire::test(TestimoniForm::class, ['testimoni' => $tm])
        ->set('nama', 'Diubah diam-diam')
        ->call('save')
        ->assertForbidden();

    expect($tm->fresh()->nama)->toBe('Rina Kusuma');
});

it('simpan form menolak pembuatan tanpa izin tambah', function () {
    $this->actingAs(adminTestimoni(['view_testimoni', 'edit_testimoni']));

    Livewire::test(TestimoniForm::class)
        ->set('nama', 'Testimoni Selundupan')
        ->set('pesan', 'Kunjungi situs saya untuk promo!')
        ->call('save')
        ->assertForbidden();

    expect(Testimoni::where('nama', 'Testimoni Selundupan')->exists())->toBeFalse();
});

it('setujui & tolak mengubah status, dan butuh izin ubah', function () {
    $tm = testimoni();

    $this->actingAs(adminTestimoni(['view_testimoni']));
    Livewire::test(TestimoniList::class)->call('approve', $tm->id);
    expect($tm->fresh()->status)->toBe('pending');

    $this->actingAs(adminTestimoni());
    Livewire::test(TestimoniList::class)->call('approve', $tm->id);
    expect($tm->fresh()->status)->toBe('active');

    Livewire::test(TestimoniList::class)->call('reject', $tm->id);
    expect($tm->fresh()->status)->toBe('non-active');
});

it('pencarian menjangkau nama, peran, dan isi pesan', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Budi', 'peran' => 'Dosen UNAIR', 'pesan' => 'Mantap sekali.']);
    testimoni(['nama' => 'Sita', 'peran' => 'Mahasiswa', 'pesan' => 'Pengecekan plagiasinya akurat.']);

    $t = Livewire::test(TestimoniList::class);
    $t->set('searchTestimoni', 'UNAIR')->assertSee('Budi')->assertDontSee('Sita');
    $t->set('searchTestimoni', 'plagiasi')->assertSee('Sita')->assertDontSee('Budi');
});

it('nama pengirim anonim disamarkan di tampilan publik, tapi utuh untuk admin', function () {
    $this->actingAs(adminTestimoni());
    $tm = testimoni(['nama' => 'Rina Kusuma', 'anonim' => true, 'status' => 'active']);

    expect($tm->nama_publik)->toBe('R•••');

    // Admin memoderasi memakai nama asli.
    Livewire::test(TestimoniList::class)->call('setFilter', 'active')->assertSee('Rina Kusuma');
});

it('rating dari pemilih bintang dijaga tetap 1..5', function () {
    $this->actingAs(adminTestimoni());

    Livewire::test(TestimoniForm::class)
        ->call('setRating', 4)->assertSet('rating', 4)
        ->call('setRating', 9)->assertSet('rating', 5)
        ->call('setRating', 0)->assertSet('rating', 1);
});
