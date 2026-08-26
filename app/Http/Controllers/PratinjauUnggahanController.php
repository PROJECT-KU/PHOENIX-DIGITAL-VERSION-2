<?php

namespace App\Http\Controllers;

use App\Support\PratinjauUnggahan;
use Illuminate\Http\Request;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menampilkan berkas yang BARU diunggah dan belum disimpan, untuk pratinjau
 * di formulir admin.
 *
 * Ini menggantikan pratinjau bawaan Livewire, yang tidak bisa dipakai di
 * hosting ini. Alamat bawaannya berakhiran nama berkas asli:
 *
 *     /livewire/preview-file/xxxx.jpg?expires=...&signature=...
 *
 * Lapisan pengoptimal gambar di depan aplikasi menyergap SETIAP alamat
 * berakhiran .jpg dan membuang query string-nya — lazim untuk gambar biasa
 * karena mempercepat penyimpanan sementara. Tetapi izin akses Livewire justru
 * dititipkan di query itu, sehingga yang sampai ke Laravel adalah permintaan
 * tanpa izin. Livewire menjawab 401 dan peramban menampilkan gambar rusak.
 * Terbukti dengan mengukur langsung apa yang diterima PHP: QUERY_STRING kosong
 * dan REQUEST_URI sudah tanpa tanda tangan.
 *
 * Karena itu alamat di sini TIDAK PERNAH berakhiran ekstensi gambar — nama
 * berkasnya disandikan base64url dan menempati satu ruas jalur. Tidak ada yang
 * perlu dititipkan di query, jadi tidak ada yang bisa dibuang.
 */
class PratinjauUnggahanController extends Controller
{
    public function __invoke(Request $request, string $sandi): Response
    {
        $nama = PratinjauUnggahan::bacaSandi($sandi);

        /*
         | Hanya nama berkas polos yang diterima.
         |
         | Rute ini membaca dari cakram berdasarkan masukan dari luar, jadi
         | nama seperti "../../.env" harus mustahil sejak awal — bukan
         | disaring belakangan. basename() membuang seluruh bagian jalur,
         | dan hasil yang berbeda dari masukannya berarti ada yang mencoba
         | keluar dari folder.
         */
        abort_if($nama === null || $nama === '' || $nama !== basename($nama), 404);

        $disk = FileUploadConfiguration::storage();
        $jalur = FileUploadConfiguration::path($nama);

        abort_unless($disk->exists($jalur), 404);

        $jenis = $disk->mimeType($jalur) ?: 'application/octet-stream';

        /*
         | Hanya gambar yang dikirim balik.
         |
         | Folder unggahan sementara menampung SEMUA berkas yang sedang
         | diunggah admin mana pun — termasuk PDF bukti bayar dan berkas
         | pesanan. Tanpa batas ini, rute pratinjau berubah menjadi cara
         | mengunduh berkas milik orang lain hanya dengan menebak namanya.
         */
        abort_unless(str_starts_with($jenis, 'image/'), 404);

        return response($disk->get($jalur), 200, [
            'Content-Type' => $jenis,
            'Content-Disposition' => 'inline',

            // Berkas ini milik satu admin dan berumur pendek. Jangan sampai
            // tersimpan di perantara mana pun lalu tersaji ke orang lain.
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
