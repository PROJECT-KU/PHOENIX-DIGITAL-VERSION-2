<?php

use App\Livewire\Pages\Admin\Surat\SuratForm;
use App\Models\Surat\Surat;
use App\Models\Surat\SuratKlasifikasi;
use App\Support\Surat\NomorSuratKeluar;
use Livewire\Livewire;

/**
 * Nomor surat keluar: 016/03.D/ACM/VI/2026
 *
 * Yang dijaga di sini adalah hal yang kalau salah tidak menimbulkan galat
 * apa pun, hanya dua surat resmi bernomor sama — dan itu baru ketahuan
 * berbulan-bulan kemudian, saat seseorang mencari satu nomor dan menemukan
 * dua surat yang berbeda.
 *
 * Penolongnya bernama sendiri (berakhiran -Nomor) karena penolong Pest
 * tinggal di ruang nama GLOBAL: nama yang sama di dua berkas uji membuat
 * rangkaian penuh mati, sementara menjalankan satu berkas saja tetap hijau.
 */
function klasNomor(string $kode, string $nama = 'Klasifikasi uji'): SuratKlasifikasi
{
    return SuratKlasifikasi::create([
        'kode' => $kode,
        'nama' => $nama,
        'retensi_tahun' => 5,
        'aktif' => true,
        'urutan' => 10,
    ]);
}

function suratKeluarNomor(array $ubah = []): Surat
{
    return Surat::create(array_merge([
        'arah' => 'keluar',
        'perihal' => 'Surat keluar uji',
        'tanggal_surat' => '2026-06-13',
        'kepada' => 'PT Mitra',
        'sifat' => 'biasa',
        'status' => 'baru',
    ], $ubah));
}

function adminNomorSurat(): \App\Models\User
{
    $role = \App\Models\Role::create(['name' => 'uji-nomor-'.uniqid(), 'description' => 'Peran uji nomor surat']);

    foreach (['akses_arsip_surat', 'view_surat', 'create_surat', 'edit_surat'] as $nama) {
        $izin = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'arsip_surat', 'description' => 'uji']
        );
        $role->permissions()->attach($izin->id);
    }

    $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Staf Administrasi Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Banyuwangi',
    ]);

    return $user;
}

/* ============================== BENTUK NOMOR ============================== */

it('merakit nomor surat keluar pertama dalam setahun', function () {
    $d = klasNomor('D', 'Dokumen Internal / Kerahasiaan');

    $hasil = NomorSuratKeluar::usulkan('2026-06-13', $d->id);

    expect($hasil['nomor'])->toBe('001/01.D/ACM/VI/2026');
});

it('memakai bulan terbit surat dalam angka Romawi, bukan bulan pencatatan', function () {
    $d = klasNomor('D');

    // Hari ini November; suratnya tertanggal Juni. Yang tercetak harus VI.
    $this->travelTo('2026-11-20');

    expect(NomorSuratKeluar::usulkan('2026-06-13', $d->id)['nomor'])->toContain('/ACM/VI/2026');
    expect(NomorSuratKeluar::usulkan('2026-07-01', $d->id)['nomor'])->toContain('/ACM/VII/2026');
    expect(NomorSuratKeluar::usulkan('2026-01-05', $d->id)['nomor'])->toContain('/ACM/I/2026');
    expect(NomorSuratKeluar::usulkan('2026-12-31', $d->id)['nomor'])->toContain('/ACM/XII/2026');
});

it('menghilangkan bagian klasifikasi bila suratnya belum diklasifikasi', function () {
    expect(NomorSuratKeluar::usulkan('2026-06-13', null)['nomor'])->toBe('001/ACM/VI/2026');
});

/* ============================== DUA HITUNGAN ============================== */

it('menaikkan nomor urut utama walau klasifikasinya berbeda', function () {
    $d = klasNomor('D', 'Dokumen Internal');
    $b = klasNomor('B', 'Kerja Sama');

    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    // Surat berikutnya berklasifikasi B — nomor depannya tetap naik ke 017.
    expect(NomorSuratKeluar::usulkan('2026-07-01', $b->id)['nomor'])->toStartWith('017/');
});

it('menyambung hitungan klasifikasi dari surat berklasifikasi sama saja', function () {
    $d = klasNomor('D', 'Dokumen Internal');
    $b = klasNomor('B', 'Kerja Sama');

    // Persis urutan yang ada di buku agenda ACM.
    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);
    suratKeluarNomor(['nomor_surat' => '017/10.B/ACM/VII/2026', 'klasifikasi_id' => $b->id, 'tanggal_surat' => '2026-07-01']);

    // Surat D berikutnya: 018 di depan (ikut semua surat), 04 di tengah
    // (hanya ikut surat D) — sepuluh surat B di antaranya tidak menggeser.
    expect(NomorSuratKeluar::usulkan('2026-07-08', $d->id)['nomor'])->toBe('018/04.D/ACM/VII/2026');
});

it('memulai hitungan dari satu lagi pada tahun berikutnya', function () {
    $d = klasNomor('D');

    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    expect(NomorSuratKeluar::usulkan('2027-01-04', $d->id)['nomor'])->toBe('001/01.D/ACM/I/2027');
});

it('tidak menggeser hitungan surat keluar karena surat masuk', function () {
    $d = klasNomor('D');

    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    // Surat masuk bernomor mirip dari instansi lain.
    Surat::create([
        'arah' => 'masuk',
        'perihal' => 'Surat dari dinas',
        'tanggal_surat' => '2026-06-20',
        'nomor_surat' => '900/99.D/DPK/VI/2026',
        'klasifikasi_id' => $d->id,
        'sifat' => 'biasa',
        'status' => 'baru',
    ]);

    expect(NomorSuratKeluar::usulkan('2026-07-01', $d->id)['nomor'])->toBe('017/04.D/ACM/VII/2026');
});

it('tetap memegang nomor surat yang sudah dihapus', function () {
    $d = klasNomor('D');

    $surat = suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);
    $surat->delete();

    // Nomor 016 sudah beredar di luar kantor; ia tidak boleh diberikan lagi.
    expect(NomorSuratKeluar::usulkan('2026-07-01', $d->id)['nomor'])->toStartWith('017/');
});

/* ========================= NOMOR YANG DIKETIK MANUAL ========================= */

it('menyambung rantai dari nomor yang diketik manual', function () {
    $d = klasNomor('D');

    // Admin mengetik sendiri, melompat jauh karena mengikuti kertasnya.
    $surat = suratKeluarNomor(['nomor_surat' => '042/09.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    expect($surat->fresh()->urut_utama)->toBe(42)
        ->and($surat->fresh()->urut_klasifikasi)->toBe(9)
        ->and($surat->fresh()->kode_klasifikasi)->toBe('D');

    expect(NomorSuratKeluar::usulkan('2026-07-01', $d->id)['nomor'])->toBe('043/10.D/ACM/VII/2026');
});

it('membaca nomor yang ditulis tanpa angka nol di depan', function () {
    expect(NomorSuratKeluar::urai('16/3.D/ACM/VI/2026'))
        ->toBe(['urut_utama' => 16, 'urut_klasifikasi' => 3, 'kode_klasifikasi' => 'D']);
});

it('tidak memaksakan hitungan dari nomor yang bentuknya lain sama sekali', function () {
    expect(NomorSuratKeluar::urai('ACM/SK/VI/2026'))->toBeNull();

    $surat = suratKeluarNomor(['nomor_surat' => 'ACM/SK/VI/2026']);

    expect($surat->fresh()->urut_utama)->toBeNull();
});

it('menaikkan usulan sampai nomornya belum terpakai', function () {
    $d = klasNomor('D');

    // Nomor 001 sudah dipakai, tetapi hitungan urutnya tidak terbaca dari
    // bentuk nomor itu — usulan tetap tidak boleh kembar dengannya.
    suratKeluarNomor(['nomor_surat' => '001/01.D/ACM/VI/2026', 'klasifikasi_id' => $d->id, 'urut_utama' => null]);
    Surat::query()->update(['urut_utama' => null, 'urut_klasifikasi' => null, 'kode_klasifikasi' => null]);

    expect(NomorSuratKeluar::usulkan('2026-06-13', $d->id)['nomor'])->toBe('002/02.D/ACM/VI/2026');
});

/* ============================== BORANG ============================== */

it('mengisikan nomor usulan begitu borang surat keluar dibuka', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->assertSet('nomorOtomatis', true)
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->assertSet('f.nomor_surat', '001/01.D/ACM/VI/2026');
});

it('mengubah nomor usulan saat klasifikasinya diganti', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');
    $b = klasNomor('B');

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->assertSet('f.nomor_surat', '001/01.D/ACM/VI/2026')
        ->set('f.klasifikasi_id', (string) $b->id)
        ->assertSet('f.nomor_surat', '001/01.B/ACM/VI/2026');
});

it('tidak pernah mengusulkan nomor untuk surat masuk', function () {
    $this->actingAs(adminNomorSurat());

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.tanggal_surat', '2026-06-13')
        ->assertSet('f.nomor_surat', '001/ACM/VI/2026')
        ->set('f.arah', 'masuk')
        ->assertSet('f.nomor_surat', '');
});

it('menyimpan nomor yang diketik manual apa adanya', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('nomorOtomatis', false)
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->set('f.perihal', 'Penawaran kerja sama')
        ->set('f.nomor_surat', '099/12.D/ACM/VI/2026')
        ->call('save');

    expect(Surat::where('arah', 'keluar')->value('nomor_surat'))->toBe('099/12.D/ACM/VI/2026');
});

it('menolak nomor surat keluar yang kembar', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('nomorOtomatis', false)
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.perihal', 'Penawaran kerja sama')
        ->set('f.nomor_surat', '016/03.D/ACM/VI/2026')
        ->call('save')
        ->assertHasErrors('f.nomor_surat');

    expect(Surat::where('arah', 'keluar')->count())->toBe(1);
});

it('merakit ulang nomornya tepat sebelum disimpan', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    $borang = Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->set('f.perihal', 'Penawaran kerja sama');

    // Admin lain mengeluarkan dua surat selagi borang ini terbuka.
    suratKeluarNomor(['nomor_surat' => '001/01.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);
    suratKeluarNomor(['nomor_surat' => '002/02.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    $borang->call('save');

    expect(Surat::where('perihal', 'Penawaran kerja sama')->value('nomor_surat'))
        ->toBe('003/03.D/ACM/VI/2026');
});

it('tidak mengganti nomor surat lama saat layar ubah dibuka', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    $surat = suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    Livewire::test(SuratForm::class, ['surat' => $surat->id])
        ->assertSet('nomorOtomatis', false)
        ->assertSet('f.nomor_surat', '016/03.D/ACM/VI/2026')
        ->call('save');

    expect($surat->fresh()->nomor_surat)->toBe('016/03.D/ACM/VI/2026');
});

it('menghitung ulang nomor hanya ketika tombolnya ditekan', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    suratKeluarNomor(['nomor_surat' => '016/03.D/ACM/VI/2026', 'klasifikasi_id' => $d->id]);

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('nomorOtomatis', false)
        ->set('f.nomor_surat', 'ditulis tangan')
        ->set('f.tanggal_surat', '2026-07-01')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->assertSet('f.nomor_surat', 'ditulis tangan')
        ->call('pakaiNomorUsulan')
        ->assertSet('f.nomor_surat', '017/04.D/ACM/VII/2026');
});

/* ============================== GAMBARAN LAYAR ============================== */

it('menggambar centang otomatis dalam keadaan tercentang', function () {
    $this->actingAs(adminNomorSurat());

    /*
     | Memeriksa HTML, bukan propertinya.
     |
     | wire:model TIDAK pernah menulis atribut `checked` sendiri. Memeriksa
     | $nomorOtomatis akan SELALU hijau — propertinya memang benar; yang
     | salah penggambarannya, dan layar yang menggambar centang kosong
     | membuat admin mengira nomornya tidak akan dibuatkan lalu mengetiknya
     | sendiri di atas nomor yang sudah benar.
     */
    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->assertSeeHtml('wire:model.live="nomorOtomatis" checked>');
});

it('menerangkan arti tiap potongan nomor di borang', function () {
    $this->actingAs(adminNomorSurat());
    $d = klasNomor('D');

    Livewire::test(SuratForm::class, ['arah' => 'keluar'])
        ->set('f.tanggal_surat', '2026-06-13')
        ->set('f.klasifikasi_id', (string) $d->id)
        ->assertSee('Urut surat keluar tahun ini')
        ->assertSee('Urut klasifikasinya')
        ->assertSee('Bulan terbit');
});
