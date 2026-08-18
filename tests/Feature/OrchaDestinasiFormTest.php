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

test('memilih wilayah menyaring pilihan provinsinya', function () {
    fakeProvinsi();

    // Merek menyaring nama unit di formulir armada; wilayah menyaring provinsi
    // di sini. Menampilkan seluruh 38 provinsi setelah wilayahnya dipilih
    // membuat admin menyaring sendiri di kepala.
    $uji = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('wilayah', 'jawa');

    expect($uji->instance()->provinsiTersedia())->toBe(['Jawa Timur']);

    // Diperiksa juga pada HTML yang benar-benar dikirim: pemilihnya menyaring
    // di sisi peramban memakai wilayah yang sedang dipilih, jadi nilai itu harus
    // ikut terkirim dan ikut berubah.
    // Diperiksa pada HTML yang benar-benar dikirim. Wilayahnya ditempel sebagai
    // atribut DOM, BUKAN ditulis di dalam <script>: Livewire tidak menjalankan
    // ulang script inline saat me-render ulang, jadi nilai di sana membeku pada
    // pemuatan pertama dan mengganti wilayah tidak mengubah daftar provinsi
    // sama sekali.
    expect($uji->html())->toContain('data-orcha-wilayah="jawa"');

    $uji->set('wilayah', 'sumatera');

    expect($uji->instance()->provinsiTersedia())->toBe(['Aceh'])
        ->and($uji->html())->toContain('data-orcha-wilayah="sumatera"');
});

test('wilayah dan provinsi sama-sama punya jalur tulis sendiri', function () {
    fakeProvinsi();

    // Dulu wilayah sengaja tidak bisa ditambah karena tab penyaring di halaman
    // publik dibaca dari config. Sesudah halaman publik ikut membaca daftar
    // gabungan, alasannya hilang: wilayah baru langsung punya tabnya sendiri.
    $html = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)->html();

    expect($html)->toContain('orchaPilihWilayah')
        ->and($html)->toContain('orchaWilManual')
        ->and($html)->toContain('orchaProvManual');
});

test('wilayah yang ditulis sendiri langsung terdaftar dan terpilih', function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    // Http::fake() yang dipanggil dua kali MENGGABUNGKAN stubnya, dan yang
    // terdaftar lebih dulu yang menang — jadi fakeProvinsi() tidak boleh
    // dipakai di sini, kalau tidak daftar rujukan yang baru tidak akan pernah
    // terbaca dan yang teruji justru stub lamanya.
    Http::fake([
        '*/rujukan' => Http::sequence()
            ->push(['data' => [
                'wilayah' => ['jawa' => 'Jawa'],
                'provinsi_wilayah' => ['Jawa Timur' => 'jawa'],
                'provinsi_kustom' => [], 'wilayah_kustom' => [],
            ]])
            ->whenEmpty(Http::response(['data' => [
                'wilayah' => ['jawa' => 'Jawa', 'jalur_rempah' => 'Jalur Rempah'],
                'provinsi_wilayah' => ['Jawa Timur' => 'jawa'],
                'provinsi_kustom' => [],
                'wilayah_kustom' => [['id' => 4, 'kunci' => 'jalur_rempah', 'label' => 'Jalur Rempah']],
            ]])),
        '*/wilayah' => Http::response(['pesan' => 'Jalur Rempah ditambahkan.'], 201),
        '*' => Http::response(['data' => []]),
    ]);

    // Kuncinya dibaca dari daftar terbaru, bukan ditebak di lemon: kunci yang
    // dibuat dua tempat berbeda cepat atau lambat akan berbeda bentuknya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('tambahWilayah', 'Jalur Rempah')
        ->assertSet('wilayah', 'jalur_rempah')
        ->assertDispatched('orcha-wilayah-segar');
});

test('menghapus wilayah meneruskan penolakan orcha apa adanya', function () {
    fakeProvinsi();

    // Orcha menolak menghapus wilayah yang masih dipakai destinasi. Pesannya
    // diteruskan supaya admin tahu harus berbuat apa, bukan sekadar "gagal".
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('hapusWilayah', 4)
        ->assertDispatched('orcha-wilayah-segar');

    Http::assertSent(fn ($p) => $p->method() === 'DELETE' && str_contains($p->url(), '/wilayah/4'));
});

test('provinsi yang tidak cocok dikosongkan saat wilayah diganti', function () {
    fakeProvinsi();

    // Membiarkannya berarti kartu destinasi menyatakan dua hal yang
    // bertentangan, dan yang salah baru ketahuan saat ada pengunjung menyaring
    // dan tidak menemukannya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'jawa')
        ->set('wilayah', 'sumatera')
        ->assertSet('provinsi', '');
});

test('provinsi yang masih cocok tidak ikut dikosongkan', function () {
    fakeProvinsi();

    // Wilayah yang dipilih ulang ke nilai yang sama tidak boleh menghapus
    // pekerjaan yang sudah benar.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Bali')
        ->set('wilayah', 'bali_nusa')
        ->assertSet('provinsi', 'Bali');
});

test('provinsi di luar daftar tidak mengubah wilayah', function () {
    fakeProvinsi();

    // Daftarnya boleh dilampaui — yang tidak boleh, menebak wilayahnya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('wilayah', 'jawa')
        ->set('provinsi', 'Timor Leste')
        ->assertSet('wilayah', 'jawa');
});

test('memuat destinasi tidak mengubah apa pun sampai admin menyunting', function () {
    fakeProvinsi();

    // Data tersimpan boleh saja tidak cocok — itu justru yang mau diperbaiki.
    // Yang tidak boleh: memperbaikinya diam-diam saat halaman dibuka, sehingga
    // admin menyimpan perubahan yang tidak pernah ia buat.
    $uji = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class, ['destinasi' => 3])
        ->assertSet('wilayah', 'bali_nusa')
        ->assertSet('provinsi', 'Jawa Timur');

    // Begitu provinsinya disunting, wilayahnya ikut dibetulkan.
    $uji->set('provinsi', 'Jawa Timur')->assertSet('wilayah', 'jawa');
});

test('daftar provinsi datang dari orcha, bukan disalin', function () {
    fakeProvinsi();

    // Disalin ke sini berarti dua daftar yang bisa berbeda diam-diam saat ada
    // provinsi baru dimekarkan.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->assertSee('Jawa Timur')
        ->assertSee('orchaPilihProvinsi');

    $berkas = file_get_contents(base_path('resources/views/livewire/pages/admin/orcha/etalase/destinasi-form.blade.php'));

    expect($berkas)->not->toContain('Papua Pegunungan');
});

/* ---------- MENAMBAH PROVINSI SENDIRI ---------- */

test('provinsi yang ditulis sendiri langsung terdaftar dan terpilih', function () {
    fakeProvinsi();

    // Sekali ditulis langsung terdaftar — bukan hanya mengisi isian lalu hilang
    // saat halaman ditutup.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('wilayah', 'jawa')
        ->call('tambahProvinsi', 'Jawa Tenggara')
        ->assertSet('provinsi', 'Jawa Tenggara')
        ->assertDispatched('orcha-provinsi-segar');

    // Wilayah yang sedang dipilih ikut terkirim: provinsi tanpa wilayah tidak
    // masuk penyaring mana pun di halaman publik.
    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/provinsi')
        && ($p->data()['nama'] ?? null) === 'Jawa Tenggara'
        && ($p->data()['wilayah'] ?? null) === 'jawa');
});

test('nama kosong tidak dikirim ke orcha', function () {
    fakeProvinsi();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('tambahProvinsi', '   ');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), '/provinsi')
        && ! str_contains($p->url(), 'rujukan'));
});

test('menghapus provinsi tambahan menyegarkan daftarnya', function () {
    fakeProvinsi();

    // Tanpa membuang simpanan rujukan, daftar yang baru saja diubah baru
    // terlihat sepuluh menit kemudian — dan admin mengira perubahannya gagal.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('hapusProvinsi', 7)
        ->assertDispatched('orcha-provinsi-segar');

    Http::assertSent(fn ($p) => $p->method() === 'DELETE'
        && str_contains($p->url(), '/provinsi/7'));
});

/* ---------- NAMA MENGUSULKAN PROVINSI & WILAYAH ---------- */

function fakeUsulan(?array $usulan): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'wilayah' => ['jawa' => 'Jawa', 'bali_nusa' => 'Bali & Nusa Tenggara', 'sumatera' => 'Sumatera'],
            'provinsi_wilayah' => ['Jawa Timur' => 'jawa', 'Bali' => 'bali_nusa', 'Aceh' => 'sumatera'],
            'provinsi_kustom' => [],
        ]]),
        '*/cari-lokasi*' => Http::response(['data' => $usulan]),
        '*' => Http::response(['data' => []]),
    ]);
}

test('mengetik nama destinasi mengisi provinsi dan wilayahnya', function () {
    fakeUsulan(['provinsi' => 'Jawa Timur', 'wilayah' => 'jawa', 'sumber' => 'peta']);

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Bromo Tengger Semeru')
        ->assertSet('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'jawa')
        // Asalnya disebut: usulan yang mengisi dua isian tanpa mengatakan
        // apa-apa terasa seperti sistem yang mengubah pekerjaan admin diam-diam.
        ->assertSee('OpenStreetMap');
});

test('usulan tidak menimpa provinsi yang sudah ditulis admin', function () {
    fakeUsulan(['provinsi' => 'Bali', 'wilayah' => 'bali_nusa', 'sumber' => 'peta']);

    // Tebakan tentang nama tempat yang mirip cukup sering meleset; menimpa
    // keputusan admin dengan tebakan adalah cara tercepat kehilangan
    // kepercayaannya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->set('nama', 'Pantai Baru')
        ->assertSet('provinsi', 'Jawa Timur');
});

test('nama terlalu pendek tidak menembak orcha', function () {
    fakeUsulan(['provinsi' => 'Bali', 'wilayah' => 'bali_nusa', 'sumber' => 'peta']);

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)->set('nama', 'Bro');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), 'cari-lokasi'));
});

test('usulan kosong tidak mengubah apa pun dan tidak menampilkan pesan', function () {
    fakeUsulan(null);

    // Usulan yang gagal bukan kegagalan admin: yang benar adalah ia mengisi
    // sendiri seperti biasa.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Tempat Antah Berantah')
        ->assertSet('provinsi', '')
        ->assertSet('usulanLokasi', '')
        ->assertHasNoErrors();
});

test('usulan dari destinasi lain menyebut asalnya sendiri', function () {
    fakeUsulan(['provinsi' => 'Bali', 'wilayah' => 'bali_nusa', 'sumber' => 'destinasi']);

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Pantai Melasti')
        ->assertSee('destinasi lain yang namanya mirip');
});

/* ---------- KATALOG NAMA DESTINASI ---------- */

function fakeKatalog(array $katalog = ['Banyuwangi' => 'Jawa Timur', 'Raja Ampat' => 'Papua Barat Daya']): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'wilayah' => ['jawa' => 'Jawa', 'papua' => 'Papua', 'maluku' => 'Maluku'],
            'wilayah_kustom' => [],
            'provinsi_wilayah' => [
                'Jawa Timur' => 'jawa', 'Papua Barat Daya' => 'papua', 'Maluku' => 'maluku',
            ],
            'provinsi_kustom' => [],
            'katalog_daerah' => ['Ambon' => 'Maluku', 'Banyuwangi' => 'Jawa Timur'],
            'katalog_daerah_kustom' => [],
            'katalog_destinasi' => $katalog,
            'katalog_destinasi_kustom' => [['id' => 5, 'nama' => 'Pantai Rahasia', 'provinsi' => 'Jawa Timur']],
        ]]),
        '*/katalog-destinasi*' => Http::response(['pesan' => 'ditambahkan'], 201),
        '*' => Http::response(['data' => []]),
    ]);
}

test('memilih destinasi dari daftar mengisi nama, provinsi, dan wilayah sekaligus', function () {
    fakeKatalog();

    // Satu tindakan mengisi tiga isian — itu yang membuat daftar ini berguna,
    // bukan namanya (admin sudah tahu namanya).
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('pilihDestinasi', 'Raja Ampat')
        ->assertSet('nama', 'Raja Ampat')
        ->assertSet('provinsi', 'Papua Barat Daya')
        ->assertSet('wilayah', 'papua')
        ->assertSee('terisi dari daftar destinasi');
});

test('memilih destinasi tidak menimpa provinsi yang DIKETIK admin', function () {
    fakeKatalog();

    // Nama tempat yang mirip ada di beberapa provinsi; tebakan tidak berhak
    // mengalahkan keputusan. updatedProvinsi menandai isinya sebagai keputusan
    // admin — bukan sekadar "terisi".
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->call('pilihDestinasi', 'Raja Ampat')
        ->assertSet('nama', 'Raja Ampat')
        ->assertSet('provinsi', 'Jawa Timur');
});

test('berganti destinasi mengganti provinsi dan wilayah yang diisi sistem', function () {
    fakeKatalog(['Ambon' => 'Maluku', 'Banyuwangi' => 'Jawa Timur']);

    // Keluhannya: pilih Ambon lalu ganti ke Banyuwangi, dan provinsinya tetap
    // Maluku. Penjagaan "jangan timpa yang sudah terisi" benar untuk yang
    // diketik admin, tetapi yang terisi di sini adalah bayangan pilihan
    // sebelumnya — dan destinasi tercatat di provinsi yang sama sekali tidak
    // ada hubungannya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('pilihDestinasi', 'Ambon')
        ->assertSet('provinsi', 'Maluku')
        ->assertSet('wilayah', 'maluku')
        ->call('pilihDestinasi', 'Banyuwangi')
        ->assertSet('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'jawa');
});

test('mengetik nama lain juga memperbarui lokasi yang diisi sistem', function () {
    fakeUsulan(['provinsi' => 'Bali', 'wilayah' => 'bali', 'daerah' => 'Badung', 'sumber' => 'peta']);

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Pantai Melasti')
        ->assertSet('provinsi', 'Bali')
        // Nama berganti: lokasinya ikut, karena yang terisi bukan keputusan admin.
        ->set('nama', 'Pantai Lain Lagi')
        ->assertSet('provinsi', 'Bali')
        ->assertSet('wilayah', 'bali');
});

test('daerah dari provinsi lama ikut dibuang saat destinasi berganti', function () {
    fakeKatalog(['Ambon' => 'Maluku', 'Banyuwangi' => 'Jawa Timur']);

    // Dibiarkan, ia akan berbunyi "Ambon, Jawa Timur" — alamat yang tidak
    // pernah ada.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('pilihDestinasi', 'Ambon')
        ->set('daerah', 'Ambon')
        ->call('pilihDestinasi', 'Banyuwangi')
        ->assertSet('daerah', '');
});

test('nama yang ditulis sendiri masuk daftar lalu terpilih', function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    // Http::fake() yang dipanggil dua kali menggabungkan stub dan yang lebih
    // dulu menang — jadi seluruh stub didaftarkan sekali di sini.
    Http::fake([
        '*/rujukan' => Http::sequence()
            ->push(['data' => [
                'wilayah' => ['jawa' => 'Jawa'], 'wilayah_kustom' => [],
                'provinsi_wilayah' => ['Jawa Timur' => 'jawa'], 'provinsi_kustom' => [],
                'katalog_destinasi' => [], 'katalog_destinasi_kustom' => [],
            ]])
            ->whenEmpty(Http::response(['data' => [
                'wilayah' => ['jawa' => 'Jawa'], 'wilayah_kustom' => [],
                'provinsi_wilayah' => ['Jawa Timur' => 'jawa'], 'provinsi_kustom' => [],
                'katalog_destinasi' => ['Pantai Pulau Merah' => 'Jawa Timur'],
                'katalog_destinasi_kustom' => [['id' => 6, 'nama' => 'Pantai Pulau Merah', 'provinsi' => 'Jawa Timur']],
            ]])),
        '*/katalog-destinasi' => Http::response(['pesan' => 'ditambahkan'], 201),
        '*' => Http::response(['data' => []]),
    ]);

    // Provinsinya dicari Orcha sendiri, jadi sekali tulis pun tiga isian terisi.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('tambahDestinasi', 'Pantai Pulau Merah')
        ->assertSet('nama', 'Pantai Pulau Merah')
        ->assertSet('provinsi', 'Jawa Timur')
        ->assertSet('wilayah', 'jawa')
        ->assertDispatched('orcha-katalog-destinasi-segar');
});

test('nama destinasi kosong tidak dikirim ke orcha', function () {
    fakeKatalog();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)->call('tambahDestinasi', '  ');

    Http::assertNotSent(fn ($p) => str_contains($p->url(), 'katalog-destinasi'));
});

test('hanya entri tambahan yang punya tombol hapus di daftar destinasi', function () {
    fakeKatalog();

    $html = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)->html();

    // Katalog bawaan ikut versi kode, dan nama destinasi yang sudah tercatat
    // dipakai barisnya sendiri — keduanya tidak boleh bisa dihapus dari sini.
    expect($html)->toContain('orchaPilihDestinasi')
        ->and($html)->toContain('orchaDestManual')
        ->and($html)->toContain('Pantai Rahasia');
});

/* ---------- DAERAH ---------- */

function fakeDaerah(): void
{
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');

    Http::fake([
        '*/rujukan' => Http::response(['data' => [
            'wilayah' => ['jawa' => 'Jawa', 'bali' => 'Bali'],
            'wilayah_kustom' => [],
            'provinsi_wilayah' => ['Jawa Timur' => 'jawa', 'Bali' => 'bali'],
            'provinsi_kustom' => [],
            'katalog_daerah' => [
                'Banyuwangi' => 'Jawa Timur', 'Malang' => 'Jawa Timur',
                'Nusa Penida' => 'Bali', 'Badung' => 'Bali',
            ],
            'katalog_daerah_kustom' => [['id' => 7, 'nama' => 'Situbondo', 'provinsi' => 'Jawa Timur']],
            'katalog_destinasi' => [], 'katalog_destinasi_kustom' => [],
        ]]),
        '*/daerah*' => Http::response(['pesan' => 'ditambahkan'], 201),
        '*' => Http::response(['data' => []]),
    ]);
}

test('daftar daerah menyusut mengikuti provinsi', function () {
    fakeDaerah();

    $uji = Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur');

    expect($uji->instance()->daerahTersedia())->toBe(['Banyuwangi', 'Malang']);

    $uji->set('provinsi', 'Bali');

    expect($uji->instance()->daerahTersedia())->toBe(['Badung', 'Nusa Penida']);

    // Provinsinya ditempel di DOM: pemilih daerah membacanya saat diklik, dan
    // nilai yang ditulis di dalam <script> membeku pada pemuatan pertama.
    expect($uji->html())->toContain('data-orcha-provinsi="Bali"');
});

test('daerah yang tidak cocok dikosongkan saat provinsi diganti', function () {
    fakeDaerah();

    // "Banyuwangi, Bali" adalah alamat yang tidak pernah ada, dan yang salah
    // begitu tidak ketahuan sampai ada penyewa yang bertanya.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->set('daerah', 'Banyuwangi')
        ->set('provinsi', 'Bali')
        ->assertSet('daerah', '');
});

test('daerah yang masih cocok tidak ikut dikosongkan', function () {
    fakeDaerah();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->set('daerah', 'Banyuwangi')
        ->set('provinsi', 'Jawa Timur')
        ->assertSet('daerah', 'Banyuwangi');
});

test('menambah daerah menyertakan provinsi yang sedang dipilih', function () {
    fakeDaerah();

    // Daftar daerah yang tidak tahu provinsinya tidak bisa disaring, dan yang
    // tidak tersaring akan menawarkan Banyuwangi kepada admin yang sedang
    // mengisi destinasi di Bali.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('provinsi', 'Jawa Timur')
        ->call('tambahDaerah', 'Situbondo')
        ->assertSet('daerah', 'Situbondo')
        ->assertDispatched('orcha-daerah-segar');

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/daerah')
        && ($p->data()['nama'] ?? null) === 'Situbondo'
        && ($p->data()['provinsi'] ?? null) === 'Jawa Timur');
});

test('daerah tidak bisa ditambah sebelum provinsinya dipilih', function () {
    fakeDaerah();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->call('tambahDaerah', 'Situbondo')
        ->assertSet('daerah', '');

    Http::assertNotSent(fn ($p) => $p->method() === 'POST' && str_contains($p->url(), '/daerah'));
});

test('daerah ikut tersimpan dan tampil di pratinjau', function () {
    fakeDaerah();

    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Kawah Ijen')
        ->set('provinsi', 'Jawa Timur')
        ->set('daerah', 'Banyuwangi')
        ->assertSee('Banyuwangi, Jawa Timur')
        ->call('simpan')
        ->assertHasNoErrors();

    Http::assertSent(fn ($p) => $p->method() === 'POST'
        && str_contains($p->url(), '/destinasi')
        && ($p->data()['daerah'] ?? null) === 'Banyuwangi');
});

test('usulan yang datang tidak lengkap tidak meruntuhkan halaman', function () {
    fakeUsulan([]);

    // Balasan kosong dari Orcha dulu membuat halaman galat justru saat admin
    // sedang mengetik — dan galat sewaktu mengetik paling merusak kepercayaan
    // pada isian yang mengisi dirinya sendiri.
    Livewire::actingAs(adminDestinasi())->test(OrchaDestinasiForm::class)
        ->set('nama', 'Tempat Antah Berantah')
        ->assertSet('provinsi', '')
        ->assertHasNoErrors();
});
