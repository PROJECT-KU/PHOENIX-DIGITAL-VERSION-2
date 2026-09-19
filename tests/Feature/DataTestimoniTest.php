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
    // created_at tidak fillable, jadi harus ditulis terpisah — kalau lewat
    // create() nilainya diam-diam diabaikan dan ujinya jadi menyesatkan.
    $dibuat = $isian['created_at'] ?? null;
    unset($isian['created_at']);

    $t = Testimoni::create(array_merge([
        'nama' => 'Pengirim '.Str::random(4),
        'pesan' => 'Pelayanannya cepat dan ramah.',
        'rating' => 5,
        'status' => 'pending',
    ], $isian));

    if ($dibuat) {
        $t->forceFill(['created_at' => $dibuat])->saveQuietly();
        $t->refresh();
    }

    return $t;
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

// ===================== Kendali tampil di beranda =====================

it('beranda hanya memuat testimoni bintang tinggi atau yang disorot', function () {
    $bagus = testimoni(['nama' => 'Bintang Lima', 'rating' => 5, 'status' => 'active']);
    $buruk = testimoni(['nama' => 'Bintang Dua', 'rating' => 2, 'status' => 'active']);
    $menunggu = testimoni(['nama' => 'Belum Ditinjau', 'rating' => 5, 'status' => 'pending']);

    $tampil = Testimoni::tampilPublik()->pluck('id');
    expect($tampil)->toContain($bagus->id)
        ->not->toContain($buruk->id)
        ->not->toContain($menunggu->id);

    // Disorot: tetap tampil walau bintangnya di bawah ambang.
    $buruk->update(['sorot' => true]);
    expect(Testimoni::tampilPublik()->pluck('id'))->toContain($buruk->id);
});

it('yang disorot naik ke urutan paling depan beranda', function () {
    $lama = testimoni(['nama' => 'Kiriman Lama', 'status' => 'active', 'created_at' => now()->subMonth()]);
    $baru = testimoni(['nama' => 'Kiriman Baru', 'status' => 'active']);

    // Tanpa sorot: yang terbaru duluan.
    expect(Testimoni::tampilPublik()->urutTampil()->first()->id)->toBe($baru->id);

    $lama->update(['sorot' => true]);
    expect(Testimoni::tampilPublik()->urutTampil()->first()->id)->toBe($lama->id);
});

it('komponen beranda memakai urutan sorot, bukan sekadar terbaru', function () {
    $lama = testimoni(['nama' => 'Disorot Lama', 'status' => 'active', 'sorot' => true, 'created_at' => now()->subYear()]);
    testimoni(['nama' => 'Biasa Baru', 'status' => 'active']);

    $tampil = Livewire::test(\App\Livewire\Components\Testimonials::class)->viewData('testimonials');
    expect($tampil->first()->id)->toBe($lama->id)
        ->and($tampil->count())->toBeLessThanOrEqual(Testimoni::BERANDA_MAKS);
});

it('sorot bisa dialihkan dari daftar dan butuh izin ubah', function () {
    $t = testimoni(['status' => 'active']);

    $this->actingAs(adminTestimoni(['view_testimoni']));
    Livewire::test(TestimoniList::class)->call('alihSorot', $t->id);
    expect($t->fresh()->sorot)->toBeFalse();

    $this->actingAs(adminTestimoni());
    Livewire::test(TestimoniList::class)->call('alihSorot', $t->id);
    expect($t->fresh()->sorot)->toBeTrue();
});

it('geser menukar urutan tampil dan menormalkan nilai kembar', function () {
    $this->actingAs(adminTestimoni());
    $a = testimoni(['nama' => 'Satu', 'status' => 'active', 'created_at' => now()]);
    $b = testimoni(['nama' => 'Dua', 'status' => 'active', 'created_at' => now()->subHour()]);

    // Semula urutan = 0 semua (data lama), jadi pengurutannya jatuh ke created_at.
    expect(Testimoni::tampilPublik()->urutTampil()->pluck('id')->all())->toBe([$a->id, $b->id]);

    Livewire::test(TestimoniList::class)->call('geser', $b->id, 'naik');
    expect(Testimoni::tampilPublik()->urutTampil()->pluck('id')->all())->toBe([$b->id, $a->id])
        ->and($b->fresh()->urutan)->toBe(1);
});

// ===================== Moderasi =====================

it('menyetujui & menolak mencatat siapa yang meninjau dan kapan', function () {
    $admin = adminTestimoni();
    $this->actingAs($admin);
    $t = testimoni();

    Livewire::test(TestimoniList::class)->call('approve', $t->id);
    expect($t->fresh()->ditinjau_oleh)->toEqual($admin->id)
        ->and($t->fresh()->ditinjau_at)->not->toBeNull();
});

it('penolakan menyimpan alasannya dan melepas sorotan', function () {
    $this->actingAs(adminTestimoni());
    $t = testimoni(['status' => 'active', 'sorot' => true]);

    Livewire::test(TestimoniList::class)
        ->call('bukaTolak', $t->id)
        ->assertSet('tolakId', $t->id)
        ->set('tolakAlasan', 'Berisi promosi/tautan')
        ->call('reject')
        ->assertSet('tolakId', null);

    $t->refresh();
    expect($t->status)->toBe('non-active')
        ->and($t->alasan_tolak)->toBe('Berisi promosi/tautan')
        ->and($t->sorot)->toBeFalse();

    // Disetujui kembali: alasan lama dibersihkan agar tidak menyesatkan.
    Livewire::test(TestimoniList::class)->call('approve', $t->id);
    expect($t->fresh()->alasan_tolak)->toBeNull();
});

it('aksi massal menyetujui & menolak semua yang dicentang', function () {
    $this->actingAs(adminTestimoni());
    $a = testimoni();
    $b = testimoni();

    Livewire::test(TestimoniList::class)
        ->set('pilih', [$a->id, $b->id])
        ->call('setujuiTerpilih')
        ->assertSet('pilih', []);
    expect([$a->fresh()->status, $b->fresh()->status])->toBe(['active', 'active']);

    Livewire::test(TestimoniList::class)
        ->set('pilih', [$a->id, $b->id])
        ->call('bukaTolak', 'massal')
        ->assertSet('tolakId', 'massal')
        ->set('tolakAlasan', 'Kiriman ganda')
        ->call('tolakTerpilih');
    expect($a->fresh()->status)->toBe('non-active')
        ->and($b->fresh()->alasan_tolak)->toBe('Kiriman ganda');
});

it('aksi massal menolak pengguna tanpa izin ubah', function () {
    $t = testimoni();
    $this->actingAs(adminTestimoni(['view_testimoni']));

    Livewire::test(TestimoniList::class)->set('pilih', [$t->id])->call('setujuiTerpilih');
    expect($t->fresh()->status)->toBe('pending');
});

it('pilih semua di halaman mencentang lalu melepas kembali', function () {
    $this->actingAs(adminTestimoni());
    $a = testimoni();
    $b = testimoni();

    Livewire::test(TestimoniList::class)
        ->call('pilihHalaman', [$a->id, $b->id])->assertSet('pilih', [$a->id, $b->id])
        ->call('pilihHalaman', [$a->id, $b->id])->assertSet('pilih', []);
});

// ===================== Saringan, urutan, arsip =====================

it('saringan lanjutan menyaring bintang, sumber, keterkaitan pelanggan, dan anonim', function () {
    $this->actingAs(adminTestimoni());
    // Pelanggan dibuat sendiri: DB uji kosong, dan Customer::first() yang null
    // membuat baris "tertaut pelanggan" diam-diam jadi tidak tertaut.
    $pelanggan = \App\Models\Customer::create(['nama' => 'Pelanggan Uji', 'no_hp' => '081255556666']);
    testimoni(['nama' => 'Lima Pelanggan', 'rating' => 5, 'status' => 'active', 'source' => 'customer', 'customer_id' => $pelanggan?->id, 'anonim' => true]);
    testimoni(['nama' => 'Tiga Admin', 'rating' => 3, 'status' => 'active', 'source' => 'admin']);

    $t = Livewire::test(TestimoniList::class)->call('setFilter', 'all');
    $t->set('fRating', '3')->assertSee('Tiga Admin')->assertDontSee('Lima Pelanggan');
    $t->set('fRating', '')->set('fSumber', 'admin')->assertSee('Tiga Admin')->assertDontSee('Lima Pelanggan');
    $t->set('fSumber', '')->set('fVerifikasi', 'tidak')->assertSee('Tiga Admin')->assertDontSee('Lima Pelanggan');
    $t->set('fVerifikasi', '')->set('fAnonim', 'ya')->assertSee('Lima Pelanggan')->assertDontSee('Tiga Admin');

    $t->call('resetSaring')->assertSet('fAnonim', '')->assertSet('searchTestimoni', '');
});

it('saringan tanggal memakai rentang kirim', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Kiriman Kemarin', 'created_at' => now()->subDay()]);
    testimoni(['nama' => 'Kiriman Hari Ini']);

    Livewire::test(TestimoniList::class)
        ->set('fDari', now()->toDateString())
        ->assertSee('Kiriman Hari Ini')
        ->assertDontSee('Kiriman Kemarin');
});

it('pengurutan bintang tertinggi & terendah bekerja', function () {
    $this->actingAs(adminTestimoni());
    $rendah = testimoni(['rating' => 2]);
    $tinggi = testimoni(['rating' => 5]);

    $t = Livewire::test(TestimoniList::class);
    expect($t->set('urut', 'tinggi')->viewData('Testimoni')->first()->id)->toBe($tinggi->id);
    expect($t->set('urut', 'rendah')->viewData('Testimoni')->first()->id)->toBe($rendah->id);
    $t->set('urut', 'ngawur')->assertSet('urut', 'baru');
});

it('hapus mengarsipkan dulu, bisa dipulihkan, lalu dibuang permanen', function () {
    $this->actingAs(adminTestimoni());
    $t = testimoni(['nama' => 'Mau Diarsip']);

    Livewire::test(TestimoniList::class)->call('deleteTestimoni', $t->id);
    expect(Testimoni::find($t->id))->toBeNull()
        ->and(Testimoni::onlyTrashed()->whereKey($t->id)->exists())->toBeTrue();

    // Arsip mengabaikan tab status — kalau tidak, isinya tampak kosong.
    Livewire::test(TestimoniList::class)->set('arsip', true)->assertSee('Mau Diarsip');

    Livewire::test(TestimoniList::class)->call('pulihkan', $t->id);
    expect(Testimoni::find($t->id))->not->toBeNull();

    Livewire::test(TestimoniList::class)->call('deleteTestimoni', $t->id);
    Livewire::test(TestimoniList::class)->call('buangPermanen', $t->id);
    expect(Testimoni::withTrashed()->whereKey($t->id)->exists())->toBeFalse();
});

it('memilih tab status selalu keluar dari arsip', function () {
    $this->actingAs(adminTestimoni());

    Livewire::test(TestimoniList::class)
        ->set('arsip', true)
        ->call('setFilter', 'active')
        ->assertSet('arsip', false);
});

// ===================== Penanda & bantuan moderasi =====================

it('kecurigaan menandai tautan, pesan kembar, dan nomor berulang', function () {
    $tautan = testimoni(['pesan' => 'Kunjungi promo-murah.com sekarang juga ya kak']);
    expect($tautan->kecurigaan())->toContain('Mengandung tautan');

    testimoni(['pesan' => 'Pesan yang sama persis.', 'no_hp' => '081200001111']);
    $kembar = testimoni(['pesan' => 'Pesan yang sama persis.', 'no_hp' => '081200001111']);
    expect($kembar->kecurigaan())->toContain('Isi kembar')->toContain('Nomor pernah mengirim');

    $wajar = testimoni(['pesan' => 'Pelayanannya cepat dan admin ramah sekali.', 'no_hp' => '081277778888']);
    expect($wajar->kecurigaan())->toBe([]);
});

it('menghitung umur antrean hanya untuk yang masih menunggu', function () {
    expect(testimoni(['created_at' => now()->subDays(3)])->menungguHari())->toBe(3)
        ->and(testimoni(['status' => 'active', 'created_at' => now()->subDays(3)])->menungguHari())->toBe(0);
});

it('tautan WhatsApp dinormalkan ke awalan 62', function () {
    expect(testimoni(['no_hp' => '081234567890'])->tautanWa())->toBe('https://wa.me/6281234567890')
        ->and(testimoni(['no_hp' => null])->tautanWa())->toBeNull();
});

it('jendela detail bisa pindah ke tetangga tanpa ditutup', function () {
    $this->actingAs(adminTestimoni());
    $baru = testimoni(['nama' => 'Kiriman Baru']);
    $lama = testimoni(['nama' => 'Kiriman Lama', 'created_at' => now()->subDay()]);

    Livewire::test(TestimoniList::class)
        ->call('lihat', $baru->id)
        ->call('detailTetangga', 1)->assertSet('lihatId', $lama->id)
        ->call('detailTetangga', -1)->assertSet('lihatId', $baru->id)
        // Di ujung daftar, jendela tetap terbuka pada testimoni yang sama.
        ->call('detailTetangga', -1)->assertSet('lihatId', $baru->id);
});

it('ekspor memakai saringan yang sedang aktif', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-19 10:00:00');
    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Ikut Ekspor', 'status' => 'active']);
    testimoni(['nama' => 'Tidak Ikut', 'status' => 'pending']);

    Livewire::test(TestimoniList::class)->call('setFilter', 'active')->call('unduhExcel');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('testimoni-20260919-100000.xlsx', function (\App\Exports\TestimoniExport $ekspor) {
        $nama = $ekspor->view()->getData()['testimoni']->pluck('nama');

        return $nama->contains('Ikut Ekspor') && ! $nama->contains('Tidak Ikut');
    });

    \Illuminate\Support\Carbon::setTestNow();
});

it('form menyimpan sorot hanya untuk testimoni yang disetujui', function () {
    $this->actingAs(adminTestimoni());
    $t = testimoni(['status' => 'active']);

    Livewire::test(TestimoniForm::class, ['testimoni' => $t])
        ->set('sorot', true)->call('save');
    expect($t->fresh()->sorot)->toBeTrue();

    // Ditolak tidak boleh tetap tersorot — beranda akan menampilkannya lagi.
    Livewire::test(TestimoniForm::class, ['testimoni' => $t->fresh()])
        ->set('status', 'non-active')->set('sorot', true)->call('save');
    expect($t->fresh()->sorot)->toBeFalse();
});

// ===================== Jumlah kartu beranda =====================

it('jumlah kartu beranda mengikuti setelan, bukan angka mati', function () {
    foreach (range(1, 12) as $i) {
        testimoni(['nama' => 'Tayang '.$i, 'status' => 'active']);
    }

    expect(Testimoni::jumlahBeranda())->toBe(Testimoni::BERANDA_MAKS)
        ->and(Livewire::test(\App\Livewire\Components\Testimonials::class)->viewData('testimonials'))->toHaveCount(9);

    \App\Models\Setting::set(Testimoni::SETELAN_BERANDA, 3);
    expect(Testimoni::jumlahBeranda())->toBe(3)
        ->and(Livewire::test(\App\Livewire\Components\Testimonials::class)->viewData('testimonials'))->toHaveCount(3);

    // Dibatasi 3..24 supaya slider beranda tetap masuk akal.
    \App\Models\Setting::set(Testimoni::SETELAN_BERANDA, 99);
    expect(Testimoni::jumlahBeranda())->toBe(24);
});

it('admin mengubah jumlah kartu beranda dan butuh izin ubah', function () {
    $this->actingAs(adminTestimoni(['view_testimoni']));
    Livewire::test(TestimoniList::class)->set('jumlahBeranda', 6)->assertForbidden();

    $this->actingAs(adminTestimoni());
    Livewire::test(TestimoniList::class)->set('jumlahBeranda', 6);
    expect(Testimoni::jumlahBeranda())->toBe(6);
});

// ===================== Halaman publik =====================

it('halaman semua testimoni memakai saringan yang sama dengan beranda', function () {
    $tampil = testimoni(['nama' => 'Tampil Publik', 'status' => 'active', 'rating' => 5]);
    testimoni(['nama' => 'Masih Menunggu', 'status' => 'pending']);
    testimoni(['nama' => 'Bintang Rendah', 'status' => 'active', 'rating' => 2]);

    Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class)
        ->assertSee('Tampil Publik')
        ->assertDontSee('Masih Menunggu')
        ->assertDontSee('Bintang Rendah');

    expect(Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class)->viewData('testimoni')->pluck('id'))
        ->toContain($tampil->id);
});

it('halaman publik menyamarkan nama pengirim anonim', function () {
    testimoni(['nama' => 'Rahasia Sekali', 'anonim' => true, 'status' => 'active']);

    Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class)
        ->assertSee('R•••')
        ->assertDontSee('Rahasia Sekali');
});

it('saringan bintang di halaman publik menolak nilai ngawur', function () {
    testimoni(['nama' => 'Lima Bintang', 'status' => 'active', 'rating' => 5]);
    testimoni(['nama' => 'Empat Bintang', 'status' => 'active', 'rating' => 4]);

    $t = Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class);
    $t->call('setBintang', '4')->assertSee('Empat Bintang')->assertDontSee('Lima Bintang');
    $t->call('setBintang', 'ngawur')->assertSet('bintang', '')->assertSee('Lima Bintang');
});

it('pelanggan bisa melampirkan foto saat mengirim testimoni', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    Livewire::test(\App\Livewire\Components\Testimonials::class)
        ->set('nama', 'Pengirim Berfoto')
        ->set('no_hp', '081299998888')
        ->set('pesan', 'Pelayanannya cepat sekali, terima kasih banyak.')
        ->set('foto', \Illuminate\Http\UploadedFile::fake()->image('wajah.jpg', 600, 600))
        ->call('submit')
        ->assertSet('submitted', true);

    $kiriman = Testimoni::where('nama', 'Pengirim Berfoto')->first();
    expect($kiriman)->not->toBeNull()
        ->and($kiriman->foto)->not->toBeNull()
        ->and($kiriman->status)->toBe('pending');
});

it('kiriman pelanggan tanpa foto tetap diterima', function () {
    Livewire::test(\App\Livewire\Components\Testimonials::class)
        ->set('nama', 'Tanpa Foto')
        ->set('no_hp', '081277776666')
        ->set('pesan', 'Sudah dua kali beli di sini, aman semua.')
        ->call('submit')
        ->assertSet('submitted', true);

    expect(Testimoni::where('nama', 'Tanpa Foto')->value('foto'))->toBeNull();
});

// ===================== Saringan & urutan tambahan =====================

it('pencarian menjangkau nomor WhatsApp apa pun formatnya', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Pemilik Nomor', 'no_hp' => '0895'.'11223344']);
    testimoni(['nama' => 'Orang Lain', 'no_hp' => '081200000000']);

    $t = Livewire::test(TestimoniList::class);
    $t->set('searchTestimoni', '089511223344')->assertSee('Pemilik Nomor')->assertDontSee('Orang Lain');
    // Format lain dari nomor yang sama tetap ketemu.
    $t->set('searchTestimoni', '+6289511223344')->assertSee('Pemilik Nomor')->assertDontSee('Orang Lain');
});

it('menyaring menurut sorot dan tampil di beranda', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Yang Disorot', 'status' => 'active', 'sorot' => true, 'rating' => 2]);
    testimoni(['nama' => 'Bintang Rendah', 'status' => 'active', 'rating' => 2]);

    $t = Livewire::test(TestimoniList::class)->call('setFilter', 'all');
    $t->set('fSorot', 'ya')->assertSee('Yang Disorot')->assertDontSee('Bintang Rendah');
    $t->set('fSorot', '')->set('fBeranda', 'ya')->assertSee('Yang Disorot')->assertDontSee('Bintang Rendah');
    $t->set('fBeranda', 'tidak')->assertSee('Bintang Rendah')->assertDontSee('Yang Disorot');
});

it('urutan paling lama menunggu menaikkan kiriman yang belum ditinjau', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Sudah Disetujui', 'status' => 'active', 'sorot' => true]);
    $menunggu = testimoni(['nama' => 'Masih Menunggu', 'created_at' => now()->subWeek()]);

    expect(Livewire::test(TestimoniList::class)->call('setFilter', 'all')->set('urut', 'tunggu')
        ->viewData('Testimoni')->first()->id)->toBe($menunggu->id);
});

it('chip saringan menampilkan saringan aktif dan bisa dilepas satu-satu', function () {
    $this->actingAs(adminTestimoni());

    $t = Livewire::test(TestimoniList::class)->set('fRating', '5')->set('fAnonim', 'ya');
    expect(collect($t->instance()->chipSaring)->pluck('nama')->all())->toBe(['fRating', 'fAnonim']);

    $t->call('lepasSaring', 'fRating')->assertSet('fRating', '')->assertSet('fAnonim', 'ya');
    // Nama properti yang tidak dikenal diabaikan, bukan menimpa apa pun.
    $t->call('lepasSaring', 'perHalaman')->assertSet('perHalaman', 12);
});

// ===================== Aksi massal tambahan =====================

it('sorot massal hanya mengenai yang sudah disetujui', function () {
    $this->actingAs(adminTestimoni());
    $aktif = testimoni(['status' => 'active']);
    $menunggu = testimoni(['status' => 'pending']);

    Livewire::test(TestimoniList::class)->set('pilih', [$aktif->id, $menunggu->id])->call('sorotTerpilih', true);
    expect($aktif->fresh()->sorot)->toBeTrue()
        ->and($menunggu->fresh()->sorot)->toBeFalse();

    Livewire::test(TestimoniList::class)->set('pilih', [$aktif->id])->call('sorotTerpilih', false);
    expect($aktif->fresh()->sorot)->toBeFalse();
});

it('arsip massal, pulihkan massal, dan buang massal bekerja berurutan', function () {
    $this->actingAs(adminTestimoni());
    $a = testimoni();
    $b = testimoni();

    Livewire::test(TestimoniList::class)->set('pilih', [$a->id, $b->id])->call('arsipkanTerpilih')->assertSet('pilih', []);
    expect(Testimoni::onlyTrashed()->count())->toBe(2);

    Livewire::test(TestimoniList::class)->set('pilih', [$a->id])->call('pulihkanTerpilih');
    expect(Testimoni::find($a->id))->not->toBeNull();

    Livewire::test(TestimoniList::class)->set('pilih', [$b->id])->call('buangTerpilih');
    expect(Testimoni::withTrashed()->whereKey($b->id)->exists())->toBeFalse();
});

it('aksi massal arsip menolak pengguna tanpa izin hapus', function () {
    $t = testimoni();
    $this->actingAs(adminTestimoni(['view_testimoni', 'edit_testimoni']));

    Livewire::test(TestimoniList::class)->set('pilih', [$t->id])->call('arsipkanTerpilih');
    expect(Testimoni::find($t->id))->not->toBeNull();
});

// ===================== Jejak moderasi =====================

it('setiap keputusan moderasi meninggalkan jejak', function () {
    $admin = adminTestimoni();
    $this->actingAs($admin);
    $t = testimoni();

    Livewire::test(TestimoniList::class)->call('approve', $t->id);
    Livewire::test(TestimoniList::class)->call('alihSorot', $t->id);
    Livewire::test(TestimoniList::class)->call('bukaTolak', $t->id)->set('tolakAlasan', 'Kiriman ganda')->call('reject');
    Livewire::test(TestimoniList::class)->call('deleteTestimoni', $t->id);

    $jejak = \App\Models\TestimoniRiwayat::where('testimoni_id', $t->id)->orderBy('id')->get();
    expect($jejak->pluck('aksi')->all())->toBe(['disetujui', 'disorot', 'ditolak', 'diarsipkan'])
        ->and($jejak->firstWhere('aksi', 'ditolak')->keterangan)->toBe('Kiriman ganda')
        ->and($jejak->first()->user_id)->toEqual($admin->id);
});

it('jejak moderasi tampil di jendela detail', function () {
    $this->actingAs(adminTestimoni());
    $t = testimoni();
    Livewire::test(TestimoniList::class)->call('approve', $t->id);

    Livewire::test(TestimoniList::class)->call('lihat', $t->id)->assertSee('Jejak moderasi');
});

// ===================== Ekspor PDF & pembersih arsip =====================

it('ekspor PDF memakai saringan yang aktif dan butuh izin lihat', function () {
    $this->actingAs(adminTestimoni());
    testimoni(['nama' => 'Ikut PDF', 'status' => 'active']);

    Livewire::test(TestimoniList::class)
        ->call('setFilter', 'active')
        ->call('unduhPdf')
        ->assertFileDownloaded();

    // Tanpa izin lihat, unduhan ditolak.
    $this->actingAs(\App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::create(['name' => 'uji-tm-nihil-'.Str::random(4), 'description' => 'uji'])->id,
        'status' => 'active',
    ]));
    Livewire::test(TestimoniList::class)->call('unduhPdf')->assertForbidden();
});

it('pembersih arsip hanya membuang yang sudah lama, dan --kering tidak menghapus', function () {
    $lama = testimoni(['nama' => 'Arsip Lama']);
    $baru = testimoni(['nama' => 'Arsip Baru']);
    $lama->delete();
    $baru->delete();
    // deleted_at tidak fillable — dimundurkan langsung supaya dianggap lama.
    Testimoni::withTrashed()->whereKey($lama->id)->update(['deleted_at' => now()->subDays(120)]);

    $this->artisan('testimoni:bersihkan-arsip', ['--hari' => 90, '--kering' => true])->assertSuccessful();
    expect(Testimoni::onlyTrashed()->count())->toBe(2);

    $this->artisan('testimoni:bersihkan-arsip', ['--hari' => 90])->assertSuccessful();
    expect(Testimoni::withTrashed()->whereKey($lama->id)->exists())->toBeFalse()
        ->and(Testimoni::onlyTrashed()->whereKey($baru->id)->exists())->toBeTrue();
});

it('pembersih arsip tidak pernah menyebut nama pengirim di keluarannya', function () {
    $t = testimoni(['nama' => 'Nama Sangat Pribadi']);
    $t->delete();
    Testimoni::withTrashed()->whereKey($t->id)->update(['deleted_at' => now()->subDays(200)]);

    $this->artisan('testimoni:bersihkan-arsip', ['--kering' => true])
        ->doesntExpectOutputToContain('Nama Sangat Pribadi')
        ->assertSuccessful();
});

it('kotak "ditinjau admin" jujur untuk data lama yang tanpa catatan', function () {
    $this->actingAs(adminTestimoni());

    // Disetujui sebelum jejak moderasi ada: ditinjau_at masih kosong.
    $lama = testimoni(['nama' => 'Data Lama', 'status' => 'active']);
    Livewire::test(TestimoniList::class)->call('lihat', $lama->id)
        ->assertSee('Tidak tercatat')
        ->assertSee('Diputuskan sebelum jejak moderasi dicatat.')
        ->assertDontSee('Belum ditinjau');

    // Masih menunggu: "Belum ditinjau" memang benar.
    $menunggu = testimoni(['nama' => 'Masih Antre']);
    Livewire::test(TestimoniList::class)->call('lihat', $menunggu->id)
        ->assertSee('Belum ditinjau')
        ->assertDontSee('Tidak tercatat');

    // Sudah ditinjau: tanggal & nama peninjaunya yang tampil.
    Livewire::test(TestimoniList::class)->call('approve', $menunggu->id);
    Livewire::test(TestimoniList::class)->call('lihat', $menunggu->id)
        ->assertSee(auth()->user()->name)
        ->assertDontSee('Belum ditinjau');
});

// ===================== Penemuan halaman publik & SEO =====================

it('halaman testimoni terdaftar di sitemap dan footer', function () {
    $peta = $this->get('/sitemap.xml');
    $peta->assertOk();
    expect($peta->getContent())->toContain(route('testimoni.semua'));

    // Footer memakai layout guest; beranda cukup untuk membuktikan tautannya ada.
    expect(file_get_contents(resource_path('views/layouts/guest.blade.php')))
        ->toContain("route('testimoni.semua')");
});

it('halaman publik mengirim rata-rata bintang sebagai data terstruktur', function () {
    testimoni(['nama' => 'Bintang Lima', 'status' => 'active', 'rating' => 5]);
    testimoni(['nama' => 'Bintang Empat', 'status' => 'active', 'rating' => 4]);

    Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class);

    $jsonLd = json_decode(view()->shared('seoJsonLd'), true);
    expect($jsonLd['@type'])->toBe('Store')
        ->and($jsonLd['aggregateRating']['ratingValue'])->toBe(4.5)
        ->and($jsonLd['aggregateRating']['reviewCount'])->toBe(2)
        ->and($jsonLd['review'])->toHaveCount(2);
});

it('data terstruktur memakai nama publik, bukan nama asli pengirim anonim', function () {
    testimoni(['nama' => 'Nama Asli Rahasia', 'anonim' => true, 'status' => 'active']);

    Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class);

    expect(view()->shared('seoJsonLd'))
        ->toContain('N•••')
        ->not->toContain('Nama Asli Rahasia');
});

it('tanpa testimoni yang tampil, data terstruktur tidak dikirim', function () {
    testimoni(['status' => 'pending']);

    Livewire::test(\App\Livewire\Pages\Public\Testimoni\SemuaTestimoni::class);

    expect(view()->shared('seoJsonLd'))->toBeNull();
});

it('komponen testimoni bisa dipakai tanpa slider, hanya ajakan menulis', function () {
    testimoni(['nama' => 'Ada di Slider', 'status' => 'active']);

    Livewire::test(\App\Livewire\Components\Testimonials::class, ['tampilkanDaftar' => false])
        ->assertSee('Tulis Testimoni')
        ->assertDontSee('Ada di Slider');

    Livewire::test(\App\Livewire\Components\Testimonials::class)->assertSee('Ada di Slider');
});

it('perangkap bot membuang kiriman tanpa memberi tahu botnya', function () {
    Livewire::test(\App\Livewire\Components\Testimonials::class)
        ->set('nama', 'Bot Spam')
        ->set('no_hp', '081200001111')
        ->set('pesan', 'Kunjungi situs promo saya sekarang juga ya.')
        ->set('situs', 'https://situs-bot.example')
        ->call('submit')
        // Bot melihat "berhasil", padahal tidak ada yang tersimpan.
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    expect(Testimoni::where('nama', 'Bot Spam')->exists())->toBeFalse();
});

// ===================== Ekspor terpilih & produk yang dibeli =====================

it('ekspor memakai yang dicentang bila ada centangan', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-19 11:00:00');
    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->actingAs(adminTestimoni());
    $dipilih = testimoni(['nama' => 'Dicentang', 'status' => 'pending']);
    testimoni(['nama' => 'Tidak Dicentang', 'status' => 'pending']);

    Livewire::test(TestimoniList::class)->set('pilih', [$dipilih->id])->call('unduhExcel');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('testimoni-20260919-110000.xlsx', function (\App\Exports\TestimoniExport $ekspor) {
        $nama = $ekspor->view()->getData()['testimoni']->pluck('nama');

        return $nama->contains('Dicentang') && ! $nama->contains('Tidak Dicentang');
    });

    \Illuminate\Support\Carbon::setTestNow();
});

it('jendela detail menampilkan produk yang pernah dibeli pengirim', function () {
    $this->actingAs(adminTestimoni());

    $pelanggan = \App\Models\Customer::create(['nama' => 'Pembeli Setia', 'no_hp' => '081233334444']);
    $order = \App\Models\Order::create([
        'id' => Str::uuid(), 'order_number' => 'INV-TESTI-1', 'subtotal' => 1, 'total' => 1, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now(),
        'customer_id' => $pelanggan->id,
    ]);
    \App\Models\OrderItem::create([
        'order_id' => $order->id,
        'product_id' => \App\Models\Product::create(['nama_akun' => 'Grammarly'])->id,
        'product_name' => 'Grammarly Premium',
        'duration_type' => 'bulan', 'duration_value' => 1,
        'quantity' => 1, 'price' => 50000, 'subtotal' => 50000,
    ]);

    $t = testimoni(['customer_id' => $pelanggan->id]);

    Livewire::test(TestimoniList::class)->call('lihat', $t->id)
        ->assertSee('Pernah membeli')
        ->assertSee('Grammarly Premium');
});

// ===================== Aktivitas & penanda data lama =====================

it('jendela aktivitas memuat jejak dari semua testimoni', function () {
    $this->actingAs(adminTestimoni());
    $a = testimoni(['nama' => 'Kiriman Satu']);
    $b = testimoni(['nama' => 'Kiriman Dua']);
    Livewire::test(TestimoniList::class)->call('approve', $a->id);
    Livewire::test(TestimoniList::class)->call('approve', $b->id);

    Livewire::test(TestimoniList::class)->set('lihatAktivitas', true)
        ->assertSee('Aktivitas moderasi')
        ->assertSee('Kiriman Satu')
        ->assertSee('Kiriman Dua');
});

it('penanda data lama hanya menyentuh yang belum punya jejak, dan --kering tidak menulis', function () {
    $lama = testimoni(['nama' => 'Disetujui Dulu', 'status' => 'active']);
    $menunggu = testimoni(['status' => 'pending']);

    $this->artisan('testimoni:tandai-lama', ['--kering' => true])->assertSuccessful();
    expect(\App\Models\TestimoniRiwayat::count())->toBe(0);

    $this->artisan('testimoni:tandai-lama')->assertSuccessful();
    $jejak = \App\Models\TestimoniRiwayat::where('testimoni_id', $lama->id)->first();
    expect($jejak->aksi)->toBe('disetujui')
        ->and($jejak->keterangan)->toContain('Perkiraan')
        // Kiriman yang masih menunggu tidak ikut ditandai.
        ->and(\App\Models\TestimoniRiwayat::where('testimoni_id', $menunggu->id)->exists())->toBeFalse()
        // ditinjau_at dibiarkan kosong: waktunya memang tidak diketahui.
        ->and($lama->fresh()->ditinjau_at)->toBeNull();

    // Dijalankan dua kali tidak menggandakan jejak.
    $this->artisan('testimoni:tandai-lama')->assertSuccessful();
    expect(\App\Models\TestimoniRiwayat::where('testimoni_id', $lama->id)->count())->toBe(1);
});
