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
