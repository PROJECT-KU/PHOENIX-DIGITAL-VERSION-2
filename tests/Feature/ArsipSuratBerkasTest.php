<?php

use App\Livewire\Pages\Admin\Surat\SuratDetail;
use App\Livewire\Pages\Admin\Surat\SuratForm;
use App\Models\Surat\Surat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Pindaian surat: beberapa lembar sekaligus, dan boleh memilih berkali-kali.
 *
 * Yang dijaga di sini adalah perangkap <input type="file">: kotaknya selalu
 * MENGGANTI isinya, jadi memilih lembar kedua membuang lembar pertama tanpa
 * pesan apa pun. Surat berlembar-lembar justru selalu dipilih beberapa kali,
 * dan lembar yang hilang diam-diam tidak akan ketahuan sampai arsipnya
 * dibuka kembali bertahun-tahun kemudian.
 *
 * Penolongnya bernama sendiri (berakhiran -Berkas) karena penolong Pest
 * tinggal di ruang nama GLOBAL.
 */
function adminBerkasSurat(): \App\Models\User
{
    $role = \App\Models\Role::create(['name' => 'uji-berkas-'.uniqid(), 'description' => 'Peran uji berkas surat']);

    foreach (['akses_arsip_surat', 'view_surat', 'create_surat', 'edit_surat', 'delete_surat'] as $nama) {
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

function pindaianUji(string $nama, int $kb = 120): UploadedFile
{
    return UploadedFile::fake()->create($nama, $kb, 'application/pdf');
}

function suratBerkasUji(): Surat
{
    return Surat::create([
        'arah' => 'masuk',
        'perihal' => 'Surat berlampiran',
        'tanggal_surat' => '2026-11-01',
        'dari' => 'Dinas Pendidikan',
        'sifat' => 'biasa',
        'status' => 'baru',
    ]);
}

beforeEach(function () {
    Storage::fake('local');
    $this->actingAs(adminBerkasSurat());
});

/* ============================== CATAT SURAT ============================== */

it('menyimpan beberapa pindaian yang dipilih sekaligus', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Surat tiga lembar')
        ->set('f.tanggal_surat', '2026-11-01')
        ->set('unggahan', [pindaianUji('lembar-1.pdf'), pindaianUji('lembar-2.pdf'), pindaianUji('lembar-3.pdf')])
        ->assertCount('berkas', 3)
        ->call('save');

    $surat = Surat::where('perihal', 'Surat tiga lembar')->firstOrFail();

    expect($surat->lampiran()->count())->toBe(3)
        ->and($surat->lampiran()->pluck('nama')->sort()->values()->all())
        ->toBe(['lembar-1.pdf', 'lembar-2.pdf', 'lembar-3.pdf']);
});

it('menambah antrean saat memilih berkas lagi, bukan menggantikannya', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Surat dipindai dua kali')
        ->set('f.tanggal_surat', '2026-11-01')
        ->set('unggahan', [pindaianUji('lembar-1.pdf'), pindaianUji('lembar-2.pdf')])
        ->assertCount('berkas', 2)
        // Pilihan kedua: kotaknya sudah kosong lagi, antreannya tidak.
        ->assertSet('unggahan', [])
        ->set('unggahan', [pindaianUji('lembar-3.pdf')])
        ->assertCount('berkas', 3)
        ->call('save');

    expect(Surat::where('perihal', 'Surat dipindai dua kali')->firstOrFail()->lampiran()->count())->toBe(3);
});

it('menyebut nama tiap berkas di antrean, bukan hanya jumlahnya', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('unggahan', [pindaianUji('surat-dinas-hal-1.pdf'), pindaianUji('surat-dinas-hal-2.pdf')])
        ->assertSee('surat-dinas-hal-1.pdf')
        ->assertSee('surat-dinas-hal-2.pdf');
});

it('mengeluarkan satu berkas dari antrean tanpa menyentuh yang lain', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('f.perihal', 'Surat salah pilih')
        ->set('f.tanggal_surat', '2026-11-01')
        ->set('unggahan', [pindaianUji('benar-1.pdf'), pindaianUji('salah.pdf'), pindaianUji('benar-2.pdf')])
        ->call('hapusAntrean', 1)
        ->assertCount('berkas', 2)
        ->assertDontSee('salah.pdf')
        ->call('save');

    expect(Surat::where('perihal', 'Surat salah pilih')->firstOrFail()
        ->lampiran()->pluck('nama')->sort()->values()->all())
        ->toBe(['benar-1.pdf', 'benar-2.pdf']);
});

it('menolak berkas yang melebihi 20 MB tanpa membuang antrean yang sudah benar', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('unggahan', [pindaianUji('kecil.pdf')])
        ->assertCount('berkas', 1)
        ->set('unggahan', [pindaianUji('raksasa.pdf', 20481)])
        ->assertHasErrors('unggahan.0')
        ->assertCount('berkas', 1);
});

it('menolak berkas yang jenisnya bukan pindaian surat', function () {
    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('unggahan', [UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream')])
        ->assertHasErrors('unggahan.0')
        ->assertCount('berkas', 0);
});

it('membatasi antrean sekali catat dan mengatakan sebabnya', function () {
    $banyak = [];

    for ($i = 1; $i <= 21; $i++) {
        $banyak[] = pindaianUji('lembar-'.$i.'.pdf', 10);
    }

    Livewire::test(SuratForm::class, ['arah' => 'masuk'])
        ->set('unggahan', $banyak)
        ->assertCount('berkas', 20)
        ->assertHasErrors('unggahan');
});

/* ============================== HALAMAN DETAIL ============================== */

it('menambah beberapa pindaian sekaligus dari halaman suratnya', function () {
    $surat = suratBerkasUji();

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('unggahan', [pindaianUji('tambahan-1.pdf'), pindaianUji('tambahan-2.pdf')])
        ->assertCount('berkasBaru', 2)
        ->call('unggahBerkas');

    expect($surat->lampiran()->count())->toBe(2);
});

it('menumpuk pilihan berkas di halaman detail, bukan menggantikannya', function () {
    $surat = suratBerkasUji();

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('unggahan', [pindaianUji('tambahan-1.pdf')])
        ->set('unggahan', [pindaianUji('tambahan-2.pdf'), pindaianUji('tambahan-3.pdf')])
        ->assertCount('berkasBaru', 3)
        ->call('unggahBerkas');

    expect($surat->lampiran()->count())->toBe(3)
        ->and($surat->lampiran()->pluck('nama')->sort()->values()->all())
        ->toBe(['tambahan-1.pdf', 'tambahan-2.pdf', 'tambahan-3.pdf']);
});

it('mengosongkan antrean setelah pindaiannya tersimpan', function () {
    $surat = suratBerkasUji();

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('unggahan', [pindaianUji('tambahan-1.pdf')])
        ->call('unggahBerkas')
        ->assertCount('berkasBaru', 0)
        ->assertSet('unggahan', []);

    // Tanpa pengosongan, menekan simpan dua kali akan menggandakan lampirannya.
    expect($surat->lampiran()->count())->toBe(1);
});

it('menyimpan ukuran tiap lampiran, bukan hanya namanya', function () {
    $surat = suratBerkasUji();

    Livewire::test(SuratDetail::class, ['surat' => $surat->id])
        ->set('unggahan', [pindaianUji('berukuran.pdf', 250)])
        ->call('unggahBerkas');

    expect((int) $surat->lampiran()->value('ukuran'))->toBeGreaterThan(0);
});
