<?php

use App\Models\User;
use App\Support\PratinjauUnggahan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

/*
 | SELURUH pratinjau unggahan di panel admin tampil sebagai gambar rusak di
 | produksi — bukan hanya di satu formulir.
 |
 | Alamat pratinjau bawaan Livewire berakhiran nama berkas asli:
 |
 |     /livewire/preview-file/xxxx.jpg?expires=...&signature=...
 |
 | Lapisan pengoptimal gambar di hosting menyergap setiap alamat berakhiran
 | ekstensi gambar dan membuang query string-nya. Terukur langsung dari apa
 | yang diterima PHP: QUERY_STRING kosong, REQUEST_URI sudah tanpa tanda
 | tangan, hasValidSignature() palsu — lalu Livewire menjawab 401.
 |
 | Rute pengganti menyandikan nama berkas ke dalam JALUR, sehingga tidak ada
 | ekstensi gambar di ujungnya dan tidak ada yang perlu dititipkan di query.
 */

function berkasSementaraUji(string $nama = 'contoh.jpg'): string
{
    Storage::persistentFake(FileUploadConfiguration::disk());

    $berkas = UploadedFile::fake()->image($nama, 8, 8);
    $simpan = FileUploadConfiguration::storage();
    $simpan->put(FileUploadConfiguration::path($nama), $berkas->get());

    return $nama;
}

it('alamat pratinjau tidak pernah berakhiran ekstensi gambar', function () {
    // Inti perbaikannya. Kalau suatu saat ada yang menyederhanakan sandinya
    // menjadi nama berkas apa adanya, pratinjau akan patah lagi di produksi
    // TANPA satu pun galat — persis seperti sebelumnya.
    $sandi = PratinjauUnggahan::sandi('foto-rombongan.jpg');
    $url = route('pratinjau.unggahan', ['sandi' => $sandi]);

    expect($url)->not->toEndWith('.jpg')
        ->and($url)->not->toContain('.jpg')
        ->and(parse_url($url, PHP_URL_QUERY))->toBeNull();
});

it('sandi bisa dibaca kembali menjadi nama semula', function () {
    // Nama berkas Livewire memuat "-" dan "_" dari base64 aslinya; sandi
    // base64url memakai dua huruf yang sama sebagai pengganti "+" dan "/".
    $nama = 'aumWEU7CQH2ZRN6yaNCY9kRu7OrqX9-metaSU1HXzE4OTkuanBn-.jpg';

    expect(PratinjauUnggahan::bacaSandi(PratinjauUnggahan::sandi($nama)))->toBe($nama);
});

it('menolak tamu yang belum masuk', function () {
    // Folder unggahan sementara menampung berkas SEMUA admin yang sedang
    // mengisi formulir, termasuk bukti pembayaran pelanggan.
    $nama = berkasSementaraUji();

    $this->get(route('pratinjau.unggahan', ['sandi' => PratinjauUnggahan::sandi($nama)]))
        ->assertRedirect();
});

it('mengirim gambar kepada admin yang sudah masuk', function () {
    $nama = berkasSementaraUji();

    $this->actingAs(User::factory()->create())
        ->get(route('pratinjau.unggahan', ['sandi' => PratinjauUnggahan::sandi($nama)]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');
});

it('menolak nama yang mencoba keluar dari folder unggahan', function () {
    // Rute ini membaca cakram berdasarkan masukan dari luar. Tanpa penjagaan,
    // sandi yang isinya "../../.env" menjadikannya alat pembaca berkas.
    $this->actingAs(User::factory()->create())
        ->get(route('pratinjau.unggahan', ['sandi' => PratinjauUnggahan::sandi('../../../.env')]))
        ->assertNotFound();
});

it('menolak berkas yang bukan gambar', function () {
    // Bukti pembayaran berupa PDF juga menumpang di folder yang sama.
    Storage::persistentFake(FileUploadConfiguration::disk());
    FileUploadConfiguration::storage()->put(FileUploadConfiguration::path('rahasia.pdf'), '%PDF-1.4 palsu');

    /*
     | Ditegaskan dulu bahwa berkasnya BENAR-BENAR ada.
     |
     | Tanpa baris ini tesnya menipu: berkas yang tidak ada juga dijawab 404,
     | jadi ia akan tetap hijau walaupun penyaring jenis berkasnya dicabut.
     */
    expect(FileUploadConfiguration::storage()->exists(FileUploadConfiguration::path('rahasia.pdf')))->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->get(route('pratinjau.unggahan', ['sandi' => PratinjauUnggahan::sandi('rahasia.pdf')]))
        ->assertNotFound();
});
