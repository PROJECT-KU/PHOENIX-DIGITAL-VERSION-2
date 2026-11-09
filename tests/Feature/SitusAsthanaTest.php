<?php

use App\Livewire\Pages\Admin\Asthana\AsthanaDaftarList;
use App\Livewire\Pages\Admin\Asthana\AsthanaTulisanForm;
use App\Livewire\Pages\Admin\Asthana\AsthanaTulisanList;
use App\Mail\TandaTerimaSitusMail;
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

    // Angka pencapaian sudah dibuang dari situs (9 Nov 2026).
    expect($data)->not->toHaveKey('angka')
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

it('daftar lowongan memuat rincian dan menyembunyikan yang lewat batas', function () {
    $nanti = lowonganUji();
    $nanti->update([
        'unit' => 'reka', 'jenis' => 'magang', 'lokasi' => 'Yogyakarta', 'pola_kerja' => 'hybrid',
        'batas_lamaran' => now()->addDays(10)->toDateString(), 'ringkasan' => 'Bantu tim desain.',
    ]);
    $hariIni = lowonganUji();
    $hariIni->update(['batas_lamaran' => now()->toDateString()]);
    $lewat = lowonganUji();
    $lewat->update(['batas_lamaran' => now()->subDay()->toDateString()]);
    $tanpaBatas = lowonganUji();

    $data = $this->getJson('/api/jobs')->assertOk()->json('data');

    // Yang paling cepat ditutup lebih dulu; tanpa batas di belakang.
    expect(array_column($data, 'id'))->toBe([$hariIni->id, $nanti->id, $tanpaBatas->id]);

    $reka = collect($data)->firstWhere('id', $nanti->id);
    expect($reka)->toMatchArray([
        'unit' => 'reka', 'unit_label' => 'Reka Technology', 'jenis_label' => 'Magang',
        'lokasi' => 'Yogyakarta', 'pola_kerja_label' => 'Hybrid', 'ringkasan' => 'Bantu tim desain.',
    ]);
    // Daftar tidak membawa isi panjang; detail yang membawanya.
    expect($reka)->not->toHaveKey('descriptions');

    $this->getJson("/api/jobs/{$lewat->slug}")->assertNotFound();
    $this->getJson("/api/jobs/{$nanti->slug}")->assertOk()
        ->assertJsonPath('data.descriptions', '<p>Deskripsi</p>')
        ->assertJsonPath('data.unit_label', 'Reka Technology');
});

it('lowongan lama tanpa rincian tetap tampil sebagai Asthana', function () {
    $lama = lowonganUji();

    $baris = collect($this->getJson('/api/jobs')->json('data'))->firstWhere('id', $lama->id);

    expect($baris['unit'])->toBe('asthana')
        ->and($baris['jenis_label'])->toBeNull()
        ->and($baris['batas_lamaran'])->toBeNull();
});

it('lamaran ke lowongan yang batasnya sudah lewat ditolak', function () {
    $lewat = lowonganUji();
    $lewat->update(['batas_lamaran' => now()->subDay()->toDateString()]);

    $this->postJson('/api/applications', ['job_id' => $lewat->id])
        ->assertJsonPath('errors.job_id.0', 'Posisi ini sudah tidak dibuka');
});

it('lamaran umum tanpa posisi diterima, berkas pendukung opsional tersimpan privat', function () {
    Storage::fake('local');
    Mail::fake();
    Notification::fake();
    $admin = penggunaSitus(['view_pelamar']);

    $this->postJson('/api/applications', [
        'name' => 'Budi Umum',
        'email' => 'budi@gmail.com',
        'phone' => '0812-1111-2222',
        'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'cover_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'lampiran' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        'portofolio_url' => 'https://behance.net/budi',
        'sumber_info' => 'instagram',
    ])->assertCreated();

    $lamaran = \App\Models\JobApplication::where('email', 'budi@gmail.com')->firstOrFail();
    expect($lamaran->job_id)->toBeNull()
        ->and($lamaran->cover_letter_path)->not->toBeNull()
        ->and($lamaran->lampiran_path)->toContain('pendukung-sertifikat')
        ->and($lamaran->posisi_label)->toBe('Lamaran umum')
        ->and($lamaran->portofolio_url)->toBe('https://behance.net/budi')
        ->and($lamaran->sumber_info_label)->toBe('Instagram');

    Storage::disk('local')->assertExists($lamaran->lampiran_path);

    Notification::assertSentTo($admin, KabarSitusAsthana::class, fn ($n) => str_contains($n->isi, 'lamaran umum'));
    Mail::assertSent(TandaTerimaSitusMail::class, fn ($m) => $m->hasTo('budi@gmail.com') && $m->posisi === null);
});

it('tautan portofolio harus http(s) dan sumber info harus dikenal', function () {
    $galat = $this->postJson('/api/applications', [
        'portofolio_url' => 'javascript:alert(1)',
        'sumber_info' => 'koran',
    ])->assertUnprocessable()->json('errors');

    expect($galat['portofolio_url'][0])->toBe('Tautan portofolio harus diawali http:// atau https://')
        ->and($galat['sumber_info'][0])->toBe('Pilihan sumber informasi tidak dikenal')
        ->and($galat['cover_letter'][0])->toBe('Surat lamaran wajib diunggah')
        ->and($galat)->not->toHaveKey('lampiran')
        ->and($galat)->not->toHaveKey('job_id');

    $galat = $this->postJson('/api/applications', [
        'lampiran' => UploadedFile::fake()->create('foto.png', 10, 'image/png'),
    ])->json('errors');
    expect($galat['lampiran'][0])->toBe('Berkas pendukung harus berformat PDF');
});

it('formulir admin lowongan menyimpan rincian situs', function () {
    $admin = penggunaSitus(['create_lowongan', 'edit_lowongan']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class)
        ->set('title', 'Desainer UI Uji')
        ->set('slug', 'desainer-ui-uji')
        ->set('isActive', 'active')
        ->set('descriptions', '<p>Desain</p>')
        ->set('requirements', '<ul><li>Figma</li></ul>')
        ->set('unit', 'reka')
        ->set('jenis', 'kontrak')
        ->set('polaKerja', 'remote')
        ->set('lokasi', ' Yogyakarta ')
        ->set('batasLamaran', '2030-01-31')
        ->set('ringkasan', 'Merancang antarmuka klien Reka.')
        ->set('gaji', '')
        ->set('jumlahPosisi', 2)
        ->call('save')
        ->assertHasNoErrors();

    $l = Lowongan::where('slug', 'desainer-ui-uji')->firstOrFail();
    expect($l->only(['unit', 'jenis', 'pola_kerja', 'lokasi', 'ringkasan', 'gaji', 'jumlah_posisi']))->toBe([
        'unit' => 'reka', 'jenis' => 'kontrak', 'pola_kerja' => 'remote', 'lokasi' => 'Yogyakarta',
        'ringkasan' => 'Merancang antarmuka klien Reka.', 'gaji' => null, 'jumlah_posisi' => 2,
    ])->and($l->batas_lamaran->toDateString())->toBe('2030-01-31');

    // Mode ubah memuat ulang isian yang sama.
    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm::class, ['lowongan' => $l])
        ->assertSet('unit', 'reka')
        ->assertSet('polaKerja', 'remote')
        ->assertSet('batasLamaran', '2030-01-31');
});

it('pilihan unit, jenis & pola kerja ditandai @selected di formulir admin', function () {
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/lowongan-pekerjaan/lowongan-pekerjaan-form.blade.php'));

    preg_match_all('/<option\b[^>]*>/', $sumber, $opsi);
    foreach ($opsi[0] as $tag) {
        expect($tag)->toContain('@selected((string) $this->');
    }
});

function kirimLamaranUji(array $ubah = [])
{
    return test()->postJson('/api/applications', $ubah + [
        'name' => 'Rina Uji',
        'email' => 'rina@gmail.com',
        'phone' => '0812-9999-0000',
        'cv' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
        'cover_letter' => UploadedFile::fake()->create('surat.pdf', 50, 'application/pdf'),
    ]);
}

it('nomor lamaran berkode unit, tanggal, dan acak', function () {
    Storage::fake('local');
    Mail::fake();
    $reka = lowonganUji();
    $reka->update(['unit' => 'reka']);

    $nomor = kirimLamaranUji(['job_id' => $reka->id])->assertCreated()->json('data.application_number');
    expect($nomor)->toMatch('/^RKA-'.now()->format('dmy').'-[A-HJ-NP-Z2-9]{5}$/');

    $umum = kirimLamaranUji(['email' => 'lain@gmail.com'])->json('data.application_number');
    expect($umum)->toStartWith('AST-');

    Mail::assertSent(TandaTerimaSitusMail::class, fn ($m) => $m->nomor === $nomor);
});

it('cek status butuh nomor DAN email yang cocok', function () {
    Storage::fake('local');
    Mail::fake();
    $nomor = kirimLamaranUji()->json('data.application_number');

    $this->postJson('/api/applications/status', ['nomor' => strtolower($nomor), 'email' => 'RINA@gmail.com'])
        ->assertOk()
        ->assertJsonPath('data.status', 'baru')
        ->assertJsonPath('data.tahap', 0)
        ->assertJsonPath('data.posisi', null);

    $this->postJson('/api/applications/status', ['nomor' => $nomor, 'email' => 'orang@lain.com'])->assertNotFound();
    $this->postJson('/api/applications/status', ['nomor' => 'AST-010101-AAAAA', 'email' => 'rina@gmail.com'])->assertNotFound();
});

it('lamaran ganda dalam 30 hari ditolak dengan menyebut nomor lamanya', function () {
    Storage::fake('local');
    Mail::fake();
    $lowongan = lowonganUji();
    $nomor = kirimLamaranUji(['job_id' => $lowongan->id])->assertCreated()->json('data.application_number');

    $galat = kirimLamaranUji(['job_id' => $lowongan->id, 'email' => 'Rina@Gmail.com'])->assertUnprocessable()->json('errors.email.0');
    expect($galat)->toContain($nomor);

    // Posisi lain tetap boleh.
    kirimLamaranUji(['job_id' => lowonganUji()->id])->assertCreated();
});

it('isian jebakan bot dijawab sukses tetapi tidak disimpan', function () {
    Storage::fake('local');
    Mail::fake();
    $sebelum = \App\Models\JobApplication::count();

    kirimLamaranUji(['situs_web' => 'http://spam.example'])->assertCreated();

    expect(\App\Models\JobApplication::count())->toBe($sebelum);
    Mail::assertNothingSent();
});

it('membuka lowongan beberapa kali tidak menghabiskan jatah kirim lamaran', function () {
    Storage::fake('local');
    Mail::fake();
    \Illuminate\Support\Facades\RateLimiter::clear('lamaran'.sha1('|127.0.0.1'));
    $lowongan = lowonganUji();

    // Pelamar: daftar, detail, daftar (formulir), lalu kirim — dulu 429.
    foreach (range(1, 6) as $_) {
        $this->getJson('/api/jobs')->assertOk();
    }
    kirimLamaranUji(['job_id' => $lowongan->id])->assertCreated();
});

it('admin mengubah tahap: tersimpan dan pelamar mendapat surel berisi pesan', function () {
    Storage::fake('local');
    Mail::fake();
    $admin = penggunaSitus(['view_pelamar']);
    kirimLamaranUji();
    $lamaran = \App\Models\JobApplication::where('email', 'rina@gmail.com')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail::class, ['id' => $lamaran->id])
        ->set('statusBaru', 'wawancara')
        ->set('pesanPelamar', 'Senin pukul 10.00 lewat Google Meet.')
        ->call('simpanStatus')
        ->assertHasNoErrors()
        ->set('catatanInternal', 'Komunikatif.')
        ->call('simpanCatatan');

    $lamaran->refresh();
    expect($lamaran->status)->toBe('wawancara')
        ->and($lamaran->status_diubah_at)->not->toBeNull()
        ->and($lamaran->catatan_internal)->toBe('Komunikatif.');

    Mail::assertSent(\App\Mail\StatusLamaranMail::class, fn ($m) => $m->hasTo('rina@gmail.com') && $m->pesan === 'Senin pukul 10.00 lewat Google Meet.');

    $this->postJson('/api/applications/status', ['nomor' => $lamaran->nomor_lamaran, 'email' => 'rina@gmail.com'])
        ->assertJsonPath('data.status_label', 'Wawancara');
});

it('surel status dan tanda terima menyisipkan logo sebagai lampiran sebaris', function () {
    $l = new \App\Models\JobApplication(['name' => 'Rina', 'nomor_lamaran' => 'AST-010126-ABCDE', 'status' => 'diterima']);
    $html = (new \App\Mail\StatusLamaranMail($l))->render();
    expect($html)->toContain('src="cid:')->toContain('AST-010126-ABCDE')->toContain('Selamat');

    $html = (new TandaTerimaSitusMail('lamaran', 'Rina', null, 'AST-010126-ABCDE'))->render();
    expect($html)->toContain('src="cid:')->toContain('lamaran umum');
});

it('ekspor pelamar mengikuti saringan tahap', function () {
    Storage::fake('local');
    Mail::fake();
    \Maatwebsite\Excel\Facades\Excel::fake();
    $admin = penggunaSitus(['view_pelamar']);
    kirimLamaranUji();
    kirimLamaranUji(['email' => 'budi@gmail.com']);
    \App\Models\JobApplication::where('email', 'budi@gmail.com')->update(['status' => 'diterima']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList::class)
        ->set('filterStatus', 'diterima')
        ->call('ekspor');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('pelamar-kerja-'.now()->format('Ymd-His').'.xlsx', function ($ekspor) {
        $view = $ekspor->view();

        return $view->getData()['pelamar']->pluck('email')->all() === ['budi@gmail.com'];
    });
});

it('tautan bagikan lowongan memberi meta OG lalu mengalihkan ke situs', function () {
    config(['asthana.situs_url' => 'https://asthana.test']);
    $l = lowonganUji();
    $l->update(['ringkasan' => 'Bantu tim desain.', 'unit' => 'orcha']);

    $this->get("/lowongan/{$l->slug}")
        ->assertOk()
        ->assertSee('og:title', false)
        ->assertSee("Lowongan {$l->title} — Orcha Journey", false)
        ->assertSee('Bantu tim desain.', false)
        ->assertSee('"@type":"JobPosting"', false)
        ->assertSee("https://asthana.test/karir/detail.html?slug={$l->slug}", false);

    $tutup = lowonganUji('non-active');
    $this->get("/lowongan/{$tutup->slug}")->assertRedirect('https://asthana.test/karir/');

    $this->get('/api/situs/sitemap-lowongan.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee("https://asthana.test/karir/detail.html?slug={$l->slug}", false)
        ->assertDontSee($tutup->slug, false);
});

it('suara karyawan dikelola di lemon dan tampil di API karir', function () {
    Storage::fake('public');
    $admin = penggunaSitus();

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Pages\Admin\Asthana\AsthanaDaftarList::class, ['jenis' => 'karyawan'])
        ->call('tambah')
        ->set('nama', 'Dewi')
        ->set('jabatan', 'Tour Leader')
        ->set('perusahaan', 'Orcha Journey')
        ->set('isi', 'Setiap trip adalah pengalaman baru.')
        ->call('simpan')
        ->assertHasNoErrors();

    $this->getJson('/api/situs/karir')->assertOk()
        ->assertJsonPath('data.suara_karyawan.0.nama', 'Dewi')
        ->assertJsonPath('data.suara_karyawan.0.perusahaan', 'Orcha Journey');

    // Testimoni klien di beranda tidak tercampur.
    expect($this->getJson('/api/situs/beranda')->json('data.testimoni'))->toBe([]);
});

it('ringkas menghitung hanya portofolio & berita yang terbit', function () {
    expect($this->getJson('/api/situs/ringkas')->assertOk()->json('data'))->toBe(['portofolio' => 0, 'berita' => 0]);

    Portofolio::create(['judul' => 'Terbit', 'slug' => 'terbit', 'kategori' => 'teknologi', 'terbit' => true]);
    Portofolio::create(['judul' => 'Draf', 'slug' => 'draf', 'kategori' => 'teknologi']);
    Berita::create(['judul' => 'Nanti', 'slug' => 'nanti', 'terbit_at' => now()->addDay()]);

    expect($this->getJson('/api/situs/ringkas')->json('data'))->toBe(['portofolio' => 1, 'berita' => 0]);
});

it('tautan bagikan portofolio & berita memberi meta OG per isi', function () {
    config(['asthana.situs_url' => 'https://asthana.test']);
    Portofolio::create(['judul' => 'Aplikasi Kasir', 'slug' => 'aplikasi-kasir', 'kategori' => 'teknologi', 'terbit' => true, 'ringkasan' => 'Kasir untuk UMKM.']);
    Berita::create(['judul' => 'Kantor Baru', 'slug' => 'kantor-baru', 'terbit_at' => now()->subDay(), 'ringkasan' => 'Kami pindah.']);
    Berita::create(['judul' => 'Rahasia', 'slug' => 'rahasia', 'terbit_at' => now()->addDay()]);

    $this->get('/situs/portofolio/aplikasi-kasir')->assertOk()
        ->assertSee('Aplikasi Kasir — Portofolio PT. Asthana Cipta Mandiri', false)
        ->assertSee('Kasir untuk UMKM.', false)
        ->assertSee('https://asthana.test/portofolio/detail.html?slug=aplikasi-kasir', false);

    $this->get('/situs/berita/kantor-baru')->assertOk()
        ->assertSee('"@type":"NewsArticle"', false)
        ->assertSee('https://asthana.test/berita/baca.html?slug=kantor-baru', false);

    // Berita terjadwal belum boleh bocor lewat pratinjau.
    $this->get('/situs/berita/rahasia')->assertRedirect('https://asthana.test/berita/');
});

it('pesan dari bot (kolom jebakan terisi) tidak disimpan', function () {
    Mail::fake();
    $sebelum = Message::count();

    $this->postJson('/api/message', ['name' => 'Bot', 'email' => 'bot@gmail.com', 'message' => 'Beli obat murah di sini sekarang', 'situs_web' => 'http://spam.test'])
        ->assertCreated();

    expect(Message::count())->toBe($sebelum);
    Mail::assertNothingSent();
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
        '/admin/asthana/klien', '/admin/asthana/testimoni', '/admin/asthana/tim'] as $alamat) {
        $this->get($alamat)->assertOk();
    }
    $this->get('/admin/asthana/ngawur')->assertNotFound();
    $this->get('/admin/asthana/angka')->assertNotFound();
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

it('daftar pendek: testimoni & tim divalidasi sesuai jenisnya', function () {
    $this->actingAs(penggunaSitus());

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
