<?php

use App\Livewire\Pages\Admin\Banners\BannersForm;
use App\Livewire\Pages\Admin\Banners\BannersList;
use App\Models\Banners;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Data Banner: kartu keadaan (sekaligus saringan), galeri, jendela detail,
 * saklar status, form dua kolom — dan celah izin di rute detail.
 */
function adminBanner(array $izin = ['view_banners', 'create_banners', 'edit_banners', 'delete_banners']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-bn-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function banner(array $isian = []): Banners
{
    return Banners::create(array_merge(['judul' => 'Banner '.Str::random(4), 'gambar' => 'x.webp', 'status' => 'active'], $isian));
}

it('kartu keadaan menghitung dan menyaring menurut keadaan NYATA di beranda', function () {
    $this->actingAs(adminBanner());
    banner(['judul' => 'Sedang Tayang']);
    banner(['judul' => 'Belum Mulai', 'mulai_tayang' => now()->addDays(2)]);
    banner(['judul' => 'Sudah Lewat', 'selesai_tayang' => now()->subDay()]);
    banner(['judul' => 'Dimatikan', 'status' => 'non-active']);

    $t = Livewire::test(BannersList::class);
    expect($t->viewData('hitung'))->toMatchArray(['semua' => 4, 'tayang' => 1, 'terjadwal' => 1, 'berakhir' => 1, 'nonaktif' => 1]);

    $t->set('keadaan', 'terjadwal')->assertSee('Belum Mulai')->assertDontSee('Sedang Tayang');
    $t->set('keadaan', 'berakhir')->assertSee('Sudah Lewat')->assertDontSee('Belum Mulai');
    $t->set('keadaan', 'ngawur')->assertSet('keadaan', '');
});

it('rute detail membuka jendela detail, BUKAN halaman ubah (celah izin lama)', function () {
    $b = banner(['judul' => 'Promo Rahasia']);
    $this->actingAs(adminBanner(['view_banners']));

    // Rute admin.Banners.show kini memuat BannersList (bukan BannersEdit).
    expect(\Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.Banners.show')->getActionName())
        ->toContain('BannersList');

    Livewire::test(BannersList::class, ['Banners' => $b->id])
        ->assertSet('lihatId', $b->id)
        ->assertSee('Promo Rahasia')
        ->assertSee('Keadaan di beranda')
        ->assertDontSee('Simpan Perubahan');

    // Form juga menolak sendiri bila dipanggil tanpa izin ubah.
    Livewire::test(BannersForm::class, ['banners' => $b])
        ->set('judul', 'Diubah diam-diam')
        ->call('save')
        ->assertForbidden();
    expect($b->fresh()->judul)->toBe('Promo Rahasia');
});

it('saklar status cepat mengubah Aktif/Non-aktif, dan butuh izin ubah', function () {
    $b = banner();

    $this->actingAs(adminBanner(['view_banners']));
    Livewire::test(BannersList::class)->call('alihStatus', $b->id)->assertForbidden();

    $this->actingAs(adminBanner());
    Livewire::test(BannersList::class)->call('alihStatus', $b->id);
    expect($b->fresh()->status)->toBe('non-active');
});

it('isi cepat jadwal mengisi tanggal mulai & selesai', function () {
    $this->actingAs(adminBanner());
    $this->travelTo(\Illuminate\Support\Carbon::parse('2026-09-19 10:00'));

    Livewire::test(BannersForm::class)
        ->assertSet('status', 'active')
        ->call('aturJadwal', '7hari')
        ->assertSet('mulai_tayang', '2026-09-19T10:00')
        ->assertSet('selesai_tayang', '2026-09-26T23:59')
        ->call('aturJadwal', 'akhirbulan')
        ->assertSet('selesai_tayang', '2026-09-30T23:59')
        ->call('aturJadwal', 'kosong')
        ->assertSet('mulai_tayang', '')
        ->assertSet('selesai_tayang', '');
});

it('tambah banner: gambar wajib, lalu tersimpan dan tampil di galeri', function () {
    Storage::fake('public');
    $this->actingAs(adminBanner());

    Livewire::test(BannersForm::class)->set('judul', 'Promo Baru')->call('save')->assertHasErrors('gambar');

    Livewire::test(BannersForm::class)
        ->set('judul', 'Promo Baru')
        ->set('gambar', UploadedFile::fake()->image('promo.png', 800, 800))
        ->call('save')
        ->assertRedirect(route('admin.Banners.index'));

    $b = Banners::where('judul', 'Promo Baru')->first();
    expect($b->status)->toBe('active');
    Storage::disk('public')->assertExists('img/banners/'.$b->gambar);
    Livewire::test(BannersList::class)->assertSee('Promo Baru')->assertSee('Tayang tanpa batas waktu');
});

it('form menjelaskan bahwa judul & deskripsi tampil di beranda', function () {
    $this->actingAs(adminBanner());
    Livewire::test(BannersForm::class)
        ->assertSee('tampil sebagai teks besar di beranda')
        ->assertDontSee('teks alternatif gambar dan untuk admin');
});

it('urutan slide: beranda mengikuti urutan, dan tombol geser menukar posisi', function () {
    $this->actingAs(adminBanner());
    $a = banner(['judul' => 'Slide A', 'urutan' => 1]);
    $b = banner(['judul' => 'Slide B', 'urutan' => 2]);
    $c = banner(['judul' => 'Slide C', 'urutan' => 3]);

    expect(Banners::tayang()->pluck('judul')->all())->toBe(['Slide A', 'Slide B', 'Slide C']);

    Livewire::test(BannersList::class)->assertSee('Slide 1')->call('geser', $c->id, 'naik');
    expect(Banners::tayang()->pluck('judul')->all())->toBe(['Slide A', 'Slide C', 'Slide B']);

    // Batas atas: yang pertama tidak bisa naik lagi.
    Livewire::test(BannersList::class)->call('geser', $a->id, 'naik');
    expect(Banners::tayang()->first()->judul)->toBe('Slide A');

    $this->actingAs(adminBanner(['view_banners']));
    Livewire::test(BannersList::class)->call('geser', $b->id, 'naik')->assertForbidden();
});

it('tautan tujuan: pilihan produk/member/lain tersimpan relatif; tautan berbahaya ditolak', function () {
    Storage::fake('public');
    $this->actingAs(adminBanner());
    $p = \App\Models\Product::create(['nama_akun' => 'Canva Pro']);
    $b = banner(['judul' => 'Promo Canva']);

    Livewire::test(BannersForm::class, ['banners' => $b])
        ->set('tautanJenis', 'produk')->set('tautanProduk', (string) $p->id)->call('save');
    expect($b->fresh()->tautan)->toBe('/shop/product/'.$p->id)
        ->and($b->fresh()->tautanTujuan())->toBe('/shop/product/'.$p->id);

    // Saat dibuka lagi, pilihannya terbaca kembali.
    Livewire::test(BannersForm::class, ['banners' => $b->fresh()])
        ->assertSet('tautanJenis', 'produk')->assertSet('tautanProduk', (string) $p->id)
        ->set('tautanJenis', 'member')->call('save');
    expect($b->fresh()->tautan)->toBe('/member');

    foreach (['javascript:alert(1)', '//situs-lain.com', 'http://tidak-aman.com'] as $jahat) {
        Livewire::test(BannersForm::class, ['banners' => $b->fresh()])
            ->set('tautanJenis', 'lain')->set('tautanLain', $jahat)->call('save')->assertHasErrors('tautanLain');
    }

    Livewire::test(BannersForm::class, ['banners' => $b->fresh()])->set('tautanJenis', '')->call('save');
    expect($b->fresh()->tautan)->toBeNull()->and($b->fresh()->tautanTujuan())->toBe(route('shop.index'));
});

it('pratinjau form menyorot judul sama persis dengan beranda', function () {
    $this->actingAs(adminBanner());

    Livewire::test(BannersForm::class)
        ->set('judul', 'Gabung Jadi Member, Gratis & Untung!')
        ->assertSeeHtml('Gabung Jadi Member, <span class="bn-aksen">Gratis &amp; Untung!</span>')
        ->assertSee('36/60');

    expect(Banners::judulBeraksen('Gabung Jadi Member, Gratis & Untung!'))
        ->toBe('Gabung Jadi Member, <span class="ph-aksen">Gratis &amp; Untung!</span>');
});

it('beranda memakai tautan tujuan banner dan urutan slide', function () {
    $p = \App\Models\Product::create(['nama_akun' => 'Canva Pro']);
    banner(['judul' => 'Kedua Tampil', 'urutan' => 2]);
    banner(['judul' => 'Pertama Tampil', 'urutan' => 1, 'tautan' => '/shop/product/'.$p->id]);

    $html = view('livewire.pages.public.homepage.partials.banner', ['banners' => Banners::tayang()->get()])->render();
    expect(strpos($html, 'Pertama Tampil'))->toBeLessThan(strpos($html, 'Kedua Tampil'))
        ->and($html)->toContain('href="/shop/product/'.$p->id.'"');
});
