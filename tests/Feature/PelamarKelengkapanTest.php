<?php

use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList;
use App\Models\JejakLamaran;
use App\Models\JobApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
 | Kelengkapan layar Pelamar — tujuh kekurangan yang ditemukan 10 Nov 2026
 | dan ditutup bersamaan. Semuanya cacat yang TIDAK menggagalkan apa pun:
 | layarnya tetap 200, datanya tetap tersimpan, dan yang salah cuma siapa
 | yang boleh, apa yang tercatat, dan apa yang terlihat.
 */

function adminPelamarLengkap(array $izin = ['akses_asthana', 'view_pelamar', 'edit_pelamar']): \App\Models\User
{
    return adminRab($izin);
}

function lamaranUji(array $ubah = []): JobApplication
{
    return JobApplication::create(array_merge([
        'name' => 'Siti Rahma',
        'email' => 'siti'.uniqid().'@contoh.test',
        'phone' => '081200000000',
    ], $ubah));
}

/* ====================================================================
 | 1. IZIN — membaca tidak sama dengan memutuskan
 | ================================================================= */

it('menolak mengubah tahap tanpa izin edit_pelamar', function () {
    $p = lamaranUji();
    $pembacaSaja = adminPelamarLengkap(['akses_asthana', 'view_pelamar']);

    Livewire::actingAs($pembacaSaja)
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('statusBaru', 'ditolak')
        ->call('simpanStatus')
        ->assertForbidden();

    // Yang penting bukan pesan galatnya, melainkan bahwa tahapnya memang
    // tidak bergerak: surel penolakan dikirim dari perubahan tahap.
    expect($p->fresh()->status)->toBe('baru');
});

it('menolak menulis catatan tanpa izin edit_pelamar', function () {
    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap(['akses_asthana', 'view_pelamar']))
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('catatanBaru', 'rahasia')
        ->call('simpanCatatan')
        ->assertForbidden();

    expect(JejakLamaran::where('job_application_id', $p->id)->count())->toBe(0);
});

it('menyembunyikan tombol tahap dari yang hanya boleh membaca', function () {
    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap(['akses_asthana', 'view_pelamar']))
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->assertDontSee('Simpan tahap')
        ->assertSee('tidak mengubah tahapnya');
});

it('mengizinkan yang berizin edit mengubah tahap', function () {
    Mail::fake();
    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('statusBaru', 'diseleksi')
        ->call('simpanStatus')
        ->assertHasNoErrors();

    expect($p->fresh()->status)->toBe('diseleksi');
});

/* ====================================================================
 | 2. SARINGAN BULAN — labelnya menyebut tahun, kueriya harus juga
 | ================================================================= */

it('menyaring bulan BESERTA tahunnya', function () {
    $sekarang = lamaranUji(['name' => 'Pelamar Sekarang']);
    $sekarang->forceFill(['created_at' => now()->startOfMonth()->addDay()])->saveQuietly();

    $setahunLalu = lamaranUji(['name' => 'Pelamar Tahun Lalu']);
    $setahunLalu->forceFill(['created_at' => now()->startOfMonth()->addDay()->subYear()])->saveQuietly();

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->set('filterMonth', now()->format('Y-m'))
        ->assertSee('Pelamar Sekarang')
        // Inilah bugnya: whereMonth tanpa whereYear memanggil bulan yang sama
        // dari TAHUN MANA PUN, dan daftarnya mencampur dua tahun tanpa tanda.
        ->assertDontSee('Pelamar Tahun Lalu');
});

it('menawarkan nilai bulan yang memuat tahun', function () {
    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->assertSeeHtml('value="'.now()->format('Y-m').'"');
});

/* ====================================================================
 | 3. JEJAK — siapa yang memutuskan, dan catatan yang tidak saling timpa
 | ================================================================= */

it('mencatat siapa yang mengubah tahap dan dari mana', function () {
    Mail::fake();
    $p = lamaranUji(['status' => 'diseleksi']);
    $admin = adminPelamarLengkap();

    Livewire::actingAs($admin)
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('statusBaru', 'wawancara')
        ->call('simpanStatus');

    $jejak = JejakLamaran::where('job_application_id', $p->id)->where('jenis', 'tahap')->first();

    expect($jejak)->not->toBeNull()
        ->and($jejak->dari_status)->toBe('diseleksi')
        ->and($jejak->ke_status)->toBe('wawancara')
        ->and($jejak->nama_penulis)->toBe($admin->name);
});

it('menyimpan catatan baru TANPA menimpa catatan sebelumnya', function () {
    $p = lamaranUji();

    $komponen = Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('catatanBaru', 'Wawancara tahap satu bagus.')
        ->call('simpanCatatan')
        ->set('catatanBaru', 'Tes teknis kurang.')
        ->call('simpanCatatan');

    // Dulu satu kotak teks: yang kedua menimpa yang pertama, tanpa jejak.
    expect(JejakLamaran::where('job_application_id', $p->id)->where('jenis', 'catatan')->count())->toBe(2);

    $komponen->assertSee('Wawancara tahap satu bagus.')
        ->assertSee('Tes teknis kurang.');
});

it('mengosongkan kotak catatan sesudah disimpan', function () {
    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('catatanBaru', 'sudah')
        ->call('simpanCatatan')
        ->assertSet('catatanBaru', '');
});

it('menolak catatan kosong', function () {
    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->set('catatanBaru', '')
        ->call('simpanCatatan')
        ->assertHasErrors(['catatanBaru' => 'required']);
});

/* ====================================================================
 | 4. BERKAS DIBUKA — kalimat yang dulu cuma menebak
 | ================================================================= */

it('menandai berkas sudah dibuka saat berkasnya benar-benar diambil', function () {
    Storage::fake('local');

    $p = lamaranUji([
        'cv_path' => UploadedFile::fake()->create('cv.pdf', 8, 'application/pdf')
            ->storeAs('applications/uji', 'cv-'.uniqid().'.pdf', 'local'),
    ]);

    expect($p->berkas_dibuka_at)->toBeNull();

    $admin = adminPelamarLengkap();
    $this->actingAs($admin)->get(route('admin.pelamar.cv', $p))->assertOk();

    $p->refresh();
    expect($p->berkas_dibuka_at)->not->toBeNull()
        ->and($p->berkas_dibuka_oleh)->toBe($admin->name);
});

it('tidak menimpa penanda dibuka, dan tidak menyentuh updated_at', function () {
    Storage::fake('local');

    $p = lamaranUji([
        'cv_path' => UploadedFile::fake()->create('cv.pdf', 8, 'application/pdf')
            ->storeAs('applications/uji', 'cv-'.uniqid().'.pdf', 'local'),
    ]);

    $p->forceFill([
        'berkas_dibuka_at' => now()->subDays(3),
        'berkas_dibuka_oleh' => 'Pembuka Pertama',
        'updated_at' => now()->subDays(10),
    ])->saveQuietly();

    $sebelum = $p->fresh()->updated_at;

    $this->actingAs(adminPelamarLengkap())->get(route('admin.pelamar.cv', $p))->assertOk();

    $p->refresh();

    // Yang dijawab kolom ini adalah "sudah ada yang menyentuh atau belum".
    expect($p->berkas_dibuka_oleh)->toBe('Pembuka Pertama')
        // updated_at yang bergerak membuat pemangkas berkas kedaluwarsa
        // mengira lamaran ini baru diurus.
        ->and($p->updated_at->timestamp)->toBe($sebelum->timestamp);
});

it('menandai di daftar bahwa berkasnya belum dibuka', function () {
    Storage::fake('local');

    lamaranUji([
        'name' => 'Belum Tersentuh',
        'cv_path' => 'applications/uji/apa-saja.pdf',
    ]);

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->assertSee('berkas belum dibuka');
});

/* ====================================================================
 | 5. PELAMAR GANDA
 | ================================================================= */

it('menandai pelamar yang emailnya punya lebih dari satu lamaran', function () {
    $email = 'ganda'.uniqid().'@contoh.test';
    lamaranUji(['name' => 'Orang Sama', 'email' => $email]);
    lamaranUji(['name' => 'Orang Sama', 'email' => $email]);

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->assertSee('2 lamaran');
});

it('menunjukkan lamaran lain orang yang sama di halaman detail', function () {
    $email = 'ganda'.uniqid().'@contoh.test';
    $lama = lamaranUji(['name' => 'Orang Sama', 'email' => $email]);
    $baru = lamaranUji(['name' => 'Orang Sama', 'email' => $email]);

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $baru->id])
        ->assertSee('Pernah melamar sebelumnya')
        ->assertSeeHtml(route('admin.pelamar.detail', $lama->id));
});

/* ====================================================================
 | 6. URUT & AKSI MASSAL
 | ================================================================= */

it('mengurutkan daftar menurut nama', function () {
    lamaranUji(['name' => 'Zulfa Akhir']);
    lamaranUji(['name' => 'Ahmad Awal']);

    $komponen = Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->call('urutkan', 'nama');

    expect($komponen->get('arahUrut'))->toBe('asc');

    $html = $komponen->html();
    expect(strpos($html, 'Ahmad Awal'))->toBeLessThan(strpos($html, 'Zulfa Akhir'));

    // Menekan kolom yang sama membalik arahnya.
    expect($komponen->call('urutkan', 'nama')->get('arahUrut'))->toBe('desc');
});

it('memindahkan tahap beberapa lamaran sekaligus TANPA mengirim surel', function () {
    Mail::fake();

    $a = lamaranUji(['name' => 'Massal A']);
    $b = lamaranUji(['name' => 'Massal B']);

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->set('pilihan', [(string) $a->id, (string) $b->id])
        ->set('tahapMassal', 'diseleksi')
        ->call('terapkanTahapMassal');

    expect($a->fresh()->status)->toBe('diseleksi')
        ->and($b->fresh()->status)->toBe('diseleksi');

    // Belasan surel tanpa pesan pribadi dari satu klik tidak bisa ditarik.
    Mail::assertNothingSent();

    // Tetap tercatat siapa yang memindahkan.
    expect(JejakLamaran::where('jenis', 'tahap')->count())->toBe(2);
});

it('menolak aksi massal dari yang tidak berizin edit', function () {
    $a = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap(['akses_asthana', 'view_pelamar']))
        ->test(PelamarKerjaList::class)
        ->set('pilihan', [(string) $a->id])
        ->set('tahapMassal', 'ditolak')
        ->call('terapkanTahapMassal');

    expect($a->fresh()->status)->toBe('baru');
});

/* ====================================================================
 | 7. BERKAS DISIMPAN SAMPAI PELAMARNYA DIHAPUS
 | ================================================================= */

it('tidak menghapus berkas pelamar secara otomatis', function () {
    /*
     | Sampai 10 Nov 2026 ada 'pelamar:hapus-berkas-kadaluarsa' terjadwal tiap
     | hari: CV dibuang 90 hari sesudah lamaran diputuskan. Dicabut atas
     | permintaan pemilik — berkas disimpan sampai ada yang menghapus
     | pelamarnya.
     |
     | Uji ini menjaga keputusan itu dari "dihidupkan lagi tanpa sengaja":
     | pemangkas yang kembali diam-diam menghapus CV orang, dan kehilangannya
     | baru ketahuan saat ada yang mencarinya.
     */
    expect(file_exists(app_path('Console/Commands/HapusBerkasPelamarKadaluarsa.php')))->toBeFalse();

    // Komentar dibuang dulu: keterangan di routes/console.php yang menjelaskan
    // kenapa pemangkas ini dicabut justru harus menyebut nama perintahnya.
    $jadwal = preg_replace('#/\*.*?\*/#s', '', file_get_contents(base_path('routes/console.php')));
    expect($jadwal)->not->toContain('pelamar:hapus-berkas-kadaluarsa');

    // Dan layarnya tidak lagi menjanjikan tenggat yang tidak ada.
    $p = lamaranUji(['cv_path' => 'applications/uji/cv.pdf']);

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->assertDontSee('dijadwalkan dihapus');
});

it('ikut membuang berkas saat pelamarnya dihapus', function () {
    Storage::fake('local');

    $jalur = UploadedFile::fake()->create('cv.pdf', 8, 'application/pdf')
        ->storeAs('applications/2026/11/'.\Illuminate\Support\Str::uuid(), 'cv.pdf', 'local');

    $p = lamaranUji(['cv_path' => $jalur]);

    expect(Storage::disk('local')->exists($jalur))->toBeTrue();

    Livewire::actingAs(adminPelamarLengkap(['akses_asthana', 'view_pelamar', 'delete_pelamar']))
        ->test(PelamarKerjaList::class)
        ->call('delete', $p->id);

    // Satu-satunya jalan berkas pelamar hilang: pelamarnya dihapus.
    expect(Storage::disk('local')->exists($jalur))->toBeFalse()
        ->and(JobApplication::find($p->id))->toBeNull();
});

/* ====================================================================
 | 8. RUPA — kelas bersama yang dipakai di luar maksudnya
 | ================================================================= */

it('menampilkan nomor lamaran di SEMUA lebar layar', function () {
    /*
     | .dsb-tabel-samar berarti "salinan kolom yang hilang di layar sempit":
     | ia disembunyikan di >=1200px DAN di <768px. Nomor lamaran bukan
     | salinan kolom mana pun — memakainya membuat nomor itu cuma muncul di
     | rentang tablet, padahal itulah kunci pelamar mengecek statusnya.
     */
    $sumber = file_get_contents(
        resource_path('views/livewire/pages/admin/pelamar-kerja/pelamar-kerja-list.blade.php')
    );

    /*
     | Komentar dibuang DULU.
     |
     | Keterangan yang menjelaskan kenapa kelas ini dilarang justru harus
     | menyebut namanya — dan tanpa langkah ini, uji ini merah oleh tulisan
     | yang menerangkan dirinya sendiri.
     */
    $tanpaKomentar = preg_replace(['/\{\{--.*?--\}\}/s', '#/\*.*?\*/#s'], '', $sumber);

    expect($tanpaKomentar)->not->toContain('dsb-tabel-samar');

    /*
     | Keterangan kecil di sel tanggal memakai kelas MILIK layar ini.
     |
     | Dulu ditulis "dsb-tabel-samar d-block": .d-block Bootstrap ber-
     | !important, jadi ia menang atas media query kelas bersamanya dan
     | justru selalu tampil — sementara nomor lamaran di baris sebelahnya,
     | dengan kelas bersama yang sama, selalu hilang. Dua hasil berlawanan
     | dari satu kelas, dua-duanya tidak disengaja.
     */
    expect($tanpaKomentar)->toContain('plm-catatan-sel')
        ->and($tanpaKomentar)->not->toContain('samar d-block');

    $p = lamaranUji();

    Livewire::actingAs(adminPelamarLengkap())
        ->test(PelamarKerjaList::class)
        ->assertSee($p->fresh()->nomor_lamaran);
});

it('menyambung rantai flex supaya pembaca berkas mengisi tinggi kartunya', function () {
    /*
     | .dsb-rak meregangkan semua kartu dalam satu baris setinggi yang
     | tertinggi. Kolom kanan (alur tahap + keterangan) lebih tinggi daripada
     | pembaca berkas, jadi kartu kiri ikut diregangkan — dan kalau rantai
     | flex-nya putus di salah satu mata rantai, isinya TIDAK ikut melar:
     | muncul putih sepanjang ~250px di bawah tombol Unduh.
     |
     | Diuji dari sumber karena yang menentukan adalah CSS, dan tidak ada
     | satu pun galat yang muncul saat rantainya putus — halamannya tetap
     | 200, cuma berlubang.
     */
    $gaya = file_get_contents(
        resource_path('views/livewire/pages/admin/pelamar-kerja/pelamar-kerja-detail.blade.php')
    );

    preg_match_all('/<style\b[^>]*>(.*?)<\/style>/si', $gaya, $cocok);
    $css = preg_replace('#/\*.*?\*/#s', '', implode("\n", $cocok[1]));

    // Tiap mata rantai, dari kartu sampai bingkainya.
    expect($css)->toContain('.pld-kartu-berkas { display: flex; flex-direction: column; }')
        ->and($css)->toContain('.pld-kartu-berkas > .dsb-kartu-isi')
        ->and($css)->toContain('.pld-kartu-berkas .pbk ')
        ->and($css)->toContain('.pld-kartu-berkas .pbk-bingkai');

    // Dan kartunya memang memakai kelas itu.
    expect($gaya)->toContain('dsb-kartu k-7 pld-kartu-berkas');
});

it('memberi nama pada tiap tombol aksi di daftar', function () {
    Storage::fake('local');
    lamaranUji(['cv_path' => 'applications/uji/cv.pdf']);

    Livewire::actingAs(adminPelamarLengkap(['akses_asthana', 'view_pelamar', 'edit_pelamar', 'delete_pelamar']))
        ->test(PelamarKerjaList::class)
        ->assertSee('Baca berkas')
        ->assertSee('Buka lamaran');
});
