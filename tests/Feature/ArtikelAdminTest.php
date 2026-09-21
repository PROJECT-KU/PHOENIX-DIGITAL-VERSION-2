<?php

use App\Livewire\Pages\Admin\Blog\BlogCreate;
use App\Livewire\Pages\Admin\Blog\BlogEdit;
use App\Livewire\Pages\Admin\Blog\BlogForm;
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

it('hapus artikel memindahkannya ke tong sampah, bukan menghapus permanen', function () {
    $this->actingAs(adminArtikel());
    $p = artikel();

    $t = Livewire::test(BlogList::class)->call('delete', $p->id);

    expect(BlogPost::find($p->id))->toBeNull()
        ->and(BlogPost::onlyTrashed()->find($p->id))->not->toBeNull();

    // Tombol urungkan muncul dan mengembalikannya.
    $t->assertSet('undo', [(string) $p->id])->call('batalkanHapus');
    expect(BlogPost::find($p->id))->not->toBeNull();
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

// ===================== Tong sampah =====================

it('tab tong sampah memuat artikel yang dibuang dan bisa mengembalikannya', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Sudah Dibuang']);
    $p->delete();

    $t = Livewire::test(BlogList::class);
    expect($t->viewData('tabCounts')['sampah'])->toBe(1);

    $t->call('setFilter', 'sampah');
    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Sudah Dibuang']);

    $t->call('pulihkan', $p->id);
    expect(BlogPost::find($p->id))->not->toBeNull();
});

it('hapus permanen membuang baris dan berkas sampulnya', function () {
    $this->actingAs(adminArtikel());
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('img/blog/sampul.webp', 'x');

    $p = artikel(['cover' => 'sampul.webp']);
    $p->delete();

    Livewire::test(BlogList::class)->call('hapusPermanen', $p->id);

    expect(BlogPost::withTrashed()->find($p->id))->toBeNull();
    \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('img/blog/sampul.webp');
});

it('kosongkan tong sampah menghapus semuanya sekaligus', function () {
    $this->actingAs(adminArtikel());
    artikel()->delete();
    artikel()->delete();
    $tetap = artikel();

    Livewire::test(BlogList::class)->call('kosongkanSampah');

    expect(BlogPost::withTrashed()->count())->toBe(1)
        ->and(BlogPost::find($tetap->id))->not->toBeNull();
});

it('tanpa izin hapus, tong sampah tidak bisa dikosongkan', function () {
    $this->actingAs(adminArtikel(['view_blog']));
    artikel()->delete();

    Livewire::test(BlogList::class)->call('kosongkanSampah');

    expect(BlogPost::onlyTrashed()->count())->toBe(1);
});

// ===================== Aksi massal =====================

it('mencentang halaman lalu mengubah status seluruhnya', function () {
    $this->actingAs(adminArtikel());
    $a = artikel();
    $b = artikel();

    $t = Livewire::test(BlogList::class)->call('pilihHalaman', [(string) $a->id, (string) $b->id]);
    expect($t->get('pilih'))->toHaveCount(2);

    $t->call('massalStatus', 'published');

    expect($a->refresh()->status)->toBe('published')
        ->and($b->refresh()->status)->toBe('published')
        ->and($a->published_at)->not->toBeNull();

    // Centang yang sama sekali lagi melepasnya (saklar, bukan tumpuk).
    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $a->id])
        ->call('pilihHalaman', [(string) $a->id])
        ->assertSet('pilih', []);
});

it('aksi massal memindah kategori dan membuang ke tong sampah', function () {
    $this->actingAs(adminArtikel());
    BlogCategory::create(['name' => 'Tujuan', 'slug' => 'tujuan']);
    $a = artikel(['category' => 'Lama']);
    $b = artikel(['category' => 'Lama']);

    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $a->id, (string) $b->id])
        ->call('massalKategori', 'Tujuan');

    expect($a->refresh()->category)->toBe('Tujuan')->and($b->refresh()->category)->toBe('Tujuan');

    $t = Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $a->id, (string) $b->id])
        ->call('massalHapus');

    expect(BlogPost::onlyTrashed()->count())->toBe(2)
        ->and($t->get('undo'))->toHaveCount(2);

    $t->call('setFilter', 'sampah')
        ->call('pilihHalaman', [(string) $a->id, (string) $b->id])
        ->call('massalPulihkan');

    expect(BlogPost::onlyTrashed()->count())->toBe(0);
});

it('pilih semua hasil mengikuti saringan yang aktif', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Ikut Terpilih', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Tidak Ikut']);

    $t = Livewire::test(BlogList::class)->call('setFilter', 'published')->call('pilihSemuaHasil');

    expect($t->get('pilih'))->toHaveCount(1);
});

it('tanpa izin ubah, aksi massal tidak mengubah apa pun', function () {
    $this->actingAs(adminArtikel(['view_blog']));
    $p = artikel();

    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $p->id])
        ->call('massalStatus', 'published');

    expect($p->refresh()->status)->toBe('draft');
});

// ===================== Duplikat & sematan =====================

it('duplikat membuat draf baru dengan slug berbeda dan salinan sampul', function () {
    $this->actingAs(adminArtikel());
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('img/blog/asli.webp', 'x');

    $p = artikel([
        'title' => 'Artikel Asli',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'cover' => 'asli.webp',
        'views' => 90,
        'is_featured' => true,
    ]);

    Livewire::test(BlogList::class)->call('duplikat', $p->id);

    $salinan = BlogPost::where('id', '!=', $p->id)->first();

    expect($salinan->title)->toStartWith('Salinan — ')
        ->and($salinan->slug)->not->toBe($p->slug)
        // Salinan tidak boleh ikut tayang, ikut disematkan, atau mewarisi pembaca.
        ->and($salinan->status)->toBe('draft')
        ->and($salinan->published_at)->toBeNull()
        ->and($salinan->is_featured)->toBeFalse()
        ->and($salinan->views)->toBe(0)
        ->and($salinan->cover)->not->toBe('asli.webp');

    \Illuminate\Support\Facades\Storage::disk('public')->assertExists('img/blog/'.$salinan->cover);
});

it('sematan bisa dinyalakan dan artikel tersemat naik ke atas daftar', function () {
    $this->actingAs(adminArtikel());
    $lama = artikel(['title' => 'Terbit Lama', 'status' => 'published', 'published_at' => now()->subDays(10)]);
    $lama->forceFill(['created_at' => now()->subDays(10)])->saveQuietly();
    $baru = artikel(['title' => 'Terbit Baru', 'status' => 'published', 'published_at' => now()->subDay()]);
    $baru->forceFill(['created_at' => now()->subDay()])->saveQuietly();

    $t = Livewire::test(BlogList::class);
    expect($t->viewData('posts')->first()->title)->toBe('Terbit Baru');

    $t->call('toggleSemat', $lama->id);
    expect($lama->refresh()->is_featured)->toBeTrue();

    expect(Livewire::test(BlogList::class)->viewData('posts')->first()->title)->toBe('Terbit Lama');
});

it('saringan hanya-disematkan menyisakan artikel yang tersemat', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Biasa']);
    artikel(['title' => 'Tersemat', 'is_featured' => true]);

    $t = Livewire::test(BlogList::class)->set('fUnggulan', true);

    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Tersemat']);
    expect(collect($t->instance()->chipSaring())->pluck('nama'))->toContain('fUnggulan');
});

// ===================== Tag =====================

it('tag bisa disaring dan dicari', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Pakai Tag', 'tags' => ['garansi', 'akun']]);
    artikel(['title' => 'Tanpa Tag']);

    $t = Livewire::test(BlogList::class);
    expect($t->viewData('tagDaftar'))->toBe(['akun', 'garansi']);

    $t->set('tag', 'garansi');
    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Pakai Tag']);

    expect(Livewire::test(BlogList::class)->set('search', 'garansi')->viewData('posts')->pluck('title')->all())
        ->toBe(['Pakai Tag']);
});

// ===================== Mandek & baca 30 hari =====================

it('artikel lama tanpa pembaca sebulan terakhir ditandai mandek', function () {
    $this->actingAs(adminArtikel());
    $mandek = artikel(['title' => 'Sepi', 'status' => 'published', 'published_at' => now()->subDays(200)]);
    $hidup = artikel(['title' => 'Ramai', 'status' => 'published', 'published_at' => now()->subDays(200)]);
    $hidup->bacaHarian()->create(['tanggal' => now()->subDays(2)->toDateString(), 'jumlah' => 7]);
    artikel(['title' => 'Baru', 'status' => 'published', 'published_at' => now()->subDay()]);

    $t = Livewire::test(BlogList::class);
    expect($t->viewData('jumlahMandek'))->toBe(1);

    $t->set('fMandek', true);
    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Sepi']);

    expect($mandek->refresh()->mandek())->toBeTrue()
        ->and($hidup->refresh()->mandek())->toBeFalse();
});

it('ringkasan membandingkan baca 30 hari dengan 30 hari sebelumnya', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['status' => 'published', 'published_at' => now()->subDays(90)]);
    $p->bacaHarian()->create(['tanggal' => now()->subDays(3)->toDateString(), 'jumlah' => 20]);
    $p->bacaHarian()->create(['tanggal' => now()->subDays(45)->toDateString(), 'jumlah' => 10]);

    $t = Livewire::test(BlogList::class);

    expect($t->viewData('dibaca30'))->toBe(20)
        ->and($t->viewData('dibaca30Sebelumnya'))->toBe(10);
});

it('urutan paling ramai 30 hari berbeda dari paling dibaca sepanjang masa', function () {
    $this->actingAs(adminArtikel());
    $lawas = artikel(['title' => 'Juara Lama', 'views' => 5000]);
    $kini = artikel(['title' => 'Sedang Ramai', 'views' => 40]);
    $lawas->bacaHarian()->create(['tanggal' => now()->subDays(80)->toDateString(), 'jumlah' => 5000]);
    $kini->bacaHarian()->create(['tanggal' => now()->subDay()->toDateString(), 'jumlah' => 40]);

    expect(Livewire::test(BlogList::class)->set('urut', 'populer')->viewData('posts')->first()->title)->toBe('Juara Lama');
    expect(Livewire::test(BlogList::class)->set('urut', 'populer30')->viewData('posts')->first()->title)->toBe('Sedang Ramai');
});

it('kepala kolom bergantian antara sepanjang masa dan 30 hari', function () {
    $this->actingAs(adminArtikel());
    artikel();

    Livewire::test(BlogList::class)
        ->call('urutkanKolom', 'baca')->assertSet('urut', 'populer')
        ->call('urutkanKolom', 'baca')->assertSet('urut', 'populer30')
        ->call('urutkanKolom', 'tanggal')->assertSet('urut', 'baru')
        ->call('urutkanKolom', 'tanggal')->assertSet('urut', 'lama')
        ->call('urutkanKolom', 'judul')->assertSet('urut', 'judul');
});

// ===================== Halaman publik =====================

it('draf tidak bisa dibuka publik tapi bisa dipratinjau admin', function () {
    $draf = artikel(['title' => 'Belum Tayang']);

    $this->get(route('blog.show', $draf->slug))->assertNotFound();

    $this->actingAs(adminArtikel());
    $this->get(route('blog.show', $draf->slug))
        ->assertOk()
        ->assertSee('Pratinjau');
});

it('pratinjau admin tidak ikut menambah hitungan pembaca', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['status' => 'published', 'published_at' => now()->subDay()]);

    $this->get(route('blog.show', $p->slug))->assertOk();

    expect($p->refresh()->views)->toBe(0)
        ->and(\App\Models\BlogPostRead::count())->toBe(0);
});

it('kunjungan pengunjung dihitung sekali per sesi dan tercatat per hari', function () {
    $p = artikel(['status' => 'published', 'published_at' => now()->subDay()]);

    $this->get(route('blog.show', $p->slug))->assertOk();
    // Muat ulang dalam sesi yang sama tidak boleh menambah lagi.
    $this->get(route('blog.show', $p->slug))->assertOk();

    expect($p->refresh()->views)->toBe(1);

    $baris = \App\Models\BlogPostRead::where('blog_post_id', $p->id)->first();
    expect($baris)->not->toBeNull()
        ->and($baris->jumlah)->toBe(1)
        ->and($baris->tanggal->toDateString())->toBe(now()->toDateString());
});

it('perayap mesin pencari tidak ikut dihitung', function () {
    $p = artikel(['status' => 'published', 'published_at' => now()->subDay()]);

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])
        ->get(route('blog.show', $p->slug))->assertOk();

    expect($p->refresh()->views)->toBe(0);
});

it('artikel tersemat memimpin halaman blog publik', function () {
    artikel(['title' => 'Terbit Baru', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Disematkan', 'status' => 'published', 'published_at' => now()->subDays(30), 'is_featured' => true]);

    $t = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class);

    expect($t->viewData('featured')->title)->toBe('Disematkan');
});

// ===================== Formulir =====================

it('slug mengikuti judul saat membuat, lalu berhenti mengikuti bila diketik sendiri', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)
        ->set('title', 'Judul Percobaan Satu')
        ->assertSet('slug', 'judul-percobaan-satu')
        ->set('slug', 'Alamat Pilihan Saya!')
        ->assertSet('slug', 'alamat-pilihan-saya')
        ->assertSet('slugManual', true);

    // Judul berubah, slug tetap.
    $t->set('title', 'Judul Yang Lain')->assertSet('slug', 'alamat-pilihan-saya');

    // Bisa dikembalikan mengikuti judul.
    $t->call('slugIkutJudul')->assertSet('slug', 'judul-yang-lain');
});

it('slug artikel yang sedang disunting tidak ikut berubah saat judul diubah', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Judul Lama']);

    Livewire::test(BlogForm::class, ['post' => $p])
        ->assertSet('slugManual', true)
        ->set('title', 'Judul Baru Sekali')
        ->assertSet('slug', $p->slug);
});

it('tag ditambah, ditolak bila kembar, dan dibatasi delapan', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)
        ->set('tagBaru', 'garansi')->call('tambahTag')
        ->set('tagBaru', 'GARANSI')->call('tambahTag')
        ->assertSet('tags', ['garansi']);

    foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'] as $nama) {
        $t->set('tagBaru', $nama)->call('tambahTag');
    }

    expect($t->get('tags'))->toHaveCount(8);

    $t->call('hapusTag', 0);
    expect($t->get('tags'))->toHaveCount(7);
});

it('simpan menyertakan tag, teks alternatif, dan jejak penyunting', function () {
    $admin = adminArtikel();
    $this->actingAs($admin);

    Livewire::test(BlogForm::class)
        ->set('title', 'Artikel Dengan Tag')
        ->set('body', '<p>'.str_repeat('kata ', 80).'</p>')
        ->set('cover_alt', 'Ilustrasi contoh')
        ->set('tagBaru', 'panduan')->call('tambahTag')
        ->call('save', 'published');

    $p = BlogPost::where('title', 'Artikel Dengan Tag')->first();

    expect($p->status)->toBe('published')
        ->and($p->tagDaftar())->toBe(['panduan'])
        ->and($p->cover_alt)->toBe('Ilustrasi contoh')
        // Penulis publik tetap "admin"; nama karyawan hanya di jejak internal.
        ->and($p->author)->toBe('admin');

    Livewire::test(BlogForm::class, ['post' => $p])->set('title', 'Judul Diubah')->call('save');

    expect($p->refresh()->updated_by)->toBe($admin->id);
});

it('simpan sebagai draf tidak menerbitkan artikel', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogForm::class)
        ->set('title', 'Masih Draf Saja')
        ->set('body', '<p>'.str_repeat('kata ', 80).'</p>')
        ->set('status', 'published')
        ->call('save', 'draft');

    expect(BlogPost::where('title', 'Masih Draf Saja')->first()->status)->toBe('draft');
});

it('simpan otomatis membuat draf lalu memperbaruinya di tempat', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)
        ->set('title', 'Tulisan Panjang Yang Belum Disimpan')
        ->set('body', '<p>'.str_repeat('kata ', 80).'</p>')
        ->call('simpanOtomatis');

    $t->assertSet('mode', 'edit');
    expect(BlogPost::count())->toBe(1);

    $p = BlogPost::first();
    expect($p->status)->toBe('draft');

    // Panggilan berikutnya MEMPERBARUI, tidak membuat draf kedua.
    $t->set('title', 'Judulnya Sudah Diperbaiki')->call('simpanOtomatis');

    expect(BlogPost::count())->toBe(1)
        ->and($p->refresh()->title)->toBe('Judulnya Sudah Diperbaiki');
});

it('simpan otomatis melewati tulisan yang masih terlalu kosong', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogForm::class)->set('title', 'Abc')->call('simpanOtomatis');

    expect(BlogPost::count())->toBe(0);
});

it('daftar kelengkapan menandai yang masih kurang', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)->set('title', 'Judul Yang Cukup Panjang');
    $cek = collect($t->instance()->kelengkapan);

    expect($cek->firstWhere('label', 'Judul minimal 5 huruf')['ok'])->toBeTrue()
        ->and($cek->firstWhere('label', 'Kategori dipilih')['ok'])->toBeFalse()
        ->and($cek->firstWhere('label', 'Minimal satu tag')['ok'])->toBeFalse();
});

it('hitungan kata dan lama baca mengikuti isi artikel', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)->set('body', '<p>'.str_repeat('kata ', 400).'</p>');

    expect($t->instance()->jumlahKata)->toBe(400)
        ->and($t->instance()->lamaBaca)->toBe(2);
});

it('periksa tautan menandai tautan internal yang artikelnya tidak ada', function () {
    $this->actingAs(adminArtikel());
    \Illuminate\Support\Facades\Http::fake();
    artikel(['title' => 'Artikel Tujuan', 'slug' => 'artikel-tujuan']);

    $t = Livewire::test(BlogForm::class)->set('body',
        '<p><a href="/blog/artikel-tujuan">ada</a> <a href="/blog/tidak-ada-ini">hilang</a></p>');

    $t->call('periksaTautan');
    $hasil = collect($t->get('tautanPeriksa'));

    expect($hasil)->toHaveCount(2)
        ->and($hasil->firstWhere('url', '/blog/artikel-tujuan')['keadaan'])->toBe('baik')
        ->and($hasil->firstWhere('url', '/blog/tidak-ada-ini')['keadaan'])->toBe('rusak');

    // Tidak ada permintaan keluar untuk tautan internal.
    \Illuminate\Support\Facades\Http::assertNothingSent();
});

it('ringkasan otomatis tidak menimpa ringkasan yang ditulis sendiri', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)
        ->set('seoManual', true)
        ->set('excerpt', 'Ringkasan tulisan tangan.')
        ->set('body', '<p>'.str_repeat('Ini kalimat contoh yang cukup panjang untuk dipilih. ', 30).'</p>');

    expect($t->get('excerpt'))->toBe('Ringkasan tulisan tangan.');
});

// ===================== Kategori =====================

it('kategori menyimpan deskripsi dan bisa diurutkan menurut jumlah artikel', function () {
    $this->actingAs(adminArtikel());
    $sepi = BlogCategory::create(['name' => 'Sepi', 'slug' => 'sepi']);
    BlogCategory::create(['name' => 'Ramai', 'slug' => 'ramai']);
    artikel(['category' => 'Ramai']);
    artikel(['category' => 'Ramai']);

    Livewire::test(CategoryList::class)
        ->call('startEdit', $sepi->id)
        ->set('editingDescription', 'Kategori yang jarang dipakai')
        ->call('saveEdit');

    expect($sepi->refresh()->description)->toBe('Kategori yang jarang dipakai');

    $t = Livewire::test(CategoryList::class)->set('urut', 'jumlah');
    expect($t->viewData('categories')->first()->name)->toBe('Ramai');
});

it('menggabungkan kategori memindahkan artikelnya lalu menghapus kategori asal', function () {
    $this->actingAs(adminArtikel());
    $dari = BlogCategory::create(['name' => 'Tips', 'slug' => 'tips']);
    BlogCategory::create(['name' => 'Tips & Panduan', 'slug' => 'tips-panduan']);
    $p = artikel(['category' => 'Tips']);

    Livewire::test(CategoryList::class)
        ->call('startEdit', $dari->id)
        ->set('gabungKe', 'Tips & Panduan')
        ->call('gabungkanTerpilih');

    expect($p->refresh()->category)->toBe('Tips & Panduan')
        ->and(BlogCategory::find($dari->id))->toBeNull();
});

it('tanpa izin ubah, kategori tidak bisa digabungkan', function () {
    $this->actingAs(adminArtikel(['view_blog']));
    $dari = BlogCategory::create(['name' => 'Tips', 'slug' => 'tips']);
    BlogCategory::create(['name' => 'Panduan', 'slug' => 'panduan']);

    Livewire::test(CategoryList::class)->call('gabungkan', $dari->id, 'Panduan');

    expect(BlogCategory::find($dari->id))->not->toBeNull();
});

// ===================== Unduhan =====================

it('unduhan bisa menyertakan isi artikel dan mengikuti yang dicentang', function () {
    $this->actingAs(adminArtikel());
    $a = artikel(['title' => 'Hanya Ini Yang Dicentang']);
    artikel(['title' => 'Tidak Dicentang']);

    Livewire::test(BlogList::class)
        ->set('ikutIsi', true)
        ->call('pilihHalaman', [(string) $a->id])
        ->call('unduhExcel')
        ->assertFileDownloaded();

    Livewire::test(BlogList::class)->set('ikutIsi', true)->call('unduhPdf')->assertFileDownloaded();
});

// ===================== Alamat lama & berhenti tayang =====================

it('mengubah slug menyisakan pengalihan dari alamat lama', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Judul Awal', 'status' => 'published', 'published_at' => now()->subDay()]);
    $slugLama = $p->slug;

    Livewire::test(BlogForm::class, ['post' => $p])
        ->set('slug', 'alamat-yang-baru')
        ->call('save');

    expect($p->refresh()->slug)->toBe('alamat-yang-baru')
        ->and(\App\Models\BlogPostRedirect::where('slug_lama', $slugLama)->exists())->toBeTrue();

    // Alamat lama tidak mati: dialihkan permanen ke yang baru.
    $this->get('/blog/'.$slugLama)
        ->assertStatus(301)
        ->assertRedirect(route('blog.show', 'alamat-yang-baru'));
});

it('alamat yang dipakai sekarang tidak pernah jadi pengalihan ke dirinya sendiri', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Bolak Balik']);
    $awal = $p->slug;

    $t = Livewire::test(BlogForm::class, ['post' => $p]);
    $t->set('slug', 'alamat-sementara')->call('save');

    $p->refresh();
    Livewire::test(BlogForm::class, ['post' => $p])->set('slug', $awal)->call('save');

    expect(\App\Models\BlogPostRedirect::where('slug_lama', $awal)->exists())->toBeFalse()
        ->and(\App\Models\BlogPostRedirect::where('slug_lama', 'alamat-sementara')->exists())->toBeTrue();
});

it('artikel yang lewat waktu berhenti tayang hilang dari publik', function () {
    $habis = artikel([
        'title' => 'Promo Sudah Lewat',
        'status' => 'published',
        'published_at' => now()->subDays(10),
        'unpublish_at' => now()->subDay(),
    ]);
    $aktif = artikel(['title' => 'Masih Tayang', 'status' => 'published', 'published_at' => now()->subDay()]);

    expect($habis->keadaan()[0])->toBe('Berakhir')
        ->and(BlogPost::published()->pluck('title')->all())->toBe(['Masih Tayang']);

    $this->get(route('blog.show', $habis->slug))->assertNotFound();
    $this->get(route('blog.show', $aktif->slug))->assertOk();
});

it('waktu berhenti tayang harus sesudah waktu terbit', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogForm::class)
        ->set('title', 'Artikel Berjadwal')
        ->set('body', '<p>'.str_repeat('kata ', 80).'</p>')
        ->set('published_at', now()->addDays(5)->format('Y-m-d\TH:i'))
        ->set('unpublish_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasErrors('unpublish_at');
});

// ===================== Riwayat versi & bentrok =====================

it('riwayat mencatat isi sebelum ditimpa dan bisa dipulihkan', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Versi Satu', 'body' => '<p>'.str_repeat('awal ', 60).'</p>']);

    Livewire::test(BlogForm::class, ['post' => $p])
        ->set('title', 'Versi Dua')
        ->set('body', '<p>'.str_repeat('baru ', 60).'</p>')
        ->call('save');

    $revisi = $p->refresh()->revisi()->first();

    expect($p->title)->toBe('Versi Dua')
        ->and($revisi->title)->toBe('Versi Satu')
        ->and($revisi->body)->toContain('awal');

    // Memulihkan mengisi formulir, bukan langsung menyimpan.
    $t = Livewire::test(BlogForm::class, ['post' => $p->fresh()])->call('pulihkanRevisi', $revisi->id);

    expect($t->get('title'))->toBe('Versi Satu');
    $t->call('save');
    expect($p->refresh()->title)->toBe('Versi Satu');
});

it('riwayat dibatasi dua puluh versi terakhir', function () {
    $this->actingAs(adminArtikel());
    $p = artikel();

    for ($i = 0; $i < 25; $i++) {
        $p->catatRevisi();
    }

    expect($p->revisi()->count())->toBe(\App\Models\BlogPostRevision::BATAS);
});

it('memulihkan versi lama tidak mengubah status atau jadwal artikel', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Asli', 'status' => 'published', 'published_at' => now()->subDay()]);
    $p->catatRevisi();

    Livewire::test(BlogForm::class, ['post' => $p])
        ->call('pulihkanRevisi', $p->revisi()->first()->id)
        ->call('save');

    expect($p->refresh()->status)->toBe('published')
        ->and($p->published_at)->not->toBeNull();
});

it('menyimpan ditahan bila artikel sudah diubah orang lain lebih dulu', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Diperebutkan']);

    $t = Livewire::test(BlogForm::class, ['post' => $p]);

    // Orang lain menyimpan duluan.
    $p->forceFill(['title' => 'Diubah Orang Lain', 'updated_at' => now()->addMinute()])->save();

    $t->set('title', 'Versi Saya')->call('save')->assertSet('bentrok', true);
    expect($p->refresh()->title)->toBe('Diubah Orang Lain');

    // Tetap simpan kalau memang versinya yang benar.
    $t->call('timpaSaja');
    expect($p->refresh()->title)->toBe('Versi Saya');
});

it('simpan otomatis diam saja saat bentrok', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Jangan Ditimpa', 'body' => '<p>'.str_repeat('kata ', 60).'</p>']);

    $t = Livewire::test(BlogForm::class, ['post' => $p]);
    $p->forceFill(['title' => 'Punya Orang Lain', 'updated_at' => now()->addMinute()])->save();

    $t->set('title', 'Ketikan Saya')->call('simpanOtomatis');

    expect($p->refresh()->title)->toBe('Punya Orang Lain');
});

it('penanda sedang dibuka kedaluwarsa sendiri', function () {
    $lain = adminArtikel();
    $saya = adminArtikel();
    $p = artikel();

    $this->actingAs($saya);

    $p->forceFill(['dibuka_oleh' => $lain->id, 'dibuka_pada' => now()->subMinute()])->save();
    expect($p->refresh()->dipegangOrangLain())->toBeTrue();

    $p->forceFill(['dibuka_pada' => now()->subMinutes(30)])->save();
    expect($p->refresh()->dipegangOrangLain())->toBeFalse();

    // Penanda milik sendiri tidak pernah dianggap milik orang lain.
    $p->forceFill(['dibuka_oleh' => $saya->id, 'dibuka_pada' => now()])->save();
    expect($p->refresh()->dipegangOrangLain())->toBeFalse();
});

// ===================== Sisi publik =====================

it('tag tampil di halaman artikel dan bisa disaring di daftar blog', function () {
    artikel(['title' => 'Pakai Tag Garansi', 'tags' => ['garansi'], 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Tanpa Tag Sama Sekali', 'status' => 'published', 'published_at' => now()->subDays(2)]);

    $p = BlogPost::where('title', 'Pakai Tag Garansi')->first();
    $this->get(route('blog.show', $p->slug))->assertOk()->assertSee('#garansi');

    $t = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class);
    expect($t->viewData('tagDipakai'))->toBe(['garansi']);

    $t->call('pilihTag', 'garansi');
    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Pakai Tag Garansi']);

    // Ditekan lagi melepas saringannya.
    $t->call('pilihTag', 'garansi');
    expect($t->viewData('posts'))->toHaveCount(2);
});

it('artikel terkait mengutamakan yang berbagi tag', function () {
    $utama = artikel(['title' => 'Induk', 'category' => 'Umum', 'tags' => ['turnitin'], 'status' => 'published', 'published_at' => now()->subDays(5)]);
    artikel(['title' => 'Se-Tag', 'category' => 'Lain', 'tags' => ['turnitin'], 'status' => 'published', 'published_at' => now()->subDays(4)]);
    artikel(['title' => 'Sekategori Saja', 'category' => 'Umum', 'status' => 'published', 'published_at' => now()->subDay()]);

    $t = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogShow::class, ['post' => $utama]);

    expect($t->viewData('related')->first()->title)->toBe('Se-Tag');
});

it('teks alternatif sampul dipakai di halaman publik', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('img/blog/sampul.webp', 'x');

    $p = artikel([
        'title' => 'Artikel Bersampul',
        'cover' => 'sampul.webp',
        'cover_alt' => 'Ilustrasi akun premium di layar ponsel',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('blog.show', $p->slug))
        ->assertOk()
        ->assertSee('Ilustrasi akun premium di layar ponsel', false);
});

it('deskripsi kategori muncul saat daftar blog disaring kategori itu', function () {
    BlogCategory::create(['name' => 'Panduan', 'slug' => 'panduan', 'description' => 'Langkah praktis memakai layanan kami.']);
    artikel(['title' => 'Satu Panduan', 'category' => 'Panduan', 'status' => 'published', 'published_at' => now()->subDay()]);

    $t = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class)->set('category', 'Panduan');

    expect($t->viewData('ketKategori'))->toBe('Langkah praktis memakai layanan kami.');
    $t->assertSee('Langkah praktis memakai layanan kami.');
});

it('pencarian isi artikel di admin harus diminta lebih dulu', function () {
    $this->actingAs(adminArtikel());
    artikel([
        'title' => 'Judul Biasa',
        'body' => '<p>Di dalam tulisan ini ada kata rahasiadalamtubuh yang dicari.</p>',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    artikel(['title' => 'Artikel Lain', 'status' => 'published', 'published_at' => now()->subDays(2)]);

    // Memindai kolom longtext tanpa indeks itu mahal, jadi bawaannya mati.
    expect(Livewire::test(BlogList::class)->set('search', 'rahasiadalamtubuh')->viewData('posts')->pluck('title')->all())
        ->toBe([]);

    expect(Livewire::test(BlogList::class)->set('cariIsi', true)->set('search', 'rahasiadalamtubuh')->viewData('posts')->pluck('title')->all())
        ->toBe(['Judul Biasa']);

    // Di halaman publik tetap otomatis, tapi hanya mulai empat huruf.
    expect(Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class)->set('search', 'rahasiadalamtubuh')->viewData('posts')->pluck('title')->all())
        ->toBe(['Judul Biasa']);

    expect(Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class)->set('search', 'rah')->viewData('posts')->pluck('title')->all())
        ->toBe([]);
});

it('umpan rss memuat artikel terbit saja', function () {
    artikel(['title' => 'Artikel Terbit', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Masih Draf']);

    $this->get(route('blog.feed'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('Artikel Terbit')
        ->assertDontSee('Masih Draf');
});

it('alamat feed tidak ditangkap sebagai slug artikel', function () {
    expect(route('blog.feed'))->toEndWith('/blog/feed.xml');

    $this->get('/blog/feed.xml')->assertOk();
});

// ===================== Impor =====================

it('impor berkas markdown membuat draf baru', function () {
    $this->actingAs(adminArtikel());

    $berkas = \Illuminate\Http\UploadedFile::fake()->createWithContent(
        'panduan-impor.md',
        "# Panduan Hasil Impor\n\nParagraf **pertama** artikel.\n\n## Bagian Dua\n\n- satu\n- dua\n"
    );

    Livewire::test(BlogList::class)->set('berkasImpor', [$berkas]);

    $p = BlogPost::where('title', 'Panduan Hasil Impor')->first();

    expect($p)->not->toBeNull()
        // Selalu draf: hasil konversi belum tentu rapi.
        ->and($p->status)->toBe('draft')
        ->and($p->body)->toContain('<strong>pertama</strong>')
        ->and($p->body)->toContain('<h2>Bagian Dua</h2>')
        ->and($p->body)->toContain('<li>satu</li>')
        // Judul dari "# ..." tidak ikut tertulis dua kali di dalam isi.
        ->and($p->body)->not->toContain('Panduan Hasil Impor');
});

it('impor menolak jenis berkas yang tidak didukung', function () {
    $this->actingAs(adminArtikel());

    $berkas = \Illuminate\Http\UploadedFile::fake()->create('gambar.png', 10, 'image/png');

    Livewire::test(BlogList::class)->set('berkasImpor', [$berkas])->assertHasErrors('berkasImpor.0');

    expect(BlogPost::count())->toBe(0);
});

it('tanpa izin membuat artikel, impor ditolak', function () {
    $this->actingAs(adminArtikel(['view_blog']));

    $berkas = \Illuminate\Http\UploadedFile::fake()->createWithContent('catatan.md', "# Apa Saja\n\nIsi.\n");

    Livewire::test(BlogList::class)->set('berkasImpor', [$berkas]);

    expect(BlogPost::count())->toBe(0);
});

// ===================== Alt gambar & kata kunci =====================

it('gambar di isi artikel yang belum punya alt dilengkapi saat disimpan', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogForm::class)
        ->set('title', 'Artikel Dengan Gambar')
        ->set('body', '<p><img src="/a.webp"><img src="/b.webp" alt="sudah ada"><img src="/c.webp" alt=""></p>'.str_repeat('<p>kata kata</p>', 20))
        ->call('save', 'draft');

    $isi = BlogPost::where('title', 'Artikel Dengan Gambar')->value('body');

    expect($isi)->toContain('alt="Artikel Dengan Gambar"')
        // Yang sudah punya alt tidak disentuh — termasuk alt kosong yang
        // memang cara baku menandai gambar hiasan.
        ->and($isi)->toContain('alt="sudah ada"')
        ->and(substr_count($isi, 'alt='))->toBe(3);
});

it('kata kunci fokus diperiksa di empat tempat yang menentukan', function () {
    $this->actingAs(adminArtikel());

    $t = Livewire::test(BlogForm::class)
        ->set('seoManual', true)
        ->set('title', 'Cara Cek Plagiasi Skripsi')
        ->set('body', '<p>Cara cek plagiasi skripsi yang benar dimulai dari sini.</p>')
        ->set('meta_description', 'Ringkasan tanpa kata itu.')
        ->set('focus_keyword', 'cek plagiasi skripsi');

    $hasil = collect($t->instance()->periksaKunci)->pluck('ok', 'label');

    expect($hasil['Muncul di judul'])->toBeTrue()
        ->and($hasil['Muncul di alamat artikel'])->toBeTrue()
        ->and($hasil['Muncul di paragraf awal'])->toBeTrue()
        ->and($hasil['Muncul di meta description'])->toBeFalse();
});

it('grafik baca menyusun tiga puluh hari termasuk hari tanpa pembaca', function () {
    $this->actingAs(adminArtikel());
    $p = artikel();
    $p->bacaHarian()->create(['tanggal' => now()->toDateString(), 'jumlah' => 9]);
    $p->bacaHarian()->create(['tanggal' => now()->subDays(5)->toDateString(), 'jumlah' => 4]);

    $grafik = Livewire::test(BlogForm::class, ['post' => $p])->instance()->grafikBaca;

    expect($grafik)->toHaveCount(30)
        ->and(end($grafik))->toBe(9)
        ->and($grafik[24])->toBe(4)
        ->and(array_sum($grafik))->toBe(13);
});

// ===================== Pemangkasan terjadwal =====================

it('tong sampah dipangkas setelah tenggangnya lewat, berikut sampulnya', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('img/blog/tua.webp', 'x');

    $tua = artikel(['title' => 'Lama Di Sampah', 'cover' => 'tua.webp']);
    $tua->delete();
    $tua->forceFill(['deleted_at' => now()->subDays(120)])->saveQuietly();

    $baru = artikel(['title' => 'Baru Dibuang']);
    $baru->delete();

    $this->artisan('artikel:bersihkan-sampah --hari=90')->assertSuccessful();

    expect(BlogPost::withTrashed()->find($tua->id))->toBeNull()
        ->and(BlogPost::withTrashed()->find($baru->id))->not->toBeNull();

    \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('img/blog/tua.webp');
});

it('mode kering tidak menghapus apa pun', function () {
    $p = artikel();
    $p->delete();
    $p->forceFill(['deleted_at' => now()->subDays(200)])->saveQuietly();

    $this->artisan('artikel:bersihkan-sampah --hari=90 --kering')->assertSuccessful();

    expect(BlogPost::withTrashed()->count())->toBe(1);
});

it('hitungan baca harian yang terlalu tua dipangkas tanpa menyentuh total', function () {
    $p = artikel(['views' => 500]);
    $p->bacaHarian()->create(['tanggal' => now()->subDays(500)->toDateString(), 'jumlah' => 300]);
    $p->bacaHarian()->create(['tanggal' => now()->subDays(10)->toDateString(), 'jumlah' => 5]);

    $this->artisan('artikel:pangkas-baca --hari=400')->assertSuccessful();

    expect(\App\Models\BlogPostRead::count())->toBe(1)
        ->and($p->refresh()->views)->toBe(500);
});

it('draf hasil simpan otomatis yang terbengkalai dibuang ke tong sampah', function () {
    $otomatis = artikel(['title' => 'Draf Terbengkalai']);
    $otomatis->forceFill(['disimpan_manual' => false, 'updated_at' => now()->subDays(90)])->saveQuietly();

    $sengaja = artikel(['title' => 'Draf Disimpan Sengaja']);
    $sengaja->forceFill(['updated_at' => now()->subDays(90)])->saveQuietly();

    $this->artisan('artikel:bersihkan-draf --hari=60')->assertSuccessful();

    // Dibuang ke TONG SAMPAH, bukan dihapus — masih bisa dikembalikan.
    expect(BlogPost::find($otomatis->id))->toBeNull()
        ->and(BlogPost::onlyTrashed()->find($otomatis->id))->not->toBeNull()
        ->and(BlogPost::find($sengaja->id))->not->toBeNull();
});

it('artikel hasil simpan manual tidak pernah ikut terpangkas', function () {
    $this->actingAs(adminArtikel());

    Livewire::test(BlogForm::class)
        ->set('title', 'Disimpan Dengan Sengaja')
        ->set('body', '<p>'.str_repeat('kata ', 80).'</p>')
        ->call('save', 'draft');

    $p = BlogPost::where('title', 'Disimpan Dengan Sengaja')->first();
    $p->forceFill(['updated_at' => now()->subDays(365)])->saveQuietly();

    $this->artisan('artikel:bersihkan-draf --hari=60')->assertSuccessful();

    expect(BlogPost::find($p->id))->not->toBeNull();
});

// ===================== Gambar di dalam isi =====================

it('gambar yang disisipkan ke isi diunggah sebagai berkas, bukan base64', function () {
    $this->actingAs(adminArtikel());
    \Illuminate\Support\Facades\Storage::fake('public');

    $gambar = \Illuminate\Http\UploadedFile::fake()->image('foto.jpg', 1200, 800);

    $t = Livewire::test(BlogForm::class)->set('gambarIsi', $gambar);

    $t->assertDispatched('gambar-tersisip');

    $berkas = \Illuminate\Support\Facades\Storage::disk('public')->files('img/blog');
    expect($berkas)->toHaveCount(1)
        ->and($berkas[0])->toContain('blog_isi_')
        // Medannya dikosongkan lagi supaya unggahan berikutnya bersih.
        ->and($t->get('gambarIsi'))->toBeNull();
});

it('penyaring html tidak lagi membuang atribut gambar yang dipakai', function () {
    $bersih = \App\Support\HtmlSanitizer::bersihkan(
        '<p><img src="/a.webp" alt="Teks" title="Keterangan" loading="lazy" decoding="async" onerror="alert(1)"></p>'
    );

    expect($bersih)->toContain('alt="Teks"')
        ->toContain('title="Keterangan"')
        ->toContain('loading="lazy"')
        ->toContain('decoding="async"')
        // Yang berbahaya tetap dibuang.
        ->not->toContain('onerror');
});

// ===================== Beda versi =====================

it('beda versi menandai kata yang dibuang dan ditambahkan', function () {
    $hasil = (string) \App\Support\BedaTeks::antara('<p>satu dua tiga</p>', '<p>satu dua empat lima</p>');

    expect($hasil)->toContain('<del class="bd-buang">tiga</del>')
        ->toContain('<ins class="bd-tambah">empat lima</ins>');

    expect(\App\Support\BedaTeks::ringkas('<p>satu dua tiga</p>', '<p>satu dua empat lima</p>'))
        ->toBe('+2 kata, −1 kata');

    expect(\App\Support\BedaTeks::ringkas('<p>sama saja</p>', '<p>sama saja</p>'))->toBe('isi sama');
});

it('layar sunting bisa menampilkan beda satu versi lalu menutupnya', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Judul Kini', 'body' => '<p>isi lama sekali</p>']);
    $p->catatRevisi();
    $revisi = $p->revisi()->first();

    $t = Livewire::test(BlogForm::class, ['post' => $p])->set('body', '<p>isi baru sekali</p>');

    expect($t->instance()->beda)->toBeNull();

    $t->call('lihatBeda', $revisi->id);
    $beda = $t->instance()->beda;

    expect($beda['revisi']->id)->toBe($revisi->id)
        ->and((string) $beda['isi'])->toContain('bd-buang')
        ->and((string) $beda['isi'])->toContain('bd-tambah');

    // Tombol yang sama menutupnya lagi.
    $t->call('lihatBeda', $revisi->id);
    expect($t->instance()->beda)->toBeNull();
});

// ===================== Kelola alamat lama =====================

it('alamat lama artikel bisa dilihat dan dihapus dari layar sunting', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Judul Awal']);
    $slugLama = $p->slug;

    Livewire::test(BlogForm::class, ['post' => $p])->set('slug', 'alamat-benar')->call('save');

    $p->refresh();
    $t = Livewire::test(BlogForm::class, ['post' => $p]);
    expect($t->instance()->pengalihan)->toHaveCount(1);

    $alih = \App\Models\BlogPostRedirect::where('slug_lama', $slugLama)->first();
    $t->call('hapusPengalihan', $alih->id);

    expect(\App\Models\BlogPostRedirect::count())->toBe(0);
    $this->get('/blog/'.$slugLama)->assertNotFound();
});

it('tanpa izin ubah, alamat lama tidak bisa dihapus', function () {
    $p = artikel();
    $alih = \App\Models\BlogPostRedirect::create(['slug_lama' => 'alamat-lama', 'blog_post_id' => $p->id]);

    $this->actingAs(adminArtikel(['view_blog']));
    Livewire::test(BlogForm::class, ['post' => $p])->call('hapusPengalihan', $alih->id);

    expect(\App\Models\BlogPostRedirect::count())->toBe(1);
});

// ===================== Sitemap & 404 =====================

it('sitemap menyebut waktu perubahan artikel dan halaman kategori & tag', function () {
    artikel([
        'title' => 'Artikel Sitemap',
        'category' => 'Panduan',
        'tags' => ['garansi'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $isi = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($isi)->toContain('<lastmod>')
        ->toContain(route('blog.index', ['kategori' => 'Panduan']))
        ->toContain(route('blog.index', ['tag' => 'garansi']));
});

it('sitemap tidak memuat artikel yang sudah berhenti tayang', function () {
    artikel([
        'title' => 'Sudah Berakhir',
        'slug' => 'sudah-berakhir',
        'status' => 'published',
        'published_at' => now()->subDays(5),
        'unpublish_at' => now()->subDay(),
    ]);

    expect($this->get('/sitemap.xml')->getContent())->not->toContain('/blog/sudah-berakhir');
});

it('alamat artikel yang salah menawarkan judul yang mirip', function () {
    artikel([
        'title' => 'Cara Cek Plagiasi Skripsi',
        'slug' => 'cara-cek-plagiasi-skripsi',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get('/blog/cara-cek-plagiasi-tesis')
        ->assertNotFound()
        ->assertSee('Artikel ini tidak ada')
        ->assertSee('Cara Cek Plagiasi Skripsi');
});

it('halaman tidak ditemukan tetap menawarkan tulisan terbaru saat tak ada yang mirip', function () {
    artikel(['title' => 'Tulisan Paling Baru', 'status' => 'published', 'published_at' => now()->subDay()]);

    $this->get('/blog/zzzz')
        ->assertNotFound()
        ->assertSee('Tulisan Paling Baru');
});

it('daftar blog menandai halaman berikutnya untuk mesin pencari', function () {
    for ($i = 0; $i < 15; $i++) {
        artikel(['title' => 'Artikel Nomor '.$i, 'status' => 'published', 'published_at' => now()->subDays($i + 1)]);
    }

    $this->get('/blog')->assertOk()->assertSee('rel="next"', false);
});

// ===================== Aksi massal tag & saringan penyunting =====================

it('tag bisa ditambahkan dan dilepas untuk banyak artikel sekaligus', function () {
    $this->actingAs(adminArtikel());
    $a = artikel(['tags' => ['lama']]);
    $b = artikel();

    $t = Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $a->id, (string) $b->id])
        ->set('tagMassal', 'promo')
        ->call('massalTagTambah');

    expect($a->refresh()->tagDaftar())->toBe(['lama', 'promo'])
        ->and($b->refresh()->tagDaftar())->toBe(['promo'])
        // Medannya dikosongkan supaya aksi berikutnya tidak memakai tag lama.
        ->and($t->get('tagMassal'))->toBe('');

    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $a->id, (string) $b->id])
        ->set('tagMassal', 'promo')
        ->call('massalTagLepas');

    expect($a->refresh()->tagDaftar())->toBe(['lama'])
        ->and($b->refresh()->tags)->toBeNull();
});

it('aksi massal tag menghormati batas delapan tag', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['tags' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h']]);

    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $p->id])
        ->set('tagMassal', 'kesembilan')
        ->call('massalTagTambah');

    expect($p->refresh()->tagDaftar())->toHaveCount(8);
});

it('daftar bisa disaring menurut siapa yang terakhir mengubah', function () {
    $saya = adminArtikel();
    $orangLain = adminArtikel();

    $this->actingAs($saya);
    $punyaSaya = artikel(['title' => 'Diubah Saya']);
    $punyaSaya->update(['excerpt' => 'diubah']);

    $punyaDia = artikel(['title' => 'Diubah Dia']);
    $punyaDia->forceFill(['updated_by' => $orangLain->id])->saveQuietly();

    $t = Livewire::test(BlogList::class)->set('fPenyunting', (string) $orangLain->id);

    expect($t->viewData('posts')->pluck('title')->all())->toBe(['Diubah Dia'])
        ->and(collect($t->instance()->chipSaring())->pluck('nama'))->toContain('fPenyunting');

    expect($t->viewData('penyuntingDaftar')->pluck('id')->all())->toContain($orangLain->id);
});

// ===================== Ekspor Markdown & impor banyak =====================

it('satu artikel diunduh sebagai satu berkas markdown', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Naskah Tunggal', 'category' => 'Tips', 'tags' => ['satu']]);

    Livewire::test(BlogList::class)
        ->call('pilihHalaman', [(string) $p->id])
        ->call('unduhMarkdown')
        ->assertFileDownloaded($p->slug.'.md');
});

it('banyak artikel diunduh sebagai satu arsip', function () {
    $this->actingAs(adminArtikel());
    artikel();
    artikel();

    Livewire::test(BlogList::class)->call('unduhMarkdown')->assertFileDownloaded();
});

it('naskah markdown membawa front-matter dan isi yang terbaca', function () {
    $p = artikel([
        'title' => 'Judul Naskah',
        'category' => 'Tips',
        'tags' => ['satu', 'dua'],
        'body' => '<h2>Bagian</h2><p>Teks <strong>tebal</strong> dan <a href="https://contoh.id">tautan</a>.</p>',
    ]);

    $md = \App\Support\EksporMarkdown::naskah($p);

    expect($md)->toStartWith('---')
        ->toContain('title: "Judul Naskah"')
        ->toContain('tags: ["satu", "dua"]')
        ->toContain('## Bagian')
        ->toContain('**tebal**')
        ->toContain('[tautan](https://contoh.id)');
});

it('impor beberapa berkas sekaligus membuat draf untuk masing-masing', function () {
    $this->actingAs(adminArtikel());

    $berkas = [
        \Illuminate\Http\UploadedFile::fake()->createWithContent('satu.md', "# Artikel Satu\n\nIsi pertama.\n"),
        \Illuminate\Http\UploadedFile::fake()->createWithContent('dua.md', "# Artikel Dua\n\nIsi kedua.\n"),
    ];

    $t = Livewire::test(BlogList::class)->set('berkasImpor', $berkas);

    expect(BlogPost::where('status', 'draft')->count())->toBe(2);

    // Banyak berkas TIDAK membuka penyunting; cukup dipindahkan ke tab Draf.
    $t->assertSet('filter', 'draft')->assertNoRedirect();
});

// ===================== Biaya & kebersihan =====================

it('daftar artikel tidak menembak satu kueri baca per baris', function () {
    $this->actingAs(adminArtikel());

    // Artikel TANPA baris baca sama sekali — di sinilah withSum memberi NULL
    // dan versi lama jatuh ke kueri per baris.
    for ($i = 0; $i < 6; $i++) {
        artikel(['title' => 'Sepi '.$i, 'status' => 'published', 'published_at' => now()->subDays(200)]);
    }

    Livewire::test(BlogList::class); // panaskan singgahan ringkasan

    \Illuminate\Support\Facades\DB::enableQueryLog();
    Livewire::test(BlogList::class);
    $kueri = \Illuminate\Support\Facades\DB::getQueryLog();
    \Illuminate\Support\Facades\DB::disableQueryLog();

    $perBaris = collect($kueri)->filter(
        fn ($q) => str_contains($q['query'], 'blog_post_reads') && str_contains($q['query'], 'blog_post_id` = ?')
    );

    expect($perBaris)->toHaveCount(0)
        ->and(count($kueri))->toBeLessThan(15);
});

it('baca30 memakai angka yang sudah dimuat, termasuk saat nol', function () {
    $p = artikel();

    $dimuat = BlogPost::query()
        ->withSum(['bacaHarian as baca_30' => fn ($q) => $q->where('tanggal', '>=', '2000-01-01')], 'jumlah')
        ->find($p->id);

    \Illuminate\Support\Facades\DB::enableQueryLog();
    $hasil = $dimuat->baca30();
    $jumlahKueri = count(\Illuminate\Support\Facades\DB::getQueryLog());
    \Illuminate\Support\Facades\DB::disableQueryLog();

    expect($hasil)->toBe(0)->and($jumlahKueri)->toBe(0);
});

it('ringkasan daftar disegarkan begitu ada artikel berubah', function () {
    $this->actingAs(adminArtikel());
    artikel(['title' => 'Pertama']);

    expect(Livewire::test(BlogList::class)->viewData('tabCounts')['all'])->toBe(1);

    artikel(['title' => 'Kedua']);

    // Kalau singgahannya tidak ikut versi data, angka ini masih 1.
    expect(Livewire::test(BlogList::class)->viewData('tabCounts')['all'])->toBe(2);
});

it('gambar yang tidak dirujuk artikel mana pun dibersihkan', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    $disk->put('img/blog/dipakai-sampul.webp', 'x');
    $disk->put('img/blog/dipakai-isi.webp', 'x');
    $disk->put('img/blog/yatim.webp', 'x');
    $disk->put('img/blog/baru-diunggah.webp', 'x');

    // Hanya yang lebih tua dari tenggang yang boleh dihapus.
    foreach (['dipakai-sampul.webp', 'dipakai-isi.webp', 'yatim.webp'] as $nama) {
        touch($disk->path('img/blog/'.$nama), now()->subDays(30)->getTimestamp());
    }

    artikel([
        'cover' => 'dipakai-sampul.webp',
        'body' => '<p><img src="/storage/img/blog/dipakai-isi.webp" alt="a"></p>',
    ]);

    $this->artisan('artikel:bersihkan-gambar --hari=7')->assertSuccessful();

    $disk->assertExists('img/blog/dipakai-sampul.webp');
    $disk->assertExists('img/blog/dipakai-isi.webp');
    // Baru diunggah: artikelnya mungkin masih dibuka dan belum disimpan.
    $disk->assertExists('img/blog/baru-diunggah.webp');
    $disk->assertMissing('img/blog/yatim.webp');
});

it('gambar yang masih dipakai artikel di tong sampah tidak dihapus', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $disk->put('img/blog/di-sampah.webp', 'x');
    touch($disk->path('img/blog/di-sampah.webp'), now()->subDays(30)->getTimestamp());

    artikel(['cover' => 'di-sampah.webp'])->delete();

    $this->artisan('artikel:bersihkan-gambar --hari=7')->assertSuccessful();

    $disk->assertExists('img/blog/di-sampah.webp');
});

// ===================== Riwayat: mampat & pangkas =====================

it('isi revisi disimpan termampat tapi terbaca utuh', function () {
    $p = artikel();
    $naskah = '<p>'.str_repeat('kalimat yang berulang-ulang supaya layak dimampatkan. ', 80).'</p>';
    $p->update(['body' => $naskah]);
    $p->catatRevisi();

    $revisi = $p->revisi()->first();
    $mentah = \Illuminate\Support\Facades\DB::table('blog_post_revisions')->where('id', $revisi->id)->value('body');

    expect($revisi->body)->toBe($naskah)
        ->and($mentah)->toStartWith('gz:')
        ->and(strlen($mentah))->toBeLessThan(strlen($naskah));
});

it('revisi lama dipangkas tapi yang terbaru selalu disisakan', function () {
    $p = artikel();

    for ($i = 0; $i < 12; $i++) {
        $p->catatRevisi();
    }

    // Semuanya dituakan; tanpa penyisaan seluruh riwayat bisa habis.
    \App\Models\BlogPostRevision::query()->update(['created_at' => now()->subDays(400)]);

    $this->artisan('artikel:pangkas-revisi --hari=180 --sisakan=5')->assertSuccessful();

    expect($p->revisi()->count())->toBe(5);
});

it('revisi yatim ikut dibersihkan', function () {
    $p = artikel();
    $p->catatRevisi();
    $idRevisi = $p->revisi()->first()->id;

    \Illuminate\Support\Facades\DB::table('blog_posts')->where('id', $p->id)->delete();

    $this->artisan('artikel:pangkas-revisi')->assertSuccessful();

    expect(\App\Models\BlogPostRevision::find($idRevisi))->toBeNull();
});

// ===================== Ekspor–impor bolak-balik =====================

it('artikel yang diekspor ke markdown bisa diimpor kembali utuh', function () {
    $this->actingAs(adminArtikel());

    $asli = artikel([
        'title' => 'Panduan Lengkap Garansi',
        'category' => 'Tips',
        'tags' => ['garansi', 'akun'],
        'excerpt' => 'Ringkasan aslinya.',
        'cover_alt' => 'Ilustrasi garansi',
        'body' => '<h2>Bagian Satu</h2><p>Isi dengan <strong>tebal</strong> dan <a href="https://contoh.id">tautan</a>.</p><ul><li>butir satu</li><li>butir dua</li></ul>',
    ]);

    $md = \App\Support\EksporMarkdown::naskah($asli);
    $berkas = \Illuminate\Http\UploadedFile::fake()->createWithContent('hasil-ekspor.md', $md);

    Livewire::test(BlogList::class)->set('berkasImpor', [$berkas]);

    $salinan = BlogPost::where('id', '!=', $asli->id)->first();

    expect($salinan->title)->toBe('Panduan Lengkap Garansi')
        ->and($salinan->category)->toBe('Tips')
        ->and($salinan->tagDaftar())->toBe(['garansi', 'akun'])
        ->and($salinan->excerpt)->toBe('Ringkasan aslinya.')
        ->and($salinan->cover_alt)->toBe('Ilustrasi garansi')
        // Front-matter TIDAK boleh bocor jadi isi tulisan.
        ->and($salinan->body)->not->toContain('title:')
        ->and($salinan->body)->toContain('<h2>Bagian Satu</h2>')
        ->and($salinan->body)->toContain('<strong>tebal</strong>')
        ->and($salinan->body)->toContain('<li>butir satu</li>')
        // Status di berkas diabaikan; hasil impor selalu draf.
        ->and($salinan->status)->toBe('draft');
});

it('berkas markdown tanpa front-matter tetap terbaca seperti biasa', function () {
    $this->actingAs(adminArtikel());

    $berkas = \Illuminate\Http\UploadedFile::fake()->createWithContent(
        'biasa.md',
        "# Judul Dari Markdown\n\nParagraf biasa.\n"
    );

    Livewire::test(BlogList::class)->set('berkasImpor', [$berkas]);

    $p = BlogPost::first();
    expect($p->title)->toBe('Judul Dari Markdown')
        ->and($p->body)->toContain('Paragraf biasa.');
});

it('garis pemisah di awal naskah tidak disangka front-matter', function () {
    [$judul, $isi] = \App\Support\ImporArtikel::urai("---\n\nParagraf sesudah garis.\n", 'berkas-uji', 'md');

    expect($isi)->toContain('Paragraf sesudah garis.')
        ->and($judul)->toBe('Berkas Uji');
});

// ===================== Kategori lewat slug =====================

it('halaman blog menerima kategori sebagai slug maupun nama', function () {
    BlogCategory::create(['name' => 'Tips & Panduan', 'slug' => 'tips-panduan']);
    artikel(['title' => 'Artikel Kategori', 'category' => 'Tips & Panduan', 'status' => 'published', 'published_at' => now()->subDay()]);
    artikel(['title' => 'Artikel Lain', 'category' => 'Akun', 'status' => 'published', 'published_at' => now()->subDays(2)]);

    $lewatSlug = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class, ['category' => 'tips-panduan']);
    expect($lewatSlug->get('category'))->toBe('Tips & Panduan')
        ->and($lewatSlug->viewData('posts')->pluck('title')->all())->toBe(['Artikel Kategori']);

    $lewatNama = Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class, ['category' => 'Tips & Panduan']);
    expect($lewatNama->viewData('posts')->pluck('title')->all())->toBe(['Artikel Kategori']);
});

it('sitemap memakai slug kategori, bukan nama ber-spasi', function () {
    BlogCategory::create(['name' => 'Tips & Panduan', 'slug' => 'tips-panduan']);
    artikel(['category' => 'Tips & Panduan', 'status' => 'published', 'published_at' => now()->subDay()]);

    $isi = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($isi)->toContain('kategori=tips-panduan')
        ->not->toContain('Tips%20%26%20Panduan');
});

// ===================== Riwayat: lihat semua =====================

it('riwayat menampilkan sepuluh versi lalu bisa dibuka seluruhnya', function () {
    $this->actingAs(adminArtikel());
    $p = artikel();

    for ($i = 0; $i < 14; $i++) {
        $p->catatRevisi();
    }

    $t = Livewire::test(BlogForm::class, ['post' => $p]);

    expect($t->instance()->riwayat)->toHaveCount(10)
        ->and($t->instance()->jumlahRevisi)->toBe(14);

    $t->set('semuaRevisi', true);
    expect($t->instance()->riwayat)->toHaveCount(14);
});

// ===================== Keutuhan komponen =====================

/**
 * Komponen Livewire WAJIB menghasilkan satu elemen akar — juga pada
 * permintaan PEMBARUAN, bukan cuma saat halaman dimuat penuh.
 *
 * Pernah terjadi: partial gaya ber-@once disertakan dari dalam formulir.
 * Saat halaman dimuat penuh ia sudah dipakai induknya sehingga tidak keluar
 * apa-apa, tetapi begitu Livewire merender komponen itu SENDIRIAN, @once
 * menyala lagi dan <style> jadi elemen akar pertama. Livewire memorf elemen
 * gaya itu, dan seluruh formulir lenyap dari layar begitu satu huruf
 * diketik.
 */
function akarKomponen(string $html): array
{
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?><body>'.$html.'</body>', LIBXML_NOERROR);
    libxml_clear_errors();

    $akar = [];
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $simpul) {
        if ($simpul->nodeType === XML_ELEMENT_NODE) {
            $akar[] = $simpul->nodeName;
        }
    }

    return $akar;
}

it('formulir artikel tetap utuh setelah satu huruf diketik', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Judul Awal']);

    $html = Livewire::test(BlogForm::class, ['post' => $p])->set('title', 'Judul Awal Diketik')->html();

    expect(akarKomponen($html))->toBe(['form'])
        // Isinya benar-benar formulirnya, bukan sisa elemen lain.
        ->and($html)->toContain('bl-form');
});

it('semua komponen blog menghasilkan satu elemen akar saat diperbarui', function () {
    $this->actingAs(adminArtikel());
    $p = artikel(['title' => 'Untuk Diperiksa', 'status' => 'published', 'published_at' => now()->subDay()]);
    BlogCategory::create(['name' => 'Panduan', 'slug' => 'panduan']);

    $komponen = [
        'BlogList' => fn () => Livewire::test(BlogList::class)->set('search', 'a'),
        'BlogForm' => fn () => Livewire::test(BlogForm::class, ['post' => $p])->set('title', 'Diubah'),
        'BlogCreate' => fn () => Livewire::test(\App\Livewire\Pages\Admin\Blog\BlogCreate::class),
        'BlogEdit' => fn () => Livewire::test(BlogEdit::class, ['post' => $p]),
        'CategoryList' => fn () => Livewire::test(CategoryList::class)->set('search', 'a'),
        'BlogIndex' => fn () => Livewire::test(\App\Livewire\Pages\Public\Blog\BlogIndex::class)->set('search', 'a'),
        'BlogShow' => fn () => Livewire::test(\App\Livewire\Pages\Public\Blog\BlogShow::class, ['post' => $p]),
    ];

    foreach ($komponen as $nama => $jalankan) {
        expect(akarKomponen($jalankan()->html()))->toHaveCount(1, $nama.' punya lebih dari satu elemen akar');
    }
});

it('gaya artikel tetap sampai ke halaman sunting lewat induknya', function () {
    // Lewat HTTP, jadi perannya harus benar-benar bernama "admin":
    // grup rute admin dijaga middleware checkrole:admin,admin-mimin.
    $peran = \App\Models\Role::create(['name' => 'admin', 'description' => 'uji']);
    foreach (['view_blog', 'edit_blog'] as $nama) {
        $izin = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($izin->id);
    }
    $this->actingAs(\App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']));
    $p = artikel(['title' => 'Cek Gaya', 'status' => 'published', 'published_at' => now()->subDay()]);

    $halaman = $this->get('/admin/blog/'.$p->slug.'/edit')->assertOk()->getContent();

    // Formulir tidak lagi menyertakan gayanya sendiri; induknya yang membawa.
    expect($halaman)->toContain('.bl-form {')
        ->toContain('.bl-panel-judul')
        ->toContain('class="blog-editor');
});
