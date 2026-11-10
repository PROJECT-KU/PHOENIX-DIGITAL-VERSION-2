<?php

use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;

/**
 * Masa simpan berkas pelamar.
 *
 * CV dan surat lamaran adalah dokumen pribadi orang lain — alamat rumah,
 * nomor telepon, riwayat kerja. Sebelum ada perintah ini tidak ada satu pun
 * yang menghapusnya.
 *
 * Yang dijaga di sini terutama yang TIDAK boleh ikut terhapus: catatan
 * lamarannya sendiri, dan berkas milik lamaran yang masih diproses.
 */
function lamaranUjiBerkas(array $ubah = []): JobApplication
{
    /*
     | created_at DIPAKSA sesudah baris dibuat.
     |
     | Dikirim lewat create(), Eloquent menimpanya dengan waktu sekarang —
     | dan uji "didiamkan setahun" lalu memeriksa baris yang umurnya nol
     | detik. Ia hijau untuk alasan yang salah: tidak ada yang dihapus
     | karena memang belum waktunya, bukan karena aturannya benar.
     */
    $waktu = array_intersect_key($ubah, array_flip(['created_at', 'status_diubah_at']));
    $ubah = array_diff_key($ubah, $waktu);

    $lamaran = JobApplication::create(array_merge([
        'name' => 'Budi Santoso',
        'email' => 'budi@contoh.test',
        'phone' => '081234567890',
        'cv_path' => 'applications/uji/cv-'.uniqid().'.pdf',
        'cover_letter_path' => 'applications/uji/sl-'.uniqid().'.pdf',
        'status' => 'baru',
    ], $ubah));

    if ($waktu !== []) {
        $lamaran->forceFill($waktu)->saveQuietly();
        $lamaran->refresh();
    }

    foreach (['cv_path', 'cover_letter_path'] as $kolom) {
        if ($lamaran->{$kolom}) {
            Storage::disk('local')->put($lamaran->{$kolom}, 'uji');
        }
    }

    return $lamaran;
}

beforeEach(function () {
    // Cakram palsu: uji ini menghapus berkas, dan cakram sungguhan memuat CV
    // pelamar yang nyata.
    Storage::fake('local');
});

it('menghapus berkas lamaran yang sudah diputuskan dan lewat masa simpan', function () {
    $lamaran = lamaranUjiBerkas([
        'status' => 'ditolak',
        'status_diubah_at' => now()->subDays(100),
    ]);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90')->assertSuccessful();

    $sesudah = $lamaran->fresh();
    expect($sesudah->cv_path)->toBeNull()
        ->and($sesudah->cover_letter_path)->toBeNull()
        // Catatannya TETAP: "dulu si A pernah melamar belum?" harus tetap
        // bisa dijawab bertahun-tahun kemudian.
        ->and($sesudah->name)->toBe('Budi Santoso')
        ->and($sesudah->email)->toBe('budi@contoh.test')
        ->and($sesudah->status)->toBe('ditolak');

    Storage::disk('local')->assertMissing($lamaran->cv_path);
});

it('tidak menyentuh lamaran yang masih diproses', function () {
    // Seleksi bisa berbulan-bulan. Berkas yang hilang di tengah proses
    // membuat tim tidak punya apa pun untuk dibaca saat wawancara.
    $lamaran = lamaranUjiBerkas([
        'status' => 'wawancara',
        'created_at' => now()->subDays(200),
        'status_diubah_at' => now()->subDays(150),
    ]);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90 --hari-diam=365')->assertSuccessful();

    expect($lamaran->fresh()->cv_path)->not->toBeNull();
});

it('menghapus berkas lamaran yang didiamkan setahun, berapa pun statusnya', function () {
    /*
     | Tanpa aturan kedua ini, lamaran yang statusnya tidak pernah diubah
     | dari "baru" menyimpan CV-nya selamanya — dan justru itulah tumpukan
     | terbesarnya.
     */
    $lamaran = lamaranUjiBerkas([
        'status' => 'baru',
        'created_at' => now()->subDays(400),
    ]);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90 --hari-diam=365')->assertSuccessful();

    expect($lamaran->fresh()->cv_path)->toBeNull();
});

it('menghitung dari tanggal keputusan, bukan tanggal melamar', function () {
    // Dihitung dari tanggal melamar, berkas pelamar yang seleksinya memang
    // lama akan lenyap tepat sesudah ia diterima.
    $lamaran = lamaranUjiBerkas([
        'status' => 'diterima',
        'created_at' => now()->subDays(300),
        'status_diubah_at' => now()->subDays(10),
    ]);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90 --hari-diam=365')->assertSuccessful();

    expect($lamaran->fresh()->cv_path)->not->toBeNull();
});

it('simulasi tidak menghapus apa pun', function () {
    $lamaran = lamaranUjiBerkas(['status' => 'ditolak', 'status_diubah_at' => now()->subDays(100)]);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90 --dry-run')->assertSuccessful();

    expect($lamaran->fresh()->cv_path)->not->toBeNull();
    Storage::disk('local')->assertExists($lamaran->cv_path);
});

it('mengosongkan kolomnya walau berkasnya sudah hilang duluan', function () {
    /*
     | Yang keliru justru kolom yang menunjuk berkas tidak ada: tombol unduh
     | tetap tergambar, lalu menyerahkan galat, dan admin mengira sistemnya
     | rusak padahal berkasnya memang sudah dihapus sesuai aturan.
     */
    $lamaran = lamaranUjiBerkas(['status' => 'ditolak', 'status_diubah_at' => now()->subDays(100)]);
    Storage::disk('local')->delete($lamaran->cv_path);

    $this->artisan('pelamar:hapus-berkas-kadaluarsa --hari=90')->assertSuccessful();

    expect($lamaran->fresh()->cv_path)->toBeNull();
});

it('terdaftar di penjadwal', function () {
    // proc_open dimatikan hosting, jadi perintahnya WAJIB lewat pembungkus
    // $jadwalkan() (Artisan::call), bukan Schedule::command().
    expect(file_get_contents(base_path('routes/console.php')))
        ->toContain("\$jadwalkan('pelamar:hapus-berkas-kadaluarsa");
});
