<?php

use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPendaftaranDetail;
use App\Livewire\Pages\Admin\Orcha\Pendaftaran\OrchaPesertaForm;
use App\Models\EmployeeDetail;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Melengkapi nama peserta dari sisi admin.
 *
 * Rombongan tanpa nama tidak bisa masuk manifes panggil-nama, dan menunggu
 * pemesan mengisi ulang lewat website jarang berhasil — daftarnya biasanya
 * sudah ada di tangan panitia, hanya belum masuk sistem.
 */
function adminPeserta(): User
{
    // Pembuat admin ditulis ulang di sini, bukan meminjam berkas uji lain,
    // supaya berkas ini tetap bisa dijalankan sendirian dengan --filter.
    $role = Role::create(['name' => 'uji-peserta-'.uniqid(), 'description' => 'Peran untuk uji peserta']);

    foreach (['akses_orcha', 'view_orcha_kesehatan'] as $nama) {
        $permission = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = User::factory()->create(['role_id' => $role->id]);

    EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

function pendaftaranTanpaNama(array $ubah = []): array
{
    return array_merge([
        'id' => 9, 'kode' => 'OT-1508-MUET', 'nama' => 'Siti Aminah',
        'whatsapp' => '081234567890', 'email' => null, 'jumlah_peserta' => 3,
        'peserta' => [], 'jemput_per_titik' => [],
        'kesehatan_terisi' => 0, 'kesehatan_lengkap' => false, 'peserta_belum_isi' => [],
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi'],
        'tanggal_berangkat' => '2026-10-19', 'titik_jemput' => 'Jogja, Klaten, Surakarta',
        'catatan' => null, 'status' => 'lunas', 'status_label' => 'Lunas',
        'jumlah_riwayat_kesehatan' => 0, 'dibuat_pada' => '2026-08-15T09:00:00+07:00',
        'tagihan' => [], 'pembayaran' => [], 'pembatalan' => null,
    ], $ubah);
}

function palsukanDetail(array $ubah = []): void
{
    Http::fake([
        '*/pendaftaran/9/peserta' => Http::response(['data' => [], 'pesan' => 'Daftar peserta tersimpan.']),
        '*/pendaftaran/9' => Http::response(['data' => pendaftaranTanpaNama($ubah)]),
        '*/rujukan*' => Http::response(['data' => [
            'status_pendaftaran' => ['lunas' => 'Lunas'],
            'pembayaran' => ['dp_persen' => 30, 'pelunasan_hari_sebelum' => 5],
        ]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);
}

beforeEach(function () {
    config()->set('orcha.url', 'https://orcha.test/api/v1');
    config()->set('orcha.kunci', 'kunci-uji');
    cache()->forget('orcha.rujukan');
});

test('rombongan tanpa nama peserta ditandai, bukan dibiarkan kosong', function () {
    palsukanDetail();

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Nama peserta belum didata')
        ->assertSee('bisa masuk manifes panggil-nama')
        ->assertSee('Lengkapi daftar peserta')
        // Tautan halaman, bukan wire:click: berpindah halaman selalu terlihat
        // sedang berjalan, sedangkan panggilan latar yang tertahan server sibuk
        // tampak seperti tombol yang mati.
        ->assertSee(route('admin.orcha.pendaftaran.peserta', 9), false);
});

test('nama yang ditempel dari excel terurai jadi baris', function () {
    palsukanDetail();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', "Budi Santoso\tJogja\nSiti Rahmawati\tKlaten\nRina Wijaya")
        ->call('tempel')
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso')
        ->assertSet('barisPeserta.0.titik_jemput', 'Jogja')
        ->assertSet('barisPeserta.1.titik_jemput', 'Klaten')
        ->assertSet('barisPeserta.2.nama', 'Rina Wijaya')
        // Tempelan yang sudah diambil dikosongkan supaya tidak terambil dua kali.
        ->assertSet('tempelan', '');
});

test('penomoran daftar whatsapp dan baris judul tidak ikut jadi nama', function () {
    palsukanDetail();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', "Nama Peserta\n1. Budi Santoso\n2) Siti Rahmawati\n3 - Rina Wijaya\n\n")
        ->call('tempel')
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso')
        ->assertSet('barisPeserta.1.nama', 'Siti Rahmawati')
        ->assertSet('barisPeserta.2.nama', 'Rina Wijaya')
        ->assertCount('barisPeserta', 3);
});

test('berkas csv panitia terbaca jadi daftar peserta', function () {
    palsukanDetail();

    $isi = "Nama,Titik Jemput\nBudi Santoso,Jogja\nSiti Rahmawati,Klaten\n";

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('berkasPeserta', UploadedFile::fake()->createWithContent('peserta.csv', $isi))
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso')
        ->assertSet('barisPeserta.0.titik_jemput', 'Jogja')
        ->assertSet('barisPeserta.1.nama', 'Siti Rahmawati')
        ->assertCount('barisPeserta', 2);
});

test('daftar peserta terkirim ke orcha saat disimpan', function () {
    palsukanDetail();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', "Budi Santoso\tJogja\nSiti Rahmawati\tKlaten")
        ->call('tempel')
        ->call('simpan')
        // Pemberitahuan sukses tampil dulu, baru berpindah balik ke detail.
        ->assertDispatched('orcha-sukses-pindah');

    Http::assertSent(function ($permintaan) {
        return $permintaan->method() === 'PATCH'
            && str_ends_with($permintaan->url(), '/pendaftaran/9/peserta')
            && $permintaan['peserta'][0]['nama'] === 'Budi Santoso'
            && $permintaan['peserta'][1]['titik_jemput'] === 'Klaten'
            && count($permintaan['peserta']) === 2;
    });
});

test('baris kosong tidak ikut terkirim', function () {
    palsukanDetail();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('barisPeserta', [
            ['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja'],
            ['nama' => '   ', 'titik_jemput' => 'Klaten'],
        ])
        ->call('simpan');

    Http::assertSent(fn ($permintaan) => $permintaan->method() === 'PATCH'
        && count($permintaan['peserta']) === 1);
});

test('selisih jumlah nama dan peserta tercatat diberitahukan', function () {
    palsukanDetail();

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        // Tiga peserta tercatat, baru dua namanya masuk.
        ->set('barisPeserta', [
            ['nama' => 'Budi Santoso', 'titik_jemput' => ''],
            ['nama' => 'Siti Rahmawati', 'titik_jemput' => ''],
        ])
        ->call('simpan')
        ->assertDispatched('orcha-sukses-pindah',
            message: '2 nama tersimpan untuk 3 peserta yang tercatat.');
});

test('halaman peserta bisa dibuka dan memuat daftar yang sudah ada', function () {
    palsukanDetail(['peserta' => [['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja']]]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/peserta')
        ->assertOk()
        ->assertSee('Daftar Peserta')
        ->assertSee('OT-1508-MUET')
        ->assertSee('Tempel dari Excel')
        ->assertSee('Unggah berkas');
});

test('nama yang sudah terisi ikut tergambar di isiannya', function () {
    palsukanDetail(['peserta' => [['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja']]]);

    // wire:model saja tidak menuliskan nilainya ke HTML. Tanpa value eksplisit,
    // admin melihat kotak kosong dan menyimpan daftar yang belum sempat
    // diperiksanya.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->assertSeeHtml('value="Budi Santoso"')
        ->assertSeeHtml('value="Jogja"');
});

test('daftar yang sudah ada bisa disunting, bukan hanya ditambah', function () {
    palsukanDetail(['peserta' => [
        ['nama' => 'Budi Santoso', 'titik_jemput' => 'Jogja'],
        ['nama' => 'Siti Rahmawati', 'titik_jemput' => 'Klaten'],
    ]]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso')
        ->call('hapusBaris', 0)
        ->assertSet('barisPeserta.0.nama', 'Siti Rahmawati')
        ->assertCount('barisPeserta', 1);
});

test('daftar menandai rombongan yang namanya belum didata', function () {
    Http::fake([
        '*/pendaftaran*' => Http::response(['data' => [pendaftaranTanpaNama()], 'meta' => [
            'halaman' => 1, 'per_halaman' => 10, 'total' => 1, 'halaman_terakhir' => 1]]),
        '*/rujukan*' => Http::response(['data' => ['status_pendaftaran' => ['lunas' => 'Lunas']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    // Ditandai di daftar supaya bisa dicari sebelum hari berangkat — bukan
    // ditemukan saat rombongan sudah berkumpul.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran')
        ->assertOk()
        ->assertSee('nama belum didata');
});

test('peserta yang riwayat kesehatannya sudah masuk ditandai', function () {
    palsukanDetail([
        'peserta' => [
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
            ['nama' => 'Dewi Lestari', 'titik_jemput' => 'Jogja'],
        ],
        'peserta_belum_isi' => ['Dewi Lestari'],
    ]);

    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9]);

    // Kaitan riwayat kesehatan berdasarkan NAMA, jadi admin perlu tahu nama
    // mana yang tidak boleh diketik ulang sembarangan — sebelum mengetik,
    // bukan setelahnya.
    expect($halaman->instance()->namaSudahIsi())->toBe(['suparjiman']);

    $halaman->assertSee('Mengubah ejaan namanya memutus kaitan dengan riwayat itu')
        ->assertSeeHtml('title="Riwayat kesehatannya sudah masuk. Mengubah ejaan namanya memutus kaitan itu."');
});

test('kotak tempelan tidak ikut dipangkas tinggi isian sebaris', function () {
    palsukanDetail();

    // Layout lemon memaksa height:48px pada setiap .form-control — masuk akal
    // untuk isian sebaris, tetapi memangkas textarea jadi setinggi satu baris.
    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9]);

    $halaman->assertSeeHtml('class="form-control orcha-tempel"');

    expect(file_get_contents(resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php')))
        ->toContain('.orcha-tempel')
        ->toContain('height: auto !important;');
});

test('titik jemput dipilih dari daftar, bukan diketik bebas', function () {
    palsukanDetail([
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi',
            'titik_jemput' => ['Jogja', 'Klaten', 'Surakarta']],
        'peserta' => [['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten']],
    ]);

    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9]);

    expect($halaman->instance()->pilihanTitik())->toBe(['Jogja', 'Klaten', 'Surakarta']);

    // Atributnya tercetak di dua baris, jadi spasinya dirapatkan dulu.
    $rapat = preg_replace('/\s+/', ' ', $halaman->html());

    expect($rapat)
        ->toContain('<option value="">— belum dipilih —</option>')
        // Nilai yang sedang dipakai harus ditandai dari server, kalau tidak
        // pemilihnya menampilkan "belum dipilih" untuk peserta yang titiknya ada.
        ->toContain('value="Klaten" selected>');
});

test('titik tersimpan yang beda besar-kecil huruf tetap terpilih', function () {
    palsukanDetail([
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi',
            'titik_jemput' => ['Jogja', 'Klaten', 'Surakarta']],
        // Data lama menyimpan huruf kecil.
        'peserta' => [['nama' => 'Jono', 'titik_jemput' => 'klaten']],
    ]);

    $rapat = preg_replace('/\s+/', ' ', Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->html());

    // Perbandingan persis huruf membuat titik yang sudah terisi tampil sebagai
    // "belum dipilih" — lalu ikut hilang begitu daftarnya disimpan.
    expect($rapat)->toContain('value="Klaten" selected>');
});

test('ejaan lama di luar daftar tetap jadi pilihannya sendiri', function () {
    palsukanDetail([
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi',
            'titik_jemput' => ['Jogja', 'Klaten']],
        // Data lama menyimpan ejaan di luar daftar paket.
        'peserta' => [['nama' => 'Jono', 'titik_jemput' => 'Boyolali']],
    ]);

    // Kalau pilihannya dibatasi daftar paket saja, menyimpan ulang akan
    // diam-diam memindahkan Jono ke titik lain — atau mengosongkannya.
    expect(Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->instance()
        ->pilihanTitik())->toContain('Boyolali');
});

test('paket tanpa titik jemput tidak mengunci admin', function () {
    palsukanDetail([
        'paket' => ['id' => 1, 'nama' => 'Private Trip Dieng', 'titik_jemput' => []],
        'titik_jemput' => null,
        'peserta' => [['nama' => 'Suparjiman', 'titik_jemput' => '']],
    ]);

    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9]);

    expect($halaman->instance()->pilihanTitik())->toBe([]);

    // Tanpa satu pun pilihan, pemilih hanya akan jadi kotak kosong yang tidak
    // bisa diisi — jadi isian bebasnya dipertahankan untuk keadaan itu.
    $halaman->assertSee('Titik jemput belum ditentukan paket');
});

test('nama bergelar di berkas tidak terpotong komanya', function () {
    palsukanDetail();

    // Sel Excel/CSV sudah terpisah sejak dibaca. Memisah ulang dengan koma
    // merusak isi selnya sendiri — dan gelar di belakang nama bukan hal yang
    // jarang di daftar peserta.
    $isi = "Nama,Titik Jemput\n\"Budi Santoso, S.Pd\",Klaten\n\"Siti Rahmawati, M.Pd\",Jogja\n";

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('berkasPeserta', UploadedFile::fake()->createWithContent('peserta.csv', $isi))
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso, S.Pd')
        ->assertSet('barisPeserta.0.titik_jemput', 'Klaten')
        ->assertSet('barisPeserta.1.nama', 'Siti Rahmawati, M.Pd');
});

test('tempelan tetap boleh memisah dengan koma', function () {
    palsukanDetail();

    // Aturan longgar dipertahankan untuk tempelan: bentuk yang datang ke admin
    // memang bermacam-macam, dan di sana tidak ada sel yang bisa dirusak.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', "Budi Santoso, Klaten\nSiti Rahmawati; Jogja")
        ->call('tempel')
        ->assertSet('barisPeserta.0.titik_jemput', 'Klaten')
        ->assertSet('barisPeserta.1.titik_jemput', 'Jogja');
});

/* --------------------------- TEMPLAT UNGGAHAN --------------------------- */

test('templat peserta bisa diunduh dan mengikuti pendaftarannya', function () {
    palsukanDetail([
        'kode' => 'OT-1608-FXYK', 'jumlah_peserta' => 4,
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi',
            'titik_jemput' => ['Jogja', 'Klaten', 'Surakarta']],
    ]);

    $balasan = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/peserta/templat')
        ->assertOk();

    expect($balasan->headers->get('content-disposition'))
        ->toContain('TEMPLAT-PESERTA-OT-1608-FXYK.xlsx');
});

test('templat memuat judul kolom, baris sebanyak peserta, dan pilihan titik jemput', function () {
    $berkas = tempnam(sys_get_temp_dir(), 'templat').'.xlsx';

    file_put_contents($berkas, Maatwebsite\Excel\Facades\Excel::raw(
        new App\Exports\OrchaTemplatPesertaExport('OT-1608-FXYK', 4, ['Jogja', 'Klaten', 'Surakarta']),
        Maatwebsite\Excel\Excel::XLSX,
    ));

    $lembar = PhpOffice\PhpSpreadsheet\IOFactory::load($berkas)->getActiveSheet();

    expect($lembar->getCell('A1')->getValue())->toBe('Nama Peserta')
        ->and($lembar->getCell('B1')->getValue())->toBe('Titik Jemput')
        // Kolom ketiga untuk menyatakan penggantian dari berkas.
        ->and($lembar->getCell('C1')->getValue())->toBe('Menggantikan (opsional)')
        // Empat peserta tercatat berarti empat baris kosong siap diisi,
        // ditutup satu baris keterangan.
        ->and($lembar->getHighestRow())->toBe(6)
        // Kolom pertama baris keterangan dikosongkan supaya pengurainya
        // melewatinya saat berkasnya diunggah kembali.
        ->and($lembar->getCell('A6')->getValue())->toBeEmpty()
        ->and($lembar->getCell('B6')->getValue())->toContain('Titik jemput tersedia');

    // Titik jemput dipilih, bukan diketik: itu yang menjaga ejaannya sama
    // dengan yang sudah ditentukan paket.
    $validasi = $lembar->getCell('B2')->getDataValidation();

    expect($validasi->getType())->toBe(PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
        ->and($validasi->getFormula1())->toBe('"Jogja,Klaten,Surakarta"');
});

test('nama titik bertanda koma tidak merusak daftar pilihannya', function () {
    $berkas = tempnam(sys_get_temp_dir(), 'templat').'.xlsx';

    // Daftar pilihan inline dipisah koma, jadi nama titik yang mengandung koma
    // akan memecah pilihannya sendiri. Lebih baik tanpa validasi daripada
    // dengan daftar yang salah.
    file_put_contents($berkas, Maatwebsite\Excel\Facades\Excel::raw(
        new App\Exports\OrchaTemplatPesertaExport('OT-X', 2, ['Terminal Giwangan, Jogja', 'Klaten']),
        Maatwebsite\Excel\Excel::XLSX,
    ));

    $lembar = PhpOffice\PhpSpreadsheet\IOFactory::load($berkas)->getActiveSheet();

    expect($lembar->getCell('B2')->getDataValidation()->getType())
        ->toBe(PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_NONE)
        // Daftar yang sah tetap tercetak di baris keterangan paling bawah.
        ->and($lembar->getCell('B4')->getValue())->toContain('Terminal Giwangan, Jogja');
});

test('templat yang diisi bisa diunggah kembali tanpa dirapikan', function () {
    palsukanDetail([
        'paket' => ['id' => 1, 'nama' => 'Open Trip Banyuwangi',
            'titik_jemput' => ['Jogja', 'Klaten', 'Surakarta']],
        'peserta' => [],
    ]);

    // Berkasnya dibuat oleh templat yang sama yang diunduh panitia, lalu diisi
    // seperti mereka mengisinya. Inilah yang membuktikan kedua sisi cocok:
    // baris judulnya terbuang, kolomnya terbaca, dan gelar di belakang nama
    // tidak terpotong.
    $berkas = tempnam(sys_get_temp_dir(), 'isi').'.xlsx';

    file_put_contents($berkas, Maatwebsite\Excel\Facades\Excel::raw(
        new App\Exports\OrchaTemplatPesertaExport('OT-1608-FXYK', 3, ['Jogja', 'Klaten', 'Surakarta']),
        Maatwebsite\Excel\Excel::XLSX,
    ));

    $buku = PhpOffice\PhpSpreadsheet\IOFactory::load($berkas);
    $lembar = $buku->getActiveSheet();
    $lembar->setCellValue('A2', 'Budi Santoso, S.Pd')->setCellValue('B2', 'Klaten');
    $lembar->setCellValue('A3', 'Siti Rahmawati')->setCellValue('B3', 'Jogja')
        ->setCellValue('C3', 'Suparjiman');
    (new PhpOffice\PhpSpreadsheet\Writer\Xlsx($buku))->save($berkas);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        // Livewire menguji unggahan lewat berkas palsu, jadi isinya disalin
        // ke sana apa adanya.
        ->set('berkasPeserta', UploadedFile::fake()->createWithContent('peserta.xlsx', file_get_contents($berkas)))
        ->assertSet('barisPeserta.0.nama', 'Budi Santoso, S.Pd')
        ->assertSet('barisPeserta.0.titik_jemput', 'Klaten')
        ->assertSet('barisPeserta.1.nama', 'Siti Rahmawati')
        ->assertSet('barisPeserta.1.titik_jemput', 'Jogja')
        // Kolom ketiga yang diisi berarti baris itu menggantikan seseorang.
        ->assertSet('barisPeserta.1.gantikan', 'Suparjiman')
        // Baris nama kolom dan baris keterangan di bawah tidak ikut jadi peserta.
        ->assertCount('barisPeserta', 2);
});

/* ------------------------ PENGGANTIAN PESERTA ------------------------ */

test('riwayat perubahan nama tampil di halaman detail', function () {
    palsukanDetail([
        'riwayat_penggantian' => [
            ['dari' => 'Haha', 'ke' => 'Wiam', 'pada' => '2026-08-24T09:00:00+07:00',
                'oleh' => 'admin@lemon.test'],
        ],
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Riwayat Perubahan Nama Peserta')
        ->assertSee('Haha')
        ->assertSee('Wiam')
        ->assertSee('admin@lemon.test')
        // Suratnya berbentuk PDF resmi: dicetak, ditandatangani, diarsipkan.
        // Satu untuk seluruh pendaftaran — tautannya tidak lagi membawa nama
        // siapa pun, karena isinya disusun Orcha dari riwayatnya sendiri.
        ->assertSee('Unduh surat pernyataan')
        ->assertDontSee('dari=Haha', false)
        ->assertSee(route('admin.orcha.pendaftaran.surat-penggantian', 9), false);
});

test('riwayat dibaca terbaru dulu, tetapi nomornya urutan kejadian', function () {
    palsukanDetail([
        'riwayat_penggantian' => [
            ['dari' => 'Budi', 'ke' => 'Ahmad', 'pada' => '2026-08-21T11:47:00+07:00'],
            ['dari' => 'Suparjiman', 'ke' => 'Rina', 'pada' => '2026-08-23T16:02:00+07:00'],
            ['dari' => 'Haha', 'ke' => 'Wiam', 'pada' => '2026-08-24T09:14:00+07:00'],
        ],
    ]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    /*
     | Dua urutan yang berlawanan, keduanya harus benar.
     |
     | Yang dibaca lebih dulu adalah yang terbaru — itu yang dicari admin.
     | Tetapi nomornya dihitung dari yang terlama, sehingga "penggantian
     | pertama" selalu bernomor 1 berapa pun banyaknya penggantian sesudahnya.
     | Kalau nomornya ikut urutan tampil, nomor satu peristiwa berubah setiap
     | ada penggantian baru — dan nomor yang berubah tidak bisa dipakai
     | menyebut apa pun.
     */
    // Diukur dari bulatan nomornya, bukan dari posisi namanya: nama yang sama
    // muncul lagi di tautan suratnya (?dari=…&ke=…) pada baris yang sama, jadi
    // strpos mentah tidak mengukur urutan tampil sama sekali.
    preg_match_all('/orcha-ganti-nomor">(\d+)</', $isi, $cocok);

    expect($cocok[1])->toBe(['3', '2', '1'])
        ->and($isi)->toContain('3 penggantian');

    // Dan yang teratas memang penggantian terbaru.
    $urutanNama = [];

    foreach (['Wiam', 'Rina', 'Ahmad'] as $nama) {
        $urutanNama[$nama] = strpos($isi, 'orcha-ganti-baru">'.$nama);
    }

    expect($urutanNama['Wiam'])->toBeLessThan($urutanNama['Rina'])
        ->and($urutanNama['Rina'])->toBeLessThan($urutanNama['Ahmad']);
});

test('surat bertanda tangan bisa diunggah dari halaman detail', function () {
    palsukanDetail(['riwayat_penggantian' => [['dari' => 'Haha', 'ke' => 'Wiam']]]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->set('suratTtd', UploadedFile::fake()->createWithContent('surat.pdf', '%PDF-1.4 tanda tangan'))
        ->assertHasNoErrors();

    /*
     | Dikirim sebagai `surat`, bukan `surat[]`.
     |
     | kirim() menempelkan berkas jamaknya bergaya `nama[]` supaya sampai di
     | Orcha sebagai larik; untuk berkas tunggal yang divalidasi `file` di sana,
     | kurung siku itu justru membuatnya tidak pernah cocok — 422 yang sulit
     | ditebak sebabnya dari sisi admin.
     */
    Http::assertSent(fn ($permintaan) => $permintaan->method() === 'POST'
        && str_contains($permintaan->url(), 'surat-penggantian-ttd')
        && collect($permintaan->data())->contains(fn ($bagian) => ($bagian['name'] ?? '') === 'surat'));
});

test('berkas selain pindaian dan foto ditolak sebelum dikirim', function () {
    palsukanDetail();

    // Ditahan di sini, bukan menunggu 422 dari Orcha: admin yang salah pilih
    // berkas berhak tahu alasannya seketika.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->set('suratTtd', UploadedFile::fake()->create('catatan.txt', 10))
        ->assertHasErrors('suratTtd');
});

test('berkas kosong ditolak dengan sebab yang benar, bukan disalahkan ke jaringan', function () {
    palsukanDetail();

    /*
     | Berkas yang tidak terbaca sempat muncul sebagai galat multipart, lalu
     | tertelan jadi "server tidak bisa dihubungi" — pesan yang membuat admin
     | menunggu jaringan membaik padahal berkasnyalah yang rusak.
     */
    $kosong = UploadedFile::fake()->createWithContent('kosong.pdf', '');

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->set('suratTtd', $kosong)
        ->assertDispatched('toast-error',
            fn ($nama, $data) => str_contains($data['message'], 'kosong atau tidak terbaca'));

    Http::assertNotSent(fn ($permintaan) => $permintaan->method() === 'POST');
});

test('surat yang salah unggah bisa dicabut dari arsip', function () {
    palsukanDetail([
        'riwayat_penggantian' => [['dari' => 'Haha', 'ke' => 'Wiam']],
        'surat_penggantian' => 'https://orcha.test/storage/surat-penggantian/abc.pdf',
    ]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPendaftaranDetail::class, ['pendaftaran' => 9])
        ->call('hapusSuratTtd')
        ->assertDispatched('toast-sukses');

    Http::assertSent(fn ($permintaan) => $permintaan->method() === 'DELETE'
        && str_contains($permintaan->url(), 'surat-penggantian-ttd'));
});

test('surat yang sudah ada ditampilkan lengkap dengan waktu unggahnya', function () {
    palsukanDetail([
        'riwayat_penggantian' => [['dari' => 'Haha', 'ke' => 'Wiam']],
        'surat_penggantian' => 'https://orcha.test/storage/surat-penggantian/abc.pdf',
        'surat_penggantian_pada' => '2026-08-24T19:30:00+07:00',
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Surat bertanda tangan sudah diarsipkan')
        ->assertSee('https://orcha.test/storage/surat-penggantian/abc.pdf', false)
        ->assertSee('24 Agustus 2026, 19:30')
        // Ajakan mengunggah tidak lagi ditampilkan: berkasnya sudah ada.
        ->assertDontSee('Sudah ditandatangani? Unggah ke sini');
});

test('tanpa surat, kartunya mengajak mengunggah', function () {
    palsukanDetail(['riwayat_penggantian' => [['dari' => 'Haha', 'ke' => 'Wiam']]]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Sudah ditandatangani? Unggah ke sini')
        ->assertSee('maksimal 8 MB');
});

test('penggantian baru dicatat atas nama admin yang login, bukan surelnya', function () {
    $admin = adminPeserta();

    palsukanDetail(['peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']]]);

    Livewire::actingAs($admin)
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 0)
        ->set('barisPeserta.0.nama', 'Wiam')
        ->call('simpan');

    // Orcha mencatat 'oleh' dari kepala X-Orcha-Admin. Isinya yang menentukan
    // apa yang kelak terbaca di kartu riwayat dan tercetak di catatan
    // kegiatan — jadi di situlah namanya harus sudah benar, bukan ditambal
    // saat ditampilkan.
    Http::assertSent(fn ($permintaan) => $permintaan->method() === 'PATCH'
        && $permintaan->hasHeader('X-Orcha-Admin', $admin->name));
});

test('pencatat lama yang tersimpan sebagai surel ditampilkan sebagai nama', function () {
    $admin = adminPeserta();

    palsukanDetail([
        'riwayat_penggantian' => [
            // Tercatat sebelum OrchaClient mengirim nama: nilainya surel, dan
            // sudah terlanjur menempel di data Orcha.
            ['dari' => 'Haha', 'ke' => 'Wiam', 'pada' => '2026-08-24T09:00:00+07:00',
                'oleh' => $admin->email],
        ],
    ]);

    // Orcha tidak mengenal pengguna lemon, jadi hanya halaman ini yang bisa
    // menerjemahkannya. Diterjemahkan saat ditampilkan, bukan dengan menulis
    // ulang arsipnya.
    $this->actingAs($admin)
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('dicatat oleh '.$admin->name)
        ->assertDontSee($admin->email);
});

test('pencatat yang penggunanya sudah tidak ada dibiarkan apa adanya', function () {
    palsukanDetail([
        'riwayat_penggantian' => [
            ['dari' => 'Haha', 'ke' => 'Wiam', 'pada' => '2026-08-24T09:00:00+07:00',
                'oleh' => 'mantan.admin@lemon.test'],
        ],
    ]);

    // Menggantinya dengan "tidak diketahui" membuang keterangan yang masih
    // bisa dilacak lewat catatan lain.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('mantan.admin@lemon.test');
});

test('titik jemput yang tetap tidak dicoret di riwayatnya', function () {
    palsukanDetail([
        'riwayat_penggantian' => [
            ['dari' => 'Haha', 'ke' => 'Wiam', 'dari_titik' => 'Jogja', 'ke_titik' => 'Jogja',
                'pada' => '2026-08-24T09:00:00+07:00', 'oleh' => 'admin@lemon.test'],
        ],
    ]);

    // Coretan berarti "sudah tidak berlaku". Titik yang justru masih dipakai
    // penggantinya tidak boleh terbaca begitu — sopir membaca kartu ini untuk
    // tahu di mana ia berhenti.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('tetap, tidak berpindah')
        ->assertDontSeeHtml('<span class="orcha-ganti-lama">Jogja</span>');
});

test('titik jemput yang berpindah dicoret dan ikut ke suratnya', function () {
    palsukanDetail([
        'riwayat_penggantian' => [
            ['dari' => 'Haha', 'ke' => 'Wiam', 'dari_titik' => 'Jogja', 'ke_titik' => 'Klaten',
                'pada' => '2026-08-24T09:00:00+07:00', 'oleh' => 'admin@lemon.test'],
        ],
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertDontSee('tetap, tidak berpindah')
        ->assertSeeHtml('<span class="orcha-ganti-lama">Jogja</span>')
        // Titiknya tidak lagi dititipkan lewat URL: surat bermaterai harus
        // memuat yang tercatat sistem, bukan yang bisa disetir pemanggilnya.
        ->assertDontSee('dari_titik=Jogja', false);
});

test('surat penggantian diteruskan sebagai berkas pdf', function () {
    Http::fake([
        '*/surat-penggantian*' => Http::response('%PDF-isi-palsu', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="SURAT-PENGGANTIAN-PESERTA-OT-9.pdf"',
        ]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $balasan = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/surat-penggantian')
        ->assertOk();

    expect($balasan->headers->get('content-type'))
        ->toContain('application/pdf')
        ->and($balasan->headers->get('content-disposition'))->toContain('.pdf');

    // Diteruskan polos, tanpa nama siapa pun di URL-nya: Orcha yang menyusun
    // isinya dari riwayat pendaftaran itu sendiri.
    Http::assertSent(fn ($permintaan) => str_contains($permintaan->url(), '/pendaftaran/9/surat-penggantian')
        && ! str_contains($permintaan->url(), 'dari=')
        && ! str_contains($permintaan->url(), 'ke='));
});

/* ---------- PILIHAN PESAN WHATSAPP ---------- */

test('pilihan pesan menyebut nominal DP, bukan menyuruh admin menyalinnya', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 4000000, 'sisa_teks' => 'Rp 4.000.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
    ]);

    /*
     | Angkanya ikut di pesan, bukan disalin admin dari layar sebelah.
     |
     | Satu digit salah ketik berarti pelanggan mentransfer jumlah yang keliru,
     | dan itu baru ketahuan saat buktinya masuk — setelah uangnya berpindah.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Tagihan uang muka (DP)')
        ->toContain(rawurlencode('Rp 1.200.000'))
        ->not->toContain('Tagihan pelunasan');
});

test('yang sudah membayar sebagian ditawari pelunasan, bukan DP lagi', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 1200000, 'sudah_teks' => 'Rp 1.200.000',
            'sisa' => 2800000, 'sisa_teks' => 'Rp 2.800.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'pelunasan',
        ],
    ]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Tagihan pelunasan')
        ->toContain(rawurlencode('Rp 2.800.000'))
        ->not->toContain('Tagihan uang muka');
});

test('pesan tagihan membawa tautan kirim bukti pembayaran', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 4000000, 'sisa_teks' => 'Rp 4.000.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
        'konfirmasi_pembayaran_tautan' => 'https://orcha.test/konfirmasi-pembayaran?kode=OT-9',
    ]);

    /*
     | "Kirim buktinya ke sini" membuat gambar transfer menumpuk di percakapan
     | lalu dicatat tangan satu per satu — dan yang terlewat baru ketahuan saat
     | pelanggan menagih. Lewat formulir ia langsung masuk ke daftar Bukti
     | Pembayaran, lengkap dengan nominal dan tanggalnya.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain(rawurlencode('https://orcha.test/konfirmasi-pembayaran?kode=OT-9'))
        ->toContain(rawurlencode('Kode dan nominalnya sudah terisi otomatis'))
        ->toContain('membawa tautan kirim bukti');
});

test('tanpa tautan konfirmasi, pesannya kembali ke kalimat lama', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 1200000, 'sudah_teks' => 'Rp 1.200.000',
            'sisa' => 2800000, 'sisa_teks' => 'Rp 2.800.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'pelunasan',
        ],
        'konfirmasi_pembayaran_tautan' => null,
    ]);

    // Menyuruh mengetuk tautan yang tidak ada lebih buruk daripada kalimat
    // lama yang masih bisa diikuti.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain(rawurlencode('mohon kirim buktinya supaya kami catat'))
        ->not->toContain('membawa tautan kirim bukti');
});

test('pendaftaran lunas tidak ditagih, tetapi tetap dikabari lunasnya', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 4000000, 'sudah_teks' => 'Rp 4.000.000',
            'sisa' => 0, 'sisa_teks' => 'Rp 0',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => true, 'jenis_disarankan' => 'pelunasan',
        ],
        'peserta_belum_isi' => [],
    ]);

    /*
     | Tidak ditagih, tetapi tetap dikabari.
     |
     | "Sudah lunas belum?" adalah salah satu pertanyaan yang paling sering
     | masuk lewat WhatsApp, dan sebelumnya justru keadaan itulah yang tidak
     | punya pesan sama sekali — popupnya berkata tidak ada yang perlu
     | dikirimkan.
     */
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertDontSee('Tagihan uang muka')
        ->assertDontSee('Tagihan pelunasan')
        ->assertSee('Konfirmasi pembayaran lunas')
        ->assertSee('tidak ada sisa');
});

test('pengajuan pembatalan dikabari, lengkap dengan nasib uang yang sudah masuk', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 1200000, 'sudah_teks' => 'Rp 1.200.000',
            'sisa' => 2800000, 'sisa_teks' => 'Rp 2.800.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'pelunasan',
        ],
        // Bentuk lengkap seperti yang dikirim Orcha: halaman detail juga
        // menggambar kartu pembatalannya, dan itu menuntut medan lain.
        'pembatalan' => [
            'id' => 1, 'status' => 'diajukan', 'nama_pemohon' => 'jono',
            'alasan_label' => 'Berhalangan', 'penjelasan' => null,
            'jumlah_dibatalkan' => 2, 'rekening' => 'BCA · 123 a.n. jono',
            'dibuat_pada' => '2026-08-24T09:00:00+07:00',
        ],
    ]);

    /*
     | Pertanyaan pertama orang yang membatalkan bukan status pengajuannya,
     | melainkan nasib uang yang sudah dikirim. Menagih pelunasan pada
     | pendaftaran yang sedang diajukan batal justru menyinggung.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Kabar pengajuan pembatalan')
        ->toContain(rawurlencode('Pembayaran yang sudah masuk: *Rp 1.200.000*'))
        ->not->toContain('Tagihan pelunasan');
});

test('pembatalan tanpa pembayaran menyebutnya apa adanya', function () {
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 4000000, 'sisa_teks' => 'Rp 4.000.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000', 'dp_persen' => 30,
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
        'pembatalan' => [
            'id' => 2, 'status' => 'disetujui', 'nama_pemohon' => 'jono',
            'alasan_label' => 'Berhalangan', 'penjelasan' => null,
            'jumlah_dibatalkan' => 1, 'rekening' => 'BCA · 123 a.n. jono',
            'dibuat_pada' => '2026-08-24T09:00:00+07:00',
        ],
    ]);

    // Menjanjikan pengembalian pada orang yang belum pernah membayar hanya
    // memancing pertanyaan susulan yang tidak ada jawabannya.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain(rawurlencode('tidak ada dana yang perlu dikembalikan'))
        ->toContain('belum ada pembayaran');
});

test('peserta yang belum mengisi riwayat kesehatan disebut namanya di pesan', function () {
    palsukanDetail(['peserta_belum_isi' => ['Wildan', 'Rina Wijaya']]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Riwayat kesehatan belum diisi')
        ->toContain('2 peserta belum mengisi')
        ->toContain(rawurlencode('Rina Wijaya'));
});

test('pesan riwayat kesehatan membawa tautan pribadi tiap peserta', function () {
    palsukanDetail([
        'peserta_belum_isi' => ['Rina Wijaya', 'Ahmad Fauzi'],
        'peserta_belum_isi_tautan' => [
            'Rina Wijaya' => 'https://orcha.test/riwayat-kesehatan?kode=OT-9&peserta=Rina+Wijaya',
            'Ahmad Fauzi' => 'https://orcha.test/riwayat-kesehatan?kode=OT-9&peserta=Ahmad+Fauzi',
        ],
    ]);

    /*
     | Sebelumnya pesannya menyuruh "buka menu Riwayat Kesehatan lalu masukkan
     | kode" — tiga langkah, dan kodenya mudah salah ketik. Tautan yang sudah
     | membawa kode dan nama membuat peserta tinggal mengisi kondisinya sendiri.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain(rawurlencode('https://orcha.test/riwayat-kesehatan?kode=OT-9&peserta=Rina+Wijaya'))
        ->toContain(rawurlencode('https://orcha.test/riwayat-kesehatan?kode=OT-9&peserta=Ahmad+Fauzi'))
        ->toContain('pesannya membawa tautan tiap orang')
        ->toContain(rawurlencode('nama dan kodenya sudah terisi otomatis'));
});

test('tanpa tautan, pesannya kembali ke petunjuk manual', function () {
    // Data lama atau Orcha versi lawas: menyuruh mengetuk tautan yang tidak ada
    // lebih buruk daripada memberi petunjuk manual yang masih bisa diikuti.
    palsukanDetail([
        'peserta_belum_isi' => ['Rina Wijaya'],
        'peserta_belum_isi_tautan' => [],
    ]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain(rawurlencode('lewat menu *Riwayat Kesehatan* di website kami'))
        ->not->toContain('pesannya membawa tautan tiap orang');
});

test('tautan kwitansi yang dibagikan adalah tautan publik, bukan alamat admin', function () {
    palsukanDetail(['kwitansi_tautan' => 'https://orcha.test/kwitansi/9?signature=abc']);

    /*
     | Alamat unduh milik admin menuntut kunci API, jadi meneruskannya lewat
     | WhatsApp cuma menghasilkan penolakan di tangan pelanggan — kegagalan
     | yang tidak terlihat dari sisi admin, karena ia sendiri bisa membukanya.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Kirim kwitansi')
        ->toContain(rawurlencode('https://orcha.test/kwitansi/9?signature=abc'));
});

test('tanpa penggantian peserta, tidak ada surat yang ditawarkan sama sekali', function () {
    palsukanDetail();

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertDontSee('Kirim surat pernyataan untuk ditandatangani')
        ->assertDontSee('Kirim salinan surat bertanda tangan');
});

test('ada penggantian tapi belum ditandatangani: yang ditawarkan surat kosongnya', function () {
    // Dua keadaan, dua uji: Http::fake yang dipanggil kedua kali TIDAK menimpa
    // stub pertama, jadi keduanya di satu uji akan menguji keadaan yang sama
    // dua kali tanpa ada yang menyadarinya.
    palsukanDetail([
        'riwayat_penggantian' => [['dari' => 'haha', 'ke' => 'wiam']],
        'surat_penggantian_kosong_tautan' => 'https://orcha.test/surat-penggantian/9?signature=abc',
        'surat_penggantian' => null,
    ]);

    /*
     | Inilah langkah pertamanya, dan yang paling lama tidak ada: berkas kosong
     | untuk dicetak dan ditandatangani. Salinan bertanda tangan baru berguna
     | SESUDAH pemesan mengirimkannya balik — menawarkan itu lebih dulu berarti
     | menawarkan sesuatu yang belum bisa dilakukan siapa pun.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('Kirim surat pernyataan untuk ditandatangani')
        ->toContain('Belum ada yang bertanda tangan masuk')
        ->toContain(rawurlencode('https://orcha.test/surat-penggantian/9?signature=abc'))
        // Salinan arsipnya belum ada, jadi tidak ditawarkan.
        ->not->toContain('Kirim salinan surat bertanda tangan');
});

test('sesudah ditandatangani, salinan arsipnya ikut ditawarkan', function () {
    palsukanDetail([
        'riwayat_penggantian' => [['dari' => 'haha', 'ke' => 'wiam']],
        'surat_penggantian_kosong_tautan' => 'https://orcha.test/surat-penggantian/9?signature=abc',
        'surat_penggantian' => 'https://orcha.test/storage/surat-penggantian/abc.pdf',
    ]);

    // Surat kosongnya tetap ditawarkan — kadang hasil pindaiannya buram atau
    // ada tanda tangan yang terlewat, dan pemesan perlu lembar baru.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Kirim salinan surat bertanda tangan')
        ->assertSee('Kirim surat pernyataan untuk ditandatangani')
        ->assertSee('kirim ulang bila perlu diperbaiki');
});

test('tagihan yang datang setengah lengkap tidak merobohkan halaman detail', function () {
    /*
     | Tagihan tanpa dp_persen — bentuk yang benar-benar datang dari sebagian
     | jalur Orcha, dan yang dipakai fixture uji lain di repo ini.
     |
     | Menyusun pesan WhatsApp sempat mengambil kunci itu langsung, dan
     | akibatnya bukan pesannya yang cacat melainkan SELURUH halaman detail
     | gagal 500 — untuk satu medan yang cuma menghiasi kalimat.
     */
    palsukanDetail([
        'tagihan' => [
            'total' => 4000000, 'total_teks' => 'Rp 4.000.000',
            'sudah' => 0, 'sudah_teks' => 'Rp 0',
            'sisa' => 4000000, 'sisa_teks' => 'Rp 4.000.000',
            'dp' => 1200000, 'dp_teks' => 'Rp 1.200.000',
            'lunas' => false, 'jenis_disarankan' => 'dp',
        ],
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Tagihan uang muka (DP)');
});

test('tautan pesan lewat api.whatsapp.com, bukan wa.me', function () {
    palsukanDetail(['kwitansi_tautan' => 'https://orcha.test/kwitansi/9?signature=abc']);

    /*
     | wa.me merusak emoji dan sebagian tanda baca di WhatsApp Web/Desktop —
     | alasan yang sudah ditulis di App\Support\TautanWa dan sudah dipakai alur
     | order. Pesan sampai dalam keadaan rusak, dan admin tidak pernah tahu
     | karena di layarnya sendiri tampak baik-baik saja.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    $lembar = substr($isi, strpos($isi, 'id="pilihanWa"'));
    $lembar = substr($lembar, 0, strpos($lembar, 'orcha-pilihan-wa-polos'));

    expect($lembar)
        ->toContain('api.whatsapp.com/send?phone=')
        ->not->toContain('wa.me');
});

test('emoji dikirim sebagai penanda titik kode, bukan huruf emojinya', function () {
    palsukanDetail(['peserta_belum_isi' => ['Rina']]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    /*
     | Emoji tidak pernah ikut melewati respons server: ia dirakit di peramban
     | dengan String.fromCodePoint. Empat putaran percobaan di halaman
     | pembayaran membuktikan bahwa di perjalanan itulah ia berubah jadi tanda
     | tanya — sisi kami bersih di tiap pemeriksaan, tapi yang sampai ke
     | WhatsApp tetap rusak.
     */
    expect($isi)
        ->toContain('data-wa-pesan=')
        ->toContain('[[E:1F44B]]')
        // Emoji mentah tidak boleh ada di dalam pesannya sama sekali.
        ->not->toContain('👋')
        ->not->toContain('🩺');
});

test('href memuat pesan polos, cadangan bila skrip perakitnya tidak jalan', function () {
    palsukanDetail(['peserta_belum_isi' => ['Rina']]);

    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    // Kalimat utuh tanpa emoji jauh lebih baik daripada kalimat penuh tanda
    // tanya — jadi yang tertulis di href sengaja sudah dibersihkan penandanya.
    preg_match('/href="([^"]*api\.whatsapp\.com[^"]*)"/', $isi, $cocok);

    expect($cocok[1] ?? '')->not->toBeEmpty()
        ->and(urldecode($cocok[1]))->not->toContain('[[E:');
});

test('penyalin papan tempel ikut dimuat sebagai jalan terakhir', function () {
    palsukanDetail();

    // Tautan yang kami hasilkan sudah benar, tetapi sebagian versi aplikasi
    // WhatsApp salah membaca sandi persennya saat peramban menyerahkannya lewat
    // skema whatsapp://. Menempel memindahkan karakter yang sama persis.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('orchaRakitEmoji', false)
        ->assertSee('Bila emojinya berantakan di WhatsApp, tempel saja');
});

test('percakapan kosong selalu tersedia sebagai jalan keluar', function () {
    palsukanDetail(['peserta_belum_isi' => [], 'tagihan' => []]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Buka percakapan kosong');
});

test('popupnya tidak bergantung pada bootstrap js yang memang tidak dimuat', function () {
    palsukanDetail();

    /*
     | Kelas Bootstrap terpasang di aplikasi ini, tetapi JS-nya tidak pernah
     | dimuat — resources/js/bootstrap.js berisi axios — dan aset Vite pun tidak
     | ikut ter-deploy. Tombol ber-data-bs-toggle diam saja di server, dan itu
     | baru ketahuan setelah dipakai.
     */
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')->assertOk()->getContent();

    expect($isi)
        ->toContain('orchaBukaLembar')
        ->not->toContain('data-bs-toggle="modal"');
});

/* ---------- HALAMAN RIWAYAT KESEHATAN ---------- */

function palsukanKesehatan(array $riwayat, array $pendaftaran = []): void
{
    Http::fake([
        '*/pendaftaran/9/riwayat-kesehatan' => Http::response(['data' => $riwayat]),
        '*/pendaftaran/9' => Http::response(['data' => pendaftaranTanpaNama($pendaftaran)]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);
}

test('peserta yang sudah diganti tidak ikut dihitung sudah mengisi', function () {
    palsukanKesehatan([
        ['nama_peserta' => 'Wildan', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => true],
        ['nama_peserta' => 'haha', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => false],
    ], ['jumlah_peserta' => 3]);

    /*
     | Riwayat milik peserta yang sudah diganti tetap tersimpan, dan ikut
     | menghitungnya membuat rombongan terlihat lebih lengkap daripada
     | sebenarnya — persis kekeliruan yang paling mahal di halaman ini: tim
     | berangkat mengira semua sudah terdata.
     */
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('1 / 3')
        ->assertDontSee('2 / 3');
});

test('arsip dipisah dari peserta yang berangkat, dan penggantinya disebut', function () {
    palsukanKesehatan([
        ['nama_peserta' => 'Wildan', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => true],
        ['nama_peserta' => 'haha', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => false],
    ], [
        'riwayat_penggantian' => [['dari' => 'haha', 'ke' => 'Wiam Ramadhani']],
    ]);

    // Digantikan oleh siapa disebut di kartunya sendiri: tanpa itu admin
    // membuka halaman detail hanya untuk satu nama yang sudah ada di tangan.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('Rincian peserta yang berangkat')
        ->assertSee('Arsip — sudah digantikan, tidak berangkat')
        ->assertSeeHtml('<span class="orcha-ganti-lama">haha</span>')
        ->assertSeeHtml('<span class="orcha-ganti-baru">Wiam Ramadhani</span>');
});

test('pengganti yang belum mengisi ditonjolkan sendiri', function () {
    palsukanKesehatan([
        ['nama_peserta' => 'haha', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => false],
    ], [
        'peserta_belum_isi' => ['Wiam Ramadhani'],
        'riwayat_penggantian' => [['dari' => 'haha', 'ke' => 'Wiam Ramadhani']],
    ]);

    // Akibat langsung penggantian, dan yang paling mudah terlewat: orang
    // lamanya sudah mengisi, jadi rombongan terlihat lengkap sampai ada yang
    // menghitung ulang.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk()
        ->assertSee('Peserta pengganti belum mengisi riwayat kesehatan')
        ->assertSeeHtml('<span class="orcha-ganti-baru">Wiam Ramadhani</span>');
});

test('pengganti yang sudah mengisi tidak ikut ditagih', function () {
    palsukanKesehatan([
        ['nama_peserta' => 'Wiam Ramadhani', 'tingkat_perhatian' => 'aman', 'peserta_aktif' => true],
    ], [
        // Orcha sudah menghitung siapa yang belum mengisi; daftar ini kosong
        // berarti semua yang berangkat sudah terdata.
        'peserta_belum_isi' => [],
        'riwayat_penggantian' => [['dari' => 'haha', 'ke' => 'Wiam Ramadhani']],
    ]);

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk()
        ->assertDontSee('Peserta pengganti belum mengisi riwayat kesehatan');
});

test('pengganti yang belakangan diganti lagi tidak ikut ditagih', function () {
    palsukanKesehatan([], [
        'peserta_belum_isi' => ['Wiam', 'Rina'],
        'riwayat_penggantian' => [
            ['dari' => 'haha', 'ke' => 'Wiam'],
            ['dari' => 'Wiam', 'ke' => 'Rina'],
        ],
    ]);

    // Wiam sempat jadi pengganti, lalu ia sendiri digantikan Rina. Yang
    // ditunggu riwayatnya cuma orang yang benar-benar jadi berangkat.
    $isi = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk()->getContent();

    expect($isi)
        ->toContain('<span class="orcha-ganti-baru">Rina</span>')
        ->not->toContain('<span class="orcha-ganti-baru">Wiam</span>');
});

test('riwayat kesehatan peserta yang diganti ditandai arsip, bukan dihilangkan', function () {
    Http::fake([
        '*/pendaftaran/9/riwayat-kesehatan' => Http::response(['data' => [
            ['nama_peserta' => 'Suparjiman', 'peserta_aktif' => true,
                'tingkat_perhatian' => 'aman', 'alasan_perhatian' => [], 'alasan_catatan' => [],
                'kontak_darurat' => ['nama' => 'Ani', 'hubungan' => 'Istri', 'hp' => '0812']],
            ['nama_peserta' => 'Haha', 'peserta_aktif' => false,
                'tingkat_perhatian' => 'tinggi', 'alasan_perhatian' => ['Riwayat jantung'],
                'alasan_catatan' => [],
                'kontak_darurat' => ['nama' => 'Budi', 'hubungan' => 'Anak', 'hp' => '0813']],
        ]]),
        '*/pendaftaran/9' => Http::response(['data' => pendaftaranTanpaNama([
            'peserta' => [['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten']],
        ])]),
        '*/rujukan*' => Http::response(['data' => ['status_pendaftaran' => ['lunas' => 'Lunas']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    $halaman = $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/riwayat-kesehatan')
        ->assertOk();

    // Datanya tetap tampil, tapi di bagian arsipnya sendiri — bukan berjajar
    // di antara peserta yang berangkat, tempat tim lapangan membacanya sambil
    // berjalan tanpa cara membedakan siapa yang benar-benar ada di kendaraan.
    $halaman->assertSee('Haha')
        ->assertSee('Arsip — sudah digantikan, tidak berangkat')
        ->assertSee('tidak perlu disiapkan apa pun untuknya');

    // Dan tidak ikut menuntut kesiapan tim: yang diganti tidak berangkat.
    $halaman->assertDontSee('Perlu disiapkan sebelum berangkat');
});

test('cara mengganti peserta disebut di kedua halaman', function () {
    palsukanDetail(['peserta' => [['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten']]]);

    // Penggantian tidak punya tombolnya sendiri — ia dikerjakan dengan mengubah
    // nama. Tanpa papan penunjuk, yang mencari "ganti peserta" tidak menemukan
    // apa pun, dan kartu riwayatnya baru muncul setelah ada yang tercatat.
    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9')
        ->assertOk()
        ->assertSee('Peserta berhalangan dan digantikan orang lain?')
        ->assertSee('surat pernyataannya muncul di halaman ini');

    $this->actingAs(adminPeserta())
        ->get('/admin/orcha/pendaftaran/9/peserta')
        ->assertOk()
        ->assertSee('Mengganti peserta?')
        // Petunjuknya menyebut tombolnya, bukan menyuruh mengetik di kotak yang
        // sama dengan nama lama.
        ->assertSee('di baris orang yang berhalangan')
        ->assertSee('nama pengganti diisi di kotak barunya sendiri');
});

test('penggantian yang baru tercatat disebut di pemberitahuan simpan', function () {
    Http::fake([
        '*/pendaftaran/9/peserta' => Http::response(['data' => [
            'riwayat_penggantian' => [
                ['dari' => 'Haha', 'ke' => 'Wiam', 'pada' => '2026-08-24T09:00:00+07:00', 'oleh' => 'a@b.test'],
            ],
        ], 'pesan' => 'Daftar peserta tersimpan.']),
        '*/pendaftaran/9' => Http::response(['data' => pendaftaranTanpaNama([
            'jumlah_peserta' => 1,
            'peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']],
        ])]),
        '*/rujukan*' => Http::response(['data' => ['status_pendaftaran' => ['lunas' => 'Lunas']]]),
        '*' => Http::response(['data' => [], 'meta' => []]),
    ]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('barisPeserta', [['nama' => 'Wiam', 'titik_jemput' => 'Jogja']])
        ->call('simpan')
        ->assertDispatched('orcha-sukses-pindah', function ($nama, $data) {
            return str_contains($data['message'], 'Haha → Wiam')
                && str_contains($data['message'], 'Surat pernyataannya bisa diunduh');
        });
});

test('tombol ganti menyiapkan kotak baru, nama lama ditahan', function () {
    palsukanDetail([
        'peserta' => [
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
            ['nama' => 'Haha', 'titik_jemput' => 'Jogja'],
        ],
    ]);

    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 1);

    // Kotak namanya dikosongkan supaya pengganti diketik di kotak yang bersih,
    // bukan menimpa nama orang lain di kotak yang sama.
    $halaman->assertSet('barisPeserta.1.gantikan', 'Haha')
        ->assertSet('barisPeserta.1.nama', '')
        // Nama lama masuk ke dalam kotaknya, mendahului panah, supaya barisnya
        // terbaca utuh sebagai "Haha → pengganti".
        ->assertSeeHtml('orcha-ganti-gabung')
        ->assertSeeHtml('<span class="orcha-ganti-lama">Haha</span>')
        ->assertSee('nama pengganti');

    // Bisa dibatalkan tanpa kehilangan nama lamanya.
    $halaman->call('batalGanti', 1)
        ->assertSet('barisPeserta.1.nama', 'Haha')
        ->assertSet('barisPeserta.1.gantikan', null);
});

test('niat penggantian ikut terkirim ke orcha', function () {
    palsukanDetail(['peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']]]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 0)
        ->set('barisPeserta.0.nama', 'Wiam')
        ->call('simpan');

    Http::assertSent(fn ($permintaan) => $permintaan->method() === 'PATCH'
        && $permintaan['peserta'][0]['nama'] === 'Wiam'
        && $permintaan['peserta'][0]['gantikan'] === 'Haha');
});

test('kolom titik jemput ikut berubah bentuk begitu barisnya diganti', function () {
    palsukanDetail([
        'peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']],
        'paket' => ['titik_jemput' => ['Jogja', 'Klaten']],
    ]);

    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 0);

    // Titiknya TIDAK ikut dikosongkan: pengganti hampir selalu naik di titik
    // yang sama, dan memilih ulang hal yang sama tiap kali cuma menambah kerja
    // — yang lupa memilih malah menghasilkan peserta tanpa titik jemput.
    $halaman->assertSet('barisPeserta.0.titik_jemput', 'Jogja')
        ->assertSet('barisPeserta.0.gantikan_titik', 'Jogja')
        /*
         | Kotaknya sudah tampil walau titiknya belum dipindah.
         |
         | Kalau ia menunggu titiknya berubah, kolom nama berubah bentuk
         | sementara kolom di sebelahnya tidak — dan admin tidak punya cara
         | tahu apakah titik jemputnya ikut terbawa ke pengganti atau
         | terlewat. Dua kolom, satu bentuk, berubah bersamaan.
         */
        ->assertSeeHtml('<span class="orcha-ganti-lama">Jogja</span>');

    $halaman->set('barisPeserta.0.titik_jemput', 'Klaten')
        ->assertSeeHtml('<span class="orcha-ganti-lama">Jogja</span>');
});

test('titik jemput lama ikut terkirim, pindah atau tidak', function () {
    palsukanDetail([
        'peserta' => [
            ['nama' => 'Haha', 'titik_jemput' => 'Jogja'],
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
        ],
    ]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 0)
        ->set('barisPeserta.0.nama', 'Wiam')
        ->set('barisPeserta.0.titik_jemput', 'Surakarta')
        ->call('mulaiGanti', 1)
        ->set('barisPeserta.1.nama', 'Rina')
        ->call('simpan');

    Http::assertSent(function ($permintaan) {
        if ($permintaan->method() !== 'PATCH') {
            return false;
        }

        return $permintaan['peserta'][0]['gantikan_titik'] === 'Jogja'
            // Rina naik di titik yang sama dengan Suparjiman, dan titiknya
            // tetap dikirim: arsip yang diam saat titiknya tidak berubah tidak
            // bisa dibedakan dari arsip yang lupa mencatatnya.
            && $permintaan['peserta'][1]['gantikan_titik'] === 'Klaten';
    });
});

test('batal mengganti mengembalikan titik jemput lamanya juga', function () {
    palsukanDetail([
        'peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']],
        'paket' => ['titik_jemput' => ['Jogja', 'Klaten']],
    ]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->call('mulaiGanti', 0)
        ->set('barisPeserta.0.titik_jemput', 'Klaten')
        ->call('batalGanti', 0)
        ->assertSet('barisPeserta.0.nama', 'Haha')
        ->assertSet('barisPeserta.0.titik_jemput', 'Jogja')
        ->assertSet('barisPeserta.0.gantikan_titik', null);
});

test('tempelan bertanda panah menimpa baris orang yang digantikan', function () {
    palsukanDetail([
        'peserta' => [
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
            ['nama' => 'Haha', 'titik_jemput' => 'Jogja'],
        ],
    ]);

    // Panitia mengirim satu daftar berisi campuran: satu nama baru, satu
    // penggantian. Memilahnya dengan tangan hanya memindahkan pekerjaan.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', "Haha > Wiam, Jogja\nRina Wijaya, Klaten")
        ->call('tempel')
        // Wiam menimpa baris Haha — bukan menambah baris ketiga, yang berarti
        // dua orang untuk satu kursi.
        ->assertSet('barisPeserta.1.nama', 'Wiam')
        ->assertSet('barisPeserta.1.gantikan', 'Haha')
        ->assertSet('barisPeserta.2.nama', 'Rina Wijaya')
        ->assertCount('barisPeserta', 3);
});

test('tempelan dan berkas memperlakukan titik jemput sama dengan tombol ganti', function () {
    palsukanDetail([
        'peserta' => [
            ['nama' => 'Haha', 'titik_jemput' => 'Jogja'],
            ['nama' => 'Suparjiman', 'titik_jemput' => 'Klaten'],
        ],
    ]);

    /*
     | Tiga jalan masuk yang sama hasilnya: tombol ⇆, tempelan, dan berkas.
     |
     | Ketiganya boleh menyatakan penggantian, jadi ketiganya harus menyimpan
     | titik jemput lamanya — kalau tidak, penggantian lewat tempelan diam-diam
     | kehilangan jejak titiknya sementara yang lewat tombol tidak, dan admin
     | tidak punya cara menduga mana yang tercatat.
     */
    $halaman = Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        // Wiam pindah titik; Rina tidak menyebut titik sama sekali.
        ->set('tempelan', "Haha > Wiam, Surakarta\nSuparjiman > Rina")
        ->call('tempel');

    $halaman->assertSet('barisPeserta.0.gantikan_titik', 'Jogja')
        ->assertSet('barisPeserta.0.titik_jemput', 'Surakarta')
        // Yang tidak menyebut titik mewarisi titik orang yang digantikannya —
        // bukan kosong, yang berarti peserta tanpa titik jemput di manifes.
        ->assertSet('barisPeserta.1.gantikan_titik', 'Klaten')
        ->assertSet('barisPeserta.1.titik_jemput', 'Klaten')
        // Kedua baris tampil berpanah, sama seperti hasil tombol ⇆.
        ->assertSeeHtml('<span class="orcha-ganti-lama">Jogja</span>')
        ->assertSeeHtml('<span class="orcha-ganti-lama">Klaten</span>');
});

test('penggantian berantai lewat tempelan mencatat titik terakhirnya', function () {
    palsukanDetail(['peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']]]);

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('tempelan', 'Haha > Wiam, Klaten')
        ->call('tempel')
        ->set('tempelan', 'Wiam > Rina, Surakarta')
        ->call('tempel')
        // Rina menggantikan Wiam dari Klaten — bukan dari Jogja, titik orang
        // dua tahap sebelumnya yang sudah tidak relevan.
        ->assertSet('barisPeserta.0.gantikan', 'Wiam')
        ->assertSet('barisPeserta.0.gantikan_titik', 'Klaten')
        ->assertCount('barisPeserta', 1);
});

test('kedua kartu unggahan menjelaskan titik jemput penggantinya', function () {
    palsukanDetail(['peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']]]);

    // Perilakunya sudah benar sejak awal; yang tertinggal keterangannya. Admin
    // yang tidak diberi tahu akan menduga penggantian lewat tempelan tidak bisa
    // memindahkan titik, lalu mengerjakannya dua kali.
    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->assertSee('Haha > Wiam, Klaten')
        ->assertSee('naik di titik yang sama')
        ->assertSee('mewarisi titik lamanya');
});

test('berkas boleh menyatakan penggantian lewat kolom ketiga', function () {
    palsukanDetail(['peserta' => [['nama' => 'Haha', 'titik_jemput' => 'Jogja']]]);

    $isi = "Nama,Titik Jemput,Menggantikan\nWiam,Jogja,Haha\nRina Wijaya,Klaten,\n";

    Livewire::actingAs(adminPeserta())
        ->test(OrchaPesertaForm::class, ['pendaftaran' => 9])
        ->set('berkasPeserta', UploadedFile::fake()->createWithContent('peserta.csv', $isi))
        ->assertSet('barisPeserta.0.nama', 'Wiam')
        ->assertSet('barisPeserta.0.gantikan', 'Haha')
        ->assertSet('barisPeserta.1.nama', 'Rina Wijaya')
        ->assertCount('barisPeserta', 2);
});
