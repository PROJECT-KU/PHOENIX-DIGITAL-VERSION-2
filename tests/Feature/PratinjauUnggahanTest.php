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

/*
 | Perbaikannya baru berlaku di tampilan yang benar-benar memanggilnya.
 |
 | Rutenya sudah ada sejak lama, tetapi empat halaman Orcha — etalase,
 | paket wisata, armada, galeri — masih memanggil ->temporaryUrl() langsung.
 | Keempatnya lolos seluruh tes dan tampil normal di lokal; yang patah hanya
 | di produksi, tempat pengoptimal gambar hosting membuang tanda tangannya.
 |
 | Karena itu penjaganya harus membaca berkas tampilan, bukan menjalankan
 | halamannya: gejalanya memang tidak muncul di lingkungan tes.
 */
it('tidak ada satu pun tampilan admin yang memanggil temporaryUrl langsung', function () {
    $berkas = array_merge(
        glob(resource_path('views/livewire/pages/admin/*.blade.php')) ?: [],
        glob(resource_path('views/livewire/pages/admin/*/*.blade.php')) ?: [],
        glob(resource_path('views/livewire/pages/admin/*/*/*.blade.php')) ?: [],
        glob(resource_path('views/livewire/pages/admin/*/*/*/*.blade.php')) ?: [],
    );

    $pelanggar = [];

    foreach ($berkas as $satu) {
        // Salinan kembar buatan OneDrive ("form.blade 2.php") tidak pernah
        // dirender Blade — melaporkannya hanya kebisingan.
        if (preg_match('/ \d+\.php$/', $satu)) {
            continue;
        }

        if (str_contains(file_get_contents($satu), '->temporaryUrl(')) {
            $pelanggar[] = str_replace(resource_path('views/livewire/pages/admin/'), '', $satu);
        }
    }

    /*
     | Yang di bawah ini BELUM diperbaiki, bukan dikecualikan.
     |
     | Semuanya masih menampilkan pratinjau rusak di produksi persis seperti
     | halaman Orcha sebelum diperbaiki. Didaftarkan apa adanya supaya utangnya
     | terlihat dan bisa dicoret satu per satu; penjaganya tetap menutup pintu
     | untuk pemanggilan BARU di mana pun di panel admin.
     |
     | Daftarnya hanya boleh menyusut. Menambah nama ke sini berarti sengaja
     | mengirim pratinjau yang tidak akan tampil ke pengguna.
     */
    $belumDibereskan = [
        'Banners/Banners-form.blade.php',
        'ProductBundlings/ProductBundlings-form.blade.php',
        'blog/blog-form.blade.php',
        'modal/modal-list.blade.php',
        'order/order-form.blade.php',
        'pemasukan/pemasukan-list.blade.php',
        'penyelesaian-task/penyelesaian-task-list.blade.php',
        'product/product-form.blade.php',
        'profile/profile-setting.blade.php',
        'spending/spending-form.blade.php',
        'task/task-saya-list.blade.php',
        'testimoni/testimoni-form.blade.php',
    ];

    expect(array_values(array_diff($pelanggar, $belumDibereskan)))
        ->toBe([], 'Pakai PratinjauUnggahan::url() — ->temporaryUrl() tidak tampil di produksi.');

    // Nama yang sudah dibereskan harus dicoret dari daftar di atas, kalau
    // tidak daftarnya pelan-pelan jadi dusta yang tidak ada yang membacanya.
    expect(array_values(array_diff($belumDibereskan, $pelanggar)))
        ->toBe([], 'Sudah tidak memanggil temporaryUrl — coret dari $belumDibereskan.');
});
