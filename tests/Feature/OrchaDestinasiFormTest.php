<?php

use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaDestinasiForm;
use App\Livewire\Pages\Admin\Orcha\Etalase\OrchaEtalaseList;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Tambah dan ubah destinasi punya halamannya sendiri.
 *
 * Sebagai jendela, isiannya terus bertambah sampai harus digulung di dalam
 * kotak yang mengambang — dan tidak menyisakan tempat untuk memperlihatkan
 * hasilnya kepada admin sebelum disimpan.
 */
/**
 * Admin ber-hak Orcha, berdiri sendiri.
 *
 * Sengaja tidak memakai helper di berkas uji lain: berkas yang dijalankan
 * sendirian akan merah karena fungsinya tidak ikut termuat, dan uji yang merah
 * karena itu tidak menemukan kesalahan apa pun.
 */
function adminDestinasi(): User
{
    $role = Role::create(['name' => 'uji-dest-'.uniqid(), 'description' => 'uji']);
    $izin = Permission::firstOrCreate(['name' => 'akses_orcha'],
        ['display_name' => 'akses_orcha', 'group' => 'orcha', 'description' => 'uji']);
    $role->permissions()->attach($izin->id);

    $user = User::factory()->create(['role_id' => $role->id]);
    EmployeeDetail::create(['user_id' => $user->id, 'jabatan' => 'Admin', 'nomor_rekening' => '1',
        'tanggal_lahir' => '1995-01-01', 'phone' => '0812', 'alamat' => 'Yogyakarta']);

    return $user->fresh();
}

function fakeDestinasiSatuan(array $baris = []): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    $isi = array_merge([
        'id' => 3, 'nama' => 'Bromo Tengger Semeru', 'wilayah' => 'jawa', 'wilayah_label' => 'Jawa',
        'provinsi' => 'Jawa Timur', 'deskripsi' => 'Lautan pasir dan matahari terbit.',
        'total_pengunjung' => 26700, 'foto' => '/storage/destinasi/utama.webp',
        'sub_foto' => ['/storage/destinasi/tambahan/a.webp', '/storage/destinasi/tambahan/b.webp'],
        'batas_sub_foto' => 3,
    ], $baris);

    Http::fake([
        '*/rujukan' => Http::response(['data' => ['wilayah' => ['jawa' => 'Jawa', 'bali' => 'Bali']]]),
        '*/destinasi/3' => Http::response(['data' => $isi]),
        '*' => Http::response(['data' => [$isi], 'meta' => ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 1]]),
    ]);
}

test('halaman ubah memuat destinasi beserta gambar tambahannya', function () {
    fakeDestinasiSatuan();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->assertSet('ubah', true)
        ->assertSet('nama', 'Bromo Tengger Semeru')
        ->assertSet('provinsi', 'Jawa Timur')
        ->assertSet('subFotoTetap', ['/storage/destinasi/tambahan/a.webp', '/storage/destinasi/tambahan/b.webp'])
        ->assertSet('batasSubFoto', 3);
});

test('daftar destinasi mengarahkan ke halaman, bukan membuka jendela', function () {
    fakeDestinasiSatuan();

    // Pengalihan dipasang di komponennya, bukan hanya pada tombolnya:
    // pemanggilan yang tertinggal akan membuka jendela setengah jadi yang tidak
    // mengenal gambar tambahan.
    Livewire::actingAs(adminDestinasi())->test(OrchaEtalaseList::class)
        ->call('tambah')
        ->assertRedirect(route('admin.orcha.destinasi.tambah'))
        ->assertSet('formTerbuka', false);

    Livewire::actingAs(adminDestinasi())->test(OrchaEtalaseList::class)
        ->call('ubah', ['id' => 3, 'nama' => 'Bromo'])
        ->assertRedirect(route('admin.orcha.destinasi.ubah', 3))
        ->assertSet('formTerbuka', false);
});

test('menghapus satu gambar tambahan hanya mengeluarkannya dari daftar', function () {
    fakeDestinasiSatuan();

    // Berkasnya baru dibuang di Orcha saat disimpan, jadi meninggalkan halaman
    // tanpa menyimpan tidak menghilangkan apa pun.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->call('hapusSubFoto', '/storage/destinasi/tambahan/a.webp')
        ->assertSet('subFotoTetap', ['/storage/destinasi/tambahan/b.webp']);

    Http::assertNotSent(fn ($p) => $p->method() === 'DELETE');
});

test('daftar gambar yang dipertahankan ikut terkirim saat menyimpan', function () {
    fakeDestinasiSatuan();

    // Selalu dikirim, termasuk saat kosong: daftar kosong berarti "semua gambar
    // tambahan dihapus", dan itu keputusan yang harus bisa dinyatakan.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->call('hapusSubFoto', '/storage/destinasi/tambahan/a.webp')
        ->call('hapusSubFoto', '/storage/destinasi/tambahan/b.webp')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/destinasi/3')
        && ($p->data()['sub_foto_tetap'] ?? null) === []);
});

test('batas gambar tambahan dijaga sebelum dikirim', function () {
    fakeDestinasiSatuan(['sub_foto' => ['/storage/a.webp', '/storage/b.webp', '/storage/c.webp']]);

    // Menyerahkan penjagaannya ke Orcha berarti admin baru tahu setelah menunggu
    // unggahan selesai — padahal jumlahnya sudah bisa dihitung di sini.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->set('subFoto', [UploadedFile::fake()->image('d.jpg')])
        ->call('simpan')
        ->assertHasErrors('subFoto');
});

test('sisa tempat menghitung yang tersimpan dan yang baru dipilih', function () {
    fakeDestinasiSatuan(['sub_foto' => ['/storage/a.webp']]);

    $uji = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3]);

    expect($uji->instance()->sisaSubFoto())->toBe(2);

    $uji->set('subFoto', [UploadedFile::fake()->image('b.jpg')]);

    expect($uji->instance()->sisaSubFoto())->toBe(1);
});

test('nama destinasi wajib diisi', function () {
    fakeDestinasiSatuan();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('simpan')
        ->assertHasErrors('nama');
});

test('pratinjau memperlihatkan kartu yang akan dilihat pengunjung', function () {
    fakeDestinasiSatuan();

    // Tanpa pratinjau, kolom kanan hanya berisi tombol dan admin baru tahu
    // hasilnya setelah membuka website di tab lain.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->assertSee('Pratinjau di Website')
        ->assertSee('Bromo Tengger Semeru')
        ->assertSee('Lautan pasir dan matahari terbit.')
        ->assertSee('26,7k pengunjung');
});

test('halaman destinasi memakai kolom lengket yang sama dengan armada', function () {
    fakeDestinasiSatuan();

    // Aturannya diambil dari gaya bersama. Menyalin nama kelasnya saja membuat
    // halaman ini mendapatkan namanya tanpa perilakunya — terukur di peramban:
    // position-nya kembali static dan tombol Simpan ikut tergulung hilang.
    $berkas = file_get_contents(base_path('resources/views/livewire/pages/admin/orcha/etalase/destinasi-form.blade.php'));
    $gaya = file_get_contents(base_path('resources/views/livewire/pages/admin/orcha/partials/gaya.blade.php'));

    expect($berkas)->toContain('orcha-lengket orcha-lengket-panjang')
        ->and($berkas)->toContain('orcha-aksi-paku')
        ->and($gaya)->toContain('.orcha-lengket-panjang .orcha-aksi-paku');

    // Palang tombol harus jadi anak langsung pembungkusnya: elemen sticky tidak
    // bisa keluar dari kotak induknya.
    $pembungkus = strpos($berkas, 'orcha-lengket orcha-lengket-panjang');
    $palang = strpos($berkas, '<div class="orcha-aksi-paku">');
    $kartuTerakhir = strrpos(substr($berkas, 0, $palang), '</div>');

    expect($palang)->toBeGreaterThan($pembungkus)
        ->and($kartuTerakhir)->toBeLessThan($palang);
});

/* ---------- PROVINSI MENENTUKAN WILAYAH ---------- */

function fakeProvinsi(array $baris = []): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'wilayah' => ['jawa' => 'Jawa', 'bali_nusa' => 'Bali & Nusa Tenggara', 'sumatera' => 'Sumatera'],
            'provinsi_wilayah' => [
                'Jawa Timur' => 'jawa',
                'Bali' => 'bali_nusa',
                'Aceh' => 'sumatera',
            ],
        ]]),
        '*/destinasi/3' => Http::response(['data' => array_merge([
            'id' => 3, 'nama' => 'Bromo', 'wilayah' => 'bali_nusa', 'provinsi' => 'Jawa Timur',
            'deskripsi' => '', 'total_pengunjung' => 0, 'foto' => null,
            'sub_foto' => [], 'batas_sub_foto' => 3,
        ], $baris)]),
        '*' => Http::response(['data' => []]),
    ]);
}

test('memilih provinsi mengisi wilayahnya sendiri', function () {
    fakeProvinsi();

    // "Jawa Timur" yang tercatat di wilayah "Bali & Nusa Tenggara" tidak akan
    // pernah ketahuan sampai ada pengunjung yang menyaring dan tidak
    // menemukannya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'jawa')
        ->set('provinsi', 'Bali')
        ->assertSet('wilayah', 'bali_nusa');
});

test('wilayah yang diubah sendiri tidak ditimpa lagi', function () {
    fakeProvinsi();

    // Ada destinasi yang memang dipasarkan di wilayah tetangganya, dan sistem
    // tidak berhak membatalkan keputusan itu diam-diam.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('wilayah', 'sumatera')
        ->set('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'sumatera');
});

test('provinsi di luar daftar tidak mengubah wilayah', function () {
    fakeProvinsi();

    // Daftarnya boleh dilampaui — yang tidak boleh, menebak wilayahnya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('wilayah', 'jawa')
        ->set('provinsi', 'Timor Leste')
        ->assertSet('wilayah', 'jawa');
});

test('destinasi tersimpan membawa wilayahnya sendiri', function () {
    fakeProvinsi();

    // Menyalakan penyesuaian otomatis saat memuat akan menimpa penempatan yang
    // mungkin sengaja dibedakan, begitu admin menyentuh provinsinya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->assertSet('wilayah', 'bali_nusa')
        ->set('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'bali_nusa');
});

test('daftar provinsi datang dari orcha, bukan disalin', function () {
    fakeProvinsi();

    // Disalin ke sini berarti dua daftar yang bisa berbeda diam-diam saat ada
    // provinsi baru dimekarkan.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->assertSee('Jawa Timur')
        ->assertSee('daftar-provinsi');

    $berkas = file_get_contents(base_path('resources/views/livewire/pages/admin/orcha/etalase/destinasi-form.blade.php'));

    expect($berkas)->not->toContain('Papua Pegunungan');
});
