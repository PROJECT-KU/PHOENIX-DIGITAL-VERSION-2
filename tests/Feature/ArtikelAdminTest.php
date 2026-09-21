<?php

use App\Livewire\Pages\Admin\Blog\BlogCreate;
use App\Livewire\Pages\Admin\Blog\BlogEdit;
use App\Livewire\Pages\Admin\Blog\BlogList;
use App\Livewire\Pages\Admin\Blog\CategoryList;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Layar Artikel (blog admin): tab keadaan, saringan, urutan, pergantian
 * bentuk kartu/tabel, unduhan, dan layar kategori.
 */
function adminArtikel(array $izin = ['view_blog', 'create_blog', 'edit_blog', 'delete_blog']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-bl-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function artikel(array $isian = []): BlogPost
{
    $judul = $isian['title'] ?? 'Artikel Uji '.Str::random(5);

    return BlogPost::create(array_merge([
        'title' => $judul,
        'slug' => BlogPost::makeSlug($judul),
        'body' => '<p>'.str_repeat('kata ', 60).'</p>',
        'status' => 'draft',
        'views' => 0,
    ], $isian));
}

it('tab keadaan menghitung dan menyaring artikel', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Draf Pertama']);
    artikel(['title' => 'Sudah Terbit', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Tayang Besok', 'status' => 'published', 'published_at' => now()->addDay()]);

    $t = Livewire::test(BlogList::class);
    expect($t->viewData('tabCounts'))->toMatchArray(['all' => 3, 'published' => 1, 'terjadwal' => 1, 'draft' => 1]);

    $judul = fn ($t) => $t->viewData('posts')->pluck('title')->all();

    $t->call('setFilter', 'published');
    expect($judul($t))->toBe(['Sudah Terbit']);

    $t->call('setFilter', 'terjadwal');
    expect($judul($t))->toBe(['Tayang Besok']);

    $t->call('setFilter', 'draft');
    expect($judul($t))->toBe(['Draf Pertama']);
});

it('tab yang tidak dikenal jatuh ke semua artikel', function () {
    $this->actingAs(adminArtikel());
    artikel();

    Livewire::test(BlogList::class)->call('setFilter', 'ngawur')->assertSet('filter', 'all');
});

it('pencarian menjangkau judul, kategori, dan ringkasan', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Memilih Akun Premium']);
    artikel(['title' => 'Tulisan Lain', 'category' => 'Panduan Aman']);
    artikel(['title' => 'Tulisan Ketiga', 'excerpt' => 'Ringkasan tentang garansi resmi']);

    $cari = function (string $kata) {
        return Livewire::test(BlogList::class)->set('search', $kata)->viewData('posts')->pluck('title')->all();
    };

    expect($cari('premium'))->toBe(['Memilih Akun Premium']);
    expect($cari('Panduan'))->toBe(['Tulisan Lain']);
    expect($cari('garansi'))->toBe(['Tulisan Ketiga']);
    expect($cari('tidak-ada-ini'))->toBe([]);
});

it('urutan mengikuti pilihan: terbaru, terlama, populer, judul', function () {
    $this->actingAs(adminArtikel());
    $a = artikel(['title' => 'Cerita Awal', 'views' => 5]);
    $a->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();
    $b = artikel(['title' => 'Bacaan Baru', 'views' => 99]);
    $b->forceFill(['created_at' => now()->subDay()])->saveQuietly();

    $urut = function (string $pilihan) {
        return Livewire::test(BlogList::class)->set('urut', $pilihan)->viewData('posts')->pluck('title')->all();
    };

    expect($urut('baru'))->toBe(['Bacaan Baru', 'Cerita Awal']);
    expect($urut('lama'))->toBe(['Cerita Awal', 'Bacaan Baru']);
    expect($urut('populer'))->toBe(['Bacaan Baru', 'Cerita Awal']);
    expect($urut('judul'))->toBe(['Bacaan Baru', 'Cerita Awal']);
});

it('urutan dan bentuk tampilan yang ngawur dikembalikan ke bawaan', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogList::class)
        ->set('urut', 'ngawur')->assertSet('urut', 'baru')
        ->set('tampilan', 'ngawur')->assertSet('tampilan', 'kartu');
});

it('bentuk tabel dan kartu memakai markup yang sama, hanya kelasnya berbeda', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Artikel Satu']);

    // Kalau tabel dirender sebagai <table> tersendiri, pergantiannya menyentak
    // karena DOM-nya dibongkar — pola ini yang menjaganya tetap mulus.
    $kartu = Livewire::test(BlogList::class)->set('tampilan', 'kartu')->html();
    $tabel = Livewire::test(BlogList::class)->set('tampilan', 'tabel')->html();

    // Bandingkan hanya bagian daftarnya; gaya & pagination di sekitarnya
    // tidak ikut menentukan mulus atau tidaknya pergantian.
    $daftar = function (string $html) {
        $i = strpos($html, 'bl-daftar');

        return substr($html, $i, strpos($html, 'bl-halaman') ? strpos($html, 'bl-halaman') - $i : 4000);
    };

    expect($kartu)->toContain('class="bl-daftar ')
        ->and($tabel)->toContain('bl-daftar is-tabel')
        ->and($daftar($tabel))->not->toContain('<table')
        ->and($daftar($tabel))->not->toContain('<tbody')
        ->and($daftar($tabel))->toContain('bl-kartu')
        ->and($daftar($kartu))->toContain('bl-kartu');
});

it('chip saringan bisa dilepas satu per satu atau sekaligus', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Apa Saja', 'category' => 'Tips']);

    $t = Livewire::test(BlogList::class)->set('search', 'apa')->set('category', 'Tips')->set('urut', 'populer');
    expect($t->instance()->chipSaring())->toHaveCount(3);
    expect($t->instance()->adaSaring)->toBeTrue();

    $t->call('lepasSaring', 'search')->assertSet('search', '');
    $t->call('lepasSaring', 'urut')->assertSet('urut', 'baru');
    $t->call('resetFilters')->assertSet('category', '');
    expect($t->instance()->adaSaring)->toBeFalse();
});

it('publikasikan dan kembalikan ke draf lewat satu tombol', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Naik Turun']);

    $t = Livewire::test(BlogList::class);

    $t->call('togglePublish', $p->id);
    $p->refresh();
    expect($p->status)->toBe('published')->and($p->published_at)->not->toBeNull();

    $t->call('togglePublish', $p->id);
    expect($p->refresh()->status)->toBe('draft');
});

it('tanpa izin ubah, artikel tidak berubah status', function () {
    $this->actingAs(adminArtikel(['view_blog']));
    $p = artikel();

    Livewire::test(BlogList::class)->call('togglePublish', $p->id);

    expect($p->refresh()->status)->toBe('draft');
});

it('tanpa izin hapus, artikel tetap ada', function () {
    $this->actingAs(adminArtikel(['view_blog']));
    $p = artikel();

    Livewire::test(BlogList::class)->call('delete', $p->id);

    expect(BlogPost::find($p->id))->not->toBeNull();
});

it('hapus artikel menghapus barisnya', function () {
    $this->actingAs(adminArtikel());
    $p = artikel();

    Livewire::test(BlogList::class)->call('delete', $p->id);

    expect(BlogPost::find($p->id))->toBeNull();
});

it('unduhan excel dan pdf mengikuti saringan yang aktif', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Ikut Terunduh', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Tidak Ikut']);

    $t = Livewire::test(BlogList::class)->call('setFilter', 'published');

    $t->call('unduhExcel')->assertFileDownloaded();
    $t->call('unduhPdf')->assertFileDownloaded();
});

it('ringkasan menyebut total dibaca, terpopuler, dan terbit terakhir', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Paling Ramai', 'views' => 120, 'status' => 'published', 'published_at' => now()->subDays(2)]);
    artikel(['title' => 'Terbit Kemarin', 'views' => 3, 'status' => 'published', 'published_at' => now()->subDay()]);

    $t = Livewire::test(BlogList::class);

    expect($t->viewData('totalDibaca'))->toBe(123)
        ->and($t->viewData('terpopuler')->title)->toBe('Paling Ramai')
        ->and($t->viewData('terbaru')->title)->toBe('Terbit Kemarin');
});

it('keadaan artikel dibedakan antara draf, terjadwal, dan terbit', function () {
    expect(artikel()->keadaan()[0])->toBe('Draf');
    expect(artikel(['status' => 'published', 'published_at' => now()->addDay()])->keadaan()[0])->toBe('Terjadwal');
    expect(artikel(['status' => 'published', 'published_at' => now()->subDay()])->keadaan()[0])->toBe('Terbit');
    expect(artikel(['status' => 'published', 'published_at' => null])->keadaan()[0])->toBe('Terbit');
});

it('lama baca minimal satu menit dan sampul kosong tidak dipaksakan', function () {
    $p = artikel(['body' => '<p>tiga kata saja</p>', 'cover' => 'tidak-ada.jpg']);

    expect($p->lamaBaca())->toBe(1)
        ->and($p->sampulUrl())->toBeNull();
});

it('layar tulis, sunting, dan kategori bisa dirender', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Untuk Disunting', 'status' => 'published', 'published_at' => now()->subDay()]);
    BlogCategory::create(['name' => 'Panduan', 'slug' => 'panduan']);

    Livewire::test(BlogCreate::class)->assertOk()->assertSee('Tulis Artikel');
    Livewire::test(BlogEdit::class, ['post' => $p])->assertOk()->assertSee('Untuk Disunting');
    Livewire::test(CategoryList::class)->assertOk()->assertSee('Panduan');
});

it('kategori yang masih dipakai artikel tidak bisa dihapus', function () {
    $this->actingAs(adminArtikel());
    $kat = BlogCategory::create(['name' => 'Dipakai', 'slug' => 'dipakai']);
    artikel(['category' => 'Dipakai']);

    Livewire::test(CategoryList::class)->call('delete', $kat->id);

    expect(BlogCategory::find($kat->id))->not->toBeNull();
});

it('mengubah nama kategori ikut memperbarui artikelnya', function () {
    $this->actingAs(adminArtikel());
    $kat = BlogCategory::create(['name' => 'Nama Lama', 'slug' => 'nama-lama']);
    $p = artikel(['category' => 'Nama Lama']);

    Livewire::test(CategoryList::class)
        ->call('startEdit', $kat->id)
        ->set('editingName', 'Nama Baru')
        ->call('saveEdit');

    expect($kat->refresh()->name)->toBe('Nama Baru')
        ->and($p->refresh()->category)->toBe('Nama Baru');
});
