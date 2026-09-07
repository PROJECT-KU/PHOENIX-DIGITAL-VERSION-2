<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\FiturPublik;
use Livewire\Livewire;

/**
 * Menutup sementara satu-dua HALAMAN publik, bukan seluruh situs.
 *
 * Berbeda dari jeda produk yang hanya menutup tombol belinya: di sini
 * halamannya sendiri diganti pemberitahuan.
 */
afterEach(function () {
    Setting::query()->delete();
});

function adminFitur(array $izin = ['view_jeda_layanan', 'manage_jeda_layanan']): User
{
    $peran = Role::create(['name' => 'uji-fitur-'.uniqid(), 'description' => 'Peran uji fitur publik']);

    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'jasa', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    return User::factory()->create(['role_id' => $peran->id])->fresh();
}

it('semua fitur terbuka saat belum pernah disetel', function () {
    foreach (array_keys(FiturPublik::DAFTAR) as $fitur) {
        expect(FiturPublik::ditutup($fitur))->toBeFalse();
    }
});

it('menutup satu fitur tidak menyentuh fitur lain', function () {
    FiturPublik::setel('blog', true);

    expect(FiturPublik::ditutup('blog'))->toBeTrue()
        ->and(FiturPublik::ditutup('shop'))->toBeFalse()
        ->and(FiturPublik::ditutup('bundling'))->toBeFalse();
});

it('nama rute dipetakan ke fiturnya, termasuk pola berakhiran bintang', function () {
    expect(FiturPublik::dariRute('blog.index'))->toBe('blog')
        ->and(FiturPublik::dariRute('blog.show'))->toBe('blog')
        ->and(FiturPublik::dariRute('bundling.detail'))->toBe('bundling')
        ->and(FiturPublik::dariRute('shop.detail-product'))->toBe('shop');
});

it('rute yang tak boleh ditutup tidak terpetakan ke fitur mana pun', function () {
    // Menutupnya akan menelantarkan pelanggan yang sudah membayar.
    foreach (['payment', 'order.receipt', 'jasa.cek', 'ebook.view', 'terms', 'privacy', 'sitemap', 'homepage'] as $rute) {
        expect(FiturPublik::dariRute($rute))->toBeNull("rute {$rute} seharusnya tidak bisa ditutup");
    }
});

it('pengunjung melihat pemberitahuan, bukan halaman kosong', function () {
    FiturPublik::setel('blog', true, 'Blog sedang dirapikan.');

    $this->get('/blog')
        ->assertStatus(503)
        ->assertSee('Blog sedang kami perbaiki')
        ->assertSee('Blog sedang dirapikan.');
});

it('status 503 dipakai supaya mesin pencari kembali lagi', function () {
    FiturPublik::setel('blog', true);

    // 404 akan membuat alamatnya dibuang dari indeks.
    $this->get('/blog')->assertStatus(503);
});

it('kalimat bawaan dipakai bila admin tidak menulis keterangan', function () {
    FiturPublik::setel('blog', true);

    $this->get('/blog')->assertSee(FiturPublik::PESAN_BAWAAN);
});

it('halaman lain tetap terbuka saat satu fitur ditutup', function () {
    FiturPublik::setel('blog', true);

    $this->get('/')->assertOk();
});

it('admin yang sudah masuk tetap bisa melihat halaman yang ditutup', function () {
    FiturPublik::setel('blog', true);

    // Perlu untuk memastikan perbaikannya selesai sebelum dibuka untuk umum.
    $this->actingAs(adminFitur())->get('/blog')->assertOk();
});

it('halaman yang ditutup tidak diminta diindeks mesin pencari', function () {
    FiturPublik::setel('blog', true);

    $this->get('/blog')->assertSee('noindex', false);
});

it('fitur yang tidak dikenal diabaikan, bukan menimbulkan galat', function () {
    FiturPublik::setel('fitur-karangan', true);

    expect(Setting::where('key', 'like', 'fitur_publik_%')->count())->toBe(0)
        ->and(FiturPublik::ditutup('fitur-karangan'))->toBeFalse();
});

/* ===================== Panel admin ===================== */

it('panel admin mendaftar seluruh halaman publik', function () {
    Livewire::actingAs(adminFitur())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->assertSee('Halaman Publik')
        ->assertSee('0 ditutup dari '.count(FiturPublik::DAFTAR))
        ->assertSee('Blog')
        ->assertSee('Paket Bundling')
        ->assertSee('Keranjang & Checkout');
});

it('admin bisa menutup dan membuka halaman publik dari panel', function () {
    $t = Livewire::actingAs(adminFitur())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog')
        ->assertDispatched('swal-success');

    expect(FiturPublik::ditutup('blog'))->toBeTrue();

    $t->call('alihkanFitur', 'blog');

    expect(FiturPublik::ditutup('blog'))->toBeFalse();
});

it('tanpa izin kelola, menutup halaman publik ditolak server', function () {
    Livewire::actingAs(adminFitur(['view_jeda_layanan']))
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog')
        ->assertDispatched('swal-error');

    expect(FiturPublik::ditutup('blog'))->toBeFalse();
});

it('keterangan tersimpan tanpa mengubah status tutupnya', function () {
    FiturPublik::setel('blog', true);

    Livewire::actingAs(adminFitur())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->set('pesanFitur.blog', 'Artikel sedang dirapikan.')
        ->call('simpanPesanFitur', 'blog');

    expect(FiturPublik::pesan('blog'))->toBe('Artikel sedang dirapikan.')
        ->and(FiturPublik::ditutup('blog'))->toBeTrue();
});
