<?php

use App\Livewire\Pages\Admin\Asthana\AsthanaDaftarList;
use App\Livewire\Pages\Admin\Asthana\AsthanaTulisanForm;
use App\Livewire\Pages\Admin\Asthana\AsthanaTulisanList;
use App\Mail\TandaTerimaSitusMail;
use App\Models\Asthana\Angka;
use App\Models\Asthana\Berita;
use App\Models\Asthana\Klien;
use App\Models\Asthana\Portofolio;
use App\Models\Asthana\Testimoni;
use App\Models\Asthana\Tim;
use App\Models\Lowongan;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\KabarSitusAsthana;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Situs profil Asthana: API publik, lowongan & lamaran, kabar ke admin, dan
 * layar pengelola isinya di lemon.
 */
function penggunaSitus(array $izin = ['kelola_situs_asthana']): User
{
    $peran = Role::create(['name' => 'uji-ast-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);

    // Tanpa data karyawan, EnsureProfileComplete mengalihkan ke profil
    // sebelum rute adminnya dijalankan (pola penggunaAdmin di FiturAdminTest).
    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function lowonganUji(string $status = 'active'): Lowongan
{
    $judul = 'Lowongan '.Str::random(5);

    return Lowongan::create([
        'title' => $judul, 'slug' => Str::slug($judul), 'is_active' => $status,
        'requirements' => '<p>Syarat</p>', 'descriptions' => '<p>Deskripsi</p>',
    ]);
}

// ---------------------------------------------------------------- API publik

it('beranda hanya mengirim isi yang tampil dan terbit', function () {
    Angka::create(['label' => 'Proyek', 'nilai' => 40, 'akhiran' => '+', 'tampil' => true]);
    Angka::create(['label' => 'Rahasia', 'nilai' => 1, 'tampil' => false]);
    Klien::create(['nama' => 'Klien Tampil', 'tampil' => true]);
    Klien::create(['nama' => 'Klien Sembunyi', 'tampil' => false]);
    Testimoni::create(['nama' => 'Budi', 'isi' => 'Bagus', 'tampil' => true]);
    Portofolio::create(['judul' => 'Unggulan', 'slug' => 'unggulan', 'kategori' => 'teknologi', 'terbit' => true, 'unggulan' => true]);
    Portofolio::create(['judul' => 'Bukan Unggulan', 'slug' => 'biasa', 'kategori' => 'teknologi', 'terbit' => true]);
    Portofolio::create(['judul' => 'Draf Unggulan', 'slug' => 'draf', 'kategori' => 'teknologi', 'unggulan' => true]);
    Berita::create(['judul' => 'Sudah Terbit', 'slug' => 'terbit', 'terbit_at' => now()->subDay()]);
    Berita::create(['judul' => 'Terjadwal', 'slug' => 'nanti', 'terbit_at' => now()->addDay()]);
    Berita::create(['judul' => 'Draf', 'slug' => 'draf-berita']);

    $data = $this->getJson('/api/situs/beranda')->assertOk()->json('data');

    expect(collect($data['angka'])->pluck('label')->all())->toBe(['Proyek'])
        ->and(collect($data['klien'])->pluck('nama')->all())->toBe(['Klien Tampil'])
        ->and(collect($data['testimoni'])->pluck('nama')->all())->toBe(['Budi'])
        ->and(collect($data['portofolio'])->pluck('judul')->all())->toBe(['Unggulan'])
        ->and(collect($data['berita'])->pluck('judul')->all())->toBe(['Sudah Terbit']);
});

it('gambar dikirim sebagai alamat lengkap, bukan jalur relatif', function () {
    Klien::create(['nama' => 'Berlogo', 'logo' => 'asthana/klien/a.webp', 'tampil' => true]);

    $logo = $this->getJson('/api/situs/beranda')->json('data.klien.0.logo_url');

    expect($logo)->toStartWith('http')->toEndWith('/storage/asthana/klien/a.webp');
});

it('portofolio bisa disaring per kategori dan draf tidak bisa dibuka', function () {
    Portofolio::create(['judul' => 'Aplikasi', 'slug' => 'aplikasi', 'kategori' => 'teknologi', 'terbit' => true]);
    Portofolio::create(['judul' => 'Trip Bali', 'slug' => 'trip-bali', 'kategori' => 'pariwisata', 'terbit' => true, 'galeri' => ['asthana/g/1.webp']]);
    Portofolio::create(['judul' => 'Rahasia', 'slug' => 'rahasia', 'kategori' => 'teknologi']);

    expect($this->getJson('/api/situs/portofolio?kategori=pariwisata')->json('data.*.judul'))->toBe(['Trip Bali'])
        ->and($this->getJson('/api/situs/portofolio')->json('data.*.judul'))->toHaveCount(2);

    $detail = $this->getJson('/api/situs/portofolio/trip-bali')->assertOk()->json('data');
    expect($detail['galeri'][0])->toStartWith('http');

    $this->getJson('/api/situs/portofolio/rahasia')->assertNotFound();
});

it('berita berhalaman dan yang terjadwal belum bisa dibaca', function () {
    foreach (range(1, 11) as $n) {
        Berita::create(['judul' => "Berita {$n}", 'slug' => "berita-{$n}", 'terbit_at' => now()->subMinutes($n)]);
    }
    Berita::create(['judul' => 'Besok', 'slug' => 'besok', 'terbit_at' => now()->addDay()]);

    $res = $this->getJson('/api/situs/berita?halaman=2')->assertOk();
    expect($res->json('meta'))->toMatchArray(['halaman' => 2, 'halaman_terakhir' => 2, 'total' => 11])
        ->and($res->json('data'))->toHaveCount(2);

    $this->getJson('/api/situs/berita/besok')->assertNotFound();
    $this->getJson('/api/situs/berita/berita-1')->assertOk()->assertJsonPath('data.judul', 'Berita 1');
});

it('tim hanya yang tampil, menurut urutan', function () {
    Tim::create(['nama' => 'Kedua', 'jabatan' => 'Direktur', 'urutan' => 2]);
    Tim::create(['nama' => 'Pertama', 'jabatan' => 'Komisaris', 'urutan' => 1]);
    Tim::create(['nama' => 'Cuti', 'jabatan' => 'Staf', 'tampil' => false]);

    expect($this->getJson('/api/situs/tim')->json('data.*.nama'))->toBe(['Pertama', 'Kedua']);
});

// ------------------------------------------------------- lowongan & lamaran

it('daftar lowongan hanya yang aktif', function () {
    $buka = lowonganUji('active');
    lowonganUji('non-active');

    expect($this->getJson('/api/jobs')->json('data.*.id'))->toBe([$buka->id]);
});

it('pesan galat lamaran berbahasa Indonesia dan satuan ukurannya benar', function () {
    $galat = $this->postJson('/api/applications', [])->assertUnprocessable()->json('errors');

    expect($galat['name'][0])->toBe('Nama lengkap wajib diisi')
        ->and($galat['cv'][0])->toBe('CV wajib diunggah');

    $besar = UploadedFile::fake()->create('cv.pdf', 5000, 'application/pdf');
    $galat = $this->postJson('/api/applications', ['cv' => $besar])->json('errors.cv.0');
    expect($galat)->toBe('Ukuran CV maksimal 2 MB');
});

it('lamaran ke lowongan yang sudah ditutup ditolak', function () {
    $tutup = lowonganUji('non-active');

    $this->postJson('/api/applications', ['job_id' => $tutup->id])
        ->assertJsonPath('errors.job_id.0', 'Posisi ini sudah tidak dibuka');
});

it('lamaran masuk memberi tahu admin dan mengirim tanda terima', function () {
    Storage::fake('local');
    Mail::fake();
    Notification::fake();
    $admin = penggunaSitus(['view_pelamar']);
    $orangLain = penggunaSitus(['view_blog']);
    $lowongan = lowonganUji();

    $this->postJson('/api/applications', [
        'job_id' => $lowongan->id,
        'name' => 'Sari Pelamar',
        'email' => 'sari@gmail.com',
        'phone' => '0812-3456-7890',
        'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'cover_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    Notification::assertSentTo($admin, KabarSitusAsthana::class, fn ($n) => str_contains($n->isi, $lowongan->title));
    Notification::assertNotSentTo($orangLain, KabarSitusAsthana::class);
    Mail::assertSent(TandaTerimaSitusMail::class, fn ($m) => $m->hasTo('sari@gmail.com') && $m->jenis === 'lamaran');
});

it('pesan masuk memberi tahu admin dan mengirim tanda terima', function () {
    Mail::fake();
    Notification::fake();
    $admin = penggunaSitus(['view_message']);

    $this->postJson('/api/message', ['name' => 'Andi', 'email' => 'andi@gmail.com', 'message' => 'Halo, saya mau tanya harga.'])
        ->assertCreated();

    Notification::assertSentTo($admin, KabarSitusAsthana::class);
    Mail::assertSent(TandaTerimaSitusMail::class, fn ($m) => $m->hasTo('andi@gmail.com'));
});

it('tanda terima bisa dimatikan dan kegagalannya tidak membatalkan pesan', function () {
    config(['asthana.balasan_otomatis' => false]);
    Mail::fake();

    $this->postJson('/api/message', ['name' => 'Andi', 'email' => 'andi@gmail.com', 'message' => 'Pesan tanpa balasan.'])->assertCreated();
    Mail::assertNothingSent();

    config(['asthana.balasan_otomatis' => true]);
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP mati'));

    $this->postJson('/api/message', ['name' => 'Budi', 'email' => 'budi@gmail.com', 'message' => 'Pesan saat surel mati.'])->assertCreated();
    expect(Message::where('name', 'Budi')->exists())->toBeTrue();
});

// ------------------------------------------------------------- layar admin

it('layar isi situs tertutup bagi yang tidak berizin', function () {
    $this->actingAs(penggunaSitus(['view_blog']))->get('/admin/asthana/klien')->assertForbidden();
    $this->actingAs(penggunaSitus(['view_blog']))->get('/admin/asthana/portofolio')->assertForbidden();
});

it('semua layar isi situs terbuka bagi yang berizin', function () {
    $this->actingAs(penggunaSitus());

    foreach (['/admin/asthana/portofolio', '/admin/asthana/portofolio/tulis', '/admin/asthana/berita', '/admin/asthana/berita/tulis',
        '/admin/asthana/klien', '/admin/asthana/testimoni', '/admin/asthana/tim', '/admin/asthana/angka'] as $alamat) {
        $this->get($alamat)->assertOk();
    }
    $this->get('/admin/asthana/ngawur')->assertNotFound();
});

it('daftar pendek: tambah dengan logo, urutkan, sembunyikan, hapus', function () {
    Storage::fake('public');
    $this->actingAs(penggunaSitus());

    $t = Livewire::test(AsthanaDaftarList::class, ['jenis' => 'klien'])
        ->call('tambah')
        ->set('nama', 'Klien A')
        ->set('tautan', 'https://a.co.id')
        ->set('gambar', UploadedFile::fake()->image('logo.png', 800, 400))
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertSet('formTerbuka', false)
        ->call('tambah')->set('nama', 'Klien B')->call('simpan');

    $a = Klien::where('nama', 'Klien A')->first();
    expect($a->logo)->toEndWith('.webp');
    Storage::disk('public')->assertExists($a->logo);

    // B naik ke atas A
    $b = Klien::where('nama', 'Klien B')->first();
    $t->call('geser', $b->id, -1);
    expect(Klien::orderBy('urutan')->pluck('nama')->all())->toBe(['Klien B', 'Klien A']);

    $t->call('alihTampil', $a->id);
    expect($a->fresh()->tampil)->toBeFalse();

    $t->call('hapus', $a->id);
    expect(Klien::find($a->id))->toBeNull();
    Storage::disk('public')->assertMissing($a->logo);
});

it('daftar pendek: angka & testimoni divalidasi sesuai jenisnya', function () {
    $this->actingAs(penggunaSitus());

    Livewire::test(AsthanaDaftarList::class, ['jenis' => 'angka'])
        ->call('tambah')->set('label', 'Klien')->set('nilai', 'banyak')->set('ikon', 'trophy')
        ->call('simpan')->assertHasErrors(['nilai', 'ikon']);

    Livewire::test(AsthanaDaftarList::class, ['jenis' => 'testimoni'])
        ->call('tambah')->set('nama', 'Rina')->call('simpan')->assertHasErrors(['isi']);

    Livewire::test(AsthanaDaftarList::class, ['jenis' => 'tim'])
        ->call('tambah')->set('nama', 'Rina')->set('jabatan', 'Direktur')->set('tautan', 'https://linkedin.com/in/rina')
        ->call('simpan')->assertHasNoErrors();
    expect(Tim::first()->linkedin)->toBe('https://linkedin.com/in/rina');
});

it('tulis portofolio: slug & ringkasan otomatis, galeri tersimpan', function () {
    Storage::fake('public');
    $this->actingAs(penggunaSitus());

    Livewire::test(AsthanaTulisanForm::class, ['jenis' => 'portofolio'])
        ->set('judul', 'Sistem Kasir Toko Roti')
        ->assertSet('slug', 'sistem-kasir-toko-roti')
        ->set('isi', '<p>Kami membangun sistem kasir untuk dua belas cabang.</p>')
        ->set('kategori', 'teknologi')
        ->set('terbit', true)
        ->set('galeriBaru', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
        ->call('simpan')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.asthana.portofolio'));

    $p = Portofolio::first();
    expect($p->ringkasan)->toBe('Kami membangun sistem kasir untuk dua belas cabang.')
        ->and($p->galeri)->toHaveCount(2)
        ->and($p->terbit)->toBeTrue();
});

it('slug yang sudah beredar tidak berubah saat judul disunting', function () {
    $this->actingAs(penggunaSitus());
    $p = Portofolio::create(['judul' => 'Lama', 'slug' => 'alamat-lama', 'kategori' => 'teknologi']);

    Livewire::test(AsthanaTulisanForm::class, ['jenis' => 'portofolio', 'id' => $p->id])
        ->set('judul', 'Judul Baru')
        ->assertSet('slug', 'alamat-lama')
        ->call('simpan')->assertHasNoErrors();

    expect($p->fresh()->judul)->toBe('Judul Baru');
});

it('berita: draf, terjadwal, dan terbit', function () {
    $this->actingAs(penggunaSitus());

    Livewire::test(AsthanaTulisanForm::class, ['jenis' => 'berita'])
        ->set('judul', 'Kantor Baru')->set('keadaanBerita', 'jadwal')->set('jadwal', '')
        ->call('simpan')->assertHasErrors(['jadwal'])
        ->set('jadwal', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->call('simpan')->assertHasNoErrors();

    $b = Berita::first();
    expect($b->keadaan())->toBe('terjadwal');

    Livewire::test(AsthanaTulisanForm::class, ['jenis' => 'berita', 'id' => $b->id])
        ->assertSet('keadaanBerita', 'jadwal')
        ->set('keadaanBerita', 'terbit')->call('simpan');
    expect($b->fresh()->keadaan())->toBe('terbit');
});

it('daftar tulisan menghitung keadaan dan bisa menerbitkan portofolio', function () {
    $this->actingAs(penggunaSitus());
    $draf = Portofolio::create(['judul' => 'Draf', 'slug' => 'draf', 'kategori' => 'teknologi']);
    Portofolio::create(['judul' => 'Terbit', 'slug' => 'terbit', 'kategori' => 'pariwisata', 'terbit' => true]);

    $t = Livewire::test(AsthanaTulisanList::class, ['jenis' => 'portofolio']);
    expect($t->viewData('jumlah')->all())->toBe(['semua' => 2, 'terbit' => 1, 'draf' => 1]);

    $t->call('alihTerbit', $draf->id);
    expect($draf->fresh()->terbit)->toBeTrue();
});

it('seeder membagikan izin ke peran pengelola lowongan', function () {
    $hr = Role::create(['name' => 'hr-uji', 'description' => 'uji']);
    $hr->permissions()->attach(Permission::firstOrCreate(['name' => 'view_lowongan'], ['display_name' => 'x', 'group' => 'x', 'description' => 'x'])->id);
    $lain = Role::create(['name' => 'lain-uji', 'description' => 'uji']);

    $this->seed(\Database\Seeders\AksesSitusAsthanaSeeder::class);
    $this->seed(\Database\Seeders\AksesSitusAsthanaSeeder::class); // aman diulang

    expect($hr->hasPermission('kelola_situs_asthana'))->toBeTrue()
        ->and($lain->hasPermission('kelola_situs_asthana'))->toBeFalse()
        ->and(Permission::where('name', 'kelola_situs_asthana')->count())->toBe(1);
});
