<?php

use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaDetail;
use App\Livewire\Pages\Admin\PelamarKerja\PelamarKerjaList;
use App\Models\JobApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
 | Nama pembantunya berawalan "Baca" dengan sengaja: adminBerkas() sudah
 | dipakai TombolBerkasJasaTest, dan dua berkas uji yang mendeklarasikan
 | fungsi bernama sama membuat SELURUH rangkaian mati dengan "Cannot
 | redeclare" — hijau saat berkasnya dijalankan sendirian, fatal saat
 | dijalankan bersama.
 */

/**
 * Admin membaca berkas pelamar TANPA mengunduhnya dulu.
 *
 * Berkasnya selalu PDF (formulir situs menolak yang lain lewat
 * mimetypes:application/pdf), jadi peramban pasti bisa menggambarnya — asal
 * peladen menyajikannya sebagai "inline", bukan "attachment". Satu huruf di
 * kepala respons itulah bedanya antara terbaca di tempat dan terunduh ke
 * folder Downloads.
 */
function adminBacaBerkas(): \App\Models\User
{
    return adminRab(['akses_asthana', 'view_pelamar', 'edit_pelamar', 'delete_pelamar']);
}

function pelamarBacaBerkas(array $ubah = []): JobApplication
{
    $buat = function (string $nama) {
        return UploadedFile::fake()->create($nama, 10, 'application/pdf')
            ->storeAs('applications/uji', $nama, 'local');
    };

    return JobApplication::create(array_merge([
        'name' => 'Budi Santoso',
        'email' => 'budi@contoh.test',
        'phone' => '081234567890',
        'cv_path' => $buat('cv-'.uniqid().'.pdf'),
        'cover_letter_path' => $buat('sl-'.uniqid().'.pdf'),
    ], $ubah));
}

beforeEach(fn () => Storage::fake('local'));

it('menyajikan berkas sebagai inline, bukan unduhan paksa', function () {
    /*
     | Content-Disposition "attachment" membuat iframe pratinjau MENGUNDUH
     | berkasnya alih-alih menggambarnya — layar tampak kosong, dan folder
     | Downloads penuh CV tanpa ada yang sengaja menekan unduh.
     */
    $p = pelamarBacaBerkas();

    $r = $this->actingAs(adminBacaBerkas())->get(route('admin.pelamar.cv', $p));

    $r->assertOk();
    expect($r->headers->get('Content-Disposition'))->toStartWith('inline')
        ->and($r->headers->get('Content-Type'))->toBe('application/pdf');
});

it('menggambar pembaca berkas di halaman detail, bukan tiga kartu bertumpuk', function () {
    $p = pelamarBacaBerkas();

    Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->assertSee('Dibaca langsung di sini')
        ->assertSeeHtml('data-pdf="'.route('admin.pelamar.cv', $p).'"')
        // Tab berkas lain tetap terlihat walau belum dibuka.
        ->assertSee('Surat Lamaran')
        ->assertSee('Berkas Pendukung');
});

it('berpindah tab menukar berkas yang dibaca', function () {
    $p = pelamarBacaBerkas();

    Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->call('bukaBerkas', 'surat')
        ->assertSeeHtml('data-pdf="'.route('admin.pelamar.surat', $p).'"')
        ->assertDontSeeHtml('data-pdf="'.route('admin.pelamar.cv', $p).'"');
});

it('membuka tab berkas yang MEMANG ADA, bukan selalu CV', function () {
    /*
     | Pelamar yang CV-nya sudah dipangkas masa simpan tetapi surat
     | lamarannya masih ada: membukanya di tab CV yang kosong terbaca sebagai
     | "tidak ada berkasnya sama sekali".
     */
    $p = pelamarBacaBerkas(['cv_path' => null]);

    expect(Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->get('berkasAktif'))->toBe('surat');
});

it('daftar bisa membaca berkas tanpa meninggalkan halamannya', function () {
    /*
     | Admin yang sedang menyaring sepuluh pelamar kehilangan posisi gulung
     | dan saringannya tiap kali membuka halaman detail, lalu kembali ke
     | daftar yang sudah ter-reset — sepuluh CV berarti sepuluh kali begitu.
     */
    $p = pelamarBacaBerkas();

    $komponen = Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaList::class)
        ->set('search', 'Budi');

    $komponen->assertDontSeeHtml('data-pdf=')
        ->call('bukaPratinjau', $p->id)
        ->assertSeeHtml('data-pdf="'.route('admin.pelamar.cv', $p).'"')
        // Saringannya TIDAK ikut hilang: itu seluruh gunanya dibaca di sini.
        ->assertSet('search', 'Budi');

    $komponen->call('tutupPratinjau')->assertDontSeeHtml('data-pdf=');
});

it('tidak menawarkan baca berkas bila tidak ada berkasnya', function () {
    // Tombol yang membuka jendela kosong hanya menambah benda yang harus
    // dicoba dulu sebelum tahu tidak ada isinya.
    $p = pelamarBacaBerkas(['cv_path' => null, 'cover_letter_path' => null]);

    Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaList::class)
        ->assertDontSeeHtml('wire:click="bukaPratinjau('.$p->id.')"');
});

it('menolak membuka pratinjau tanpa izin melihat pelamar', function () {
    $p = pelamarBacaBerkas();
    $tanpaIzin = adminRab(['akses_asthana']);

    Livewire::actingAs($tanpaIzin)
        ->test(PelamarKerjaList::class)
        ->call('bukaPratinjau', $p->id)
        // Livewire menangkap abort() dan menjadikannya status respons; ia
        // TIDAK merambat keluar sebagai exception, jadi assertForbidden()
        // yang dipakai — bukan ->throws().
        ->assertForbidden();
});

it('menggambar PDF sendiri, tidak menyerahkannya ke penampil peramban', function () {
    /*
     | <iframe src="berkas.pdf"> bergantung pada setelan peramban. Chrome
     | yang disetel "Download PDFs instead of automatically opening them"
     | menggantinya dengan kotak abu bertombol "Open" — isinya tidak pernah
     | terbaca tanpa satu klik lagi, dan setelan itu ada di mesin tiap admin,
     | bukan di tangan kita.
     */
    $p = pelamarBacaBerkas();

    Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaDetail::class, ['id' => $p->id])
        ->assertDontSeeHtml('<iframe');

    expect(file_get_contents(resource_path(
        'views/livewire/pages/admin/pelamar-kerja/partials/berkas-gaya.blade.php'
    )))->toContain('pdf.js/3.11.174');
});

it('jendela pembaca memakai kerangka jendela bersama, bukan lapisan sendiri', function () {
    /*
     | Lapisan buatan sendiri ber-z-index 1050/1051 duduk DI BAWAH bilah
     | samping: jendelanya tertindih dan separuh kirinya hilang. Kerangka
     | bersama (.ts-modal, 1055/1056) sudah terbukti di empat jendela layar
     | Task, dan ikut mengunci guliran halaman di belakangnya.
     */
    $p = pelamarBacaBerkas();

    Livewire::actingAs(adminBacaBerkas())
        ->test(PelamarKerjaList::class)
        ->call('bukaPratinjau', $p->id)
        ->assertSeeHtml('class="ts-modal-back"')
        ->assertSeeHtml('ts-modal-card dsb is-datar is-pembaca')
        ->assertDontSeeHtml('pbk-latar');
});
