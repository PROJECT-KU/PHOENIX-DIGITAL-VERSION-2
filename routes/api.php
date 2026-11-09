<?php

use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\MessageController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/online-users', function () {
    $authUser = Auth::user();

    // ambil user aktif (1 menit terakhir)
    $onlineUserIds = DB::table('sessions')
        ->where('last_activity', '>=', now()->subMinutes(1)->timestamp)
        ->whereNotNull('user_id')
        ->pluck('user_id')
        ->unique()
        ->toArray();

    $users = User::whereIn('id', $onlineUserIds)
        ->where('id', '!=', $authUser->id)
        ->get(['id', 'name', 'last_seen_at']); // ambil field penting saja

    return response()->json($users);
});

/*
 * Endpoint publik (form lamaran & pesan di situs).
 *
 * 'verify.origin' SENGAJA belum dinyalakan: config('cors.allowed_origins.0')
 * masih '*', sehingga hash_equals('*', $origin) tak akan pernah cocok dan
 * SEMUA permintaan akan ditolak 403 — termasuk dari situs sendiri.
 * Untuk menyalakannya: isi ALLOWED_ORIGIN di .env produksi, ubah
 * config/cors.php ke baris 'prod', baru tambahkan 'verify.origin' di sini.
 *
 * Sementara itu pengamannya rate limit per IP — menutup penyalahgunaan utama
 * (banjir lamaran berisi berkas yang bisa menghabiskan disk, dan spam pesan).
 */
Route::middleware(['throttle:20,1'])->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{slug}', [JobController::class, 'show']);

    // Argumen ketiga throttle = AWALAN kunci. Tanpa awalan, semua throttle di
    // grup ini memakai SATU penghitung per IP (kuncinya hanya domain|IP):
    // membuka daftar lowongan, detail, lalu formulir sudah menghabiskan jatah
    // "5 lamaran" sehingga pelamar kena 429 — dan batas per jamnya tidak
    // pernah berlaku karena penghitungnya ikut kedaluwarsa tiap menit.

    // Unggah PDF — paling mahal, jadi dibatasi paling ketat.
    Route::post('/applications', [JobApplicationController::class, 'store'])
        ->middleware('throttle:5,60,lamaran');
    // Cek status lamaran (nomor + email). Dibatasi supaya nomor tidak bisa
    // ditebak beruntun.
    Route::post('/applications/status', [JobApplicationController::class, 'status'])
        ->middleware('throttle:10,10,cek-lamaran');

    Route::post('/message', [MessageController::class, 'store'])
        ->middleware('throttle:10,60,pesan');

    // Isi situs profil Asthana (baca saja): beranda, tim, portofolio, berita.
    Route::prefix('situs')->controller(\App\Http\Controllers\Api\SitusAsthanaController::class)->group(function () {
        Route::get('/beranda', 'beranda');
        Route::get('/karir', 'karir');
        Route::get('/sitemap-lowongan.xml', [\App\Http\Controllers\SitusLowonganController::class, 'sitemap']);
        Route::get('/tim', 'tim');
        Route::get('/portofolio', 'portofolio');
        Route::get('/portofolio/{slug}', 'portofolioDetail');
        Route::get('/berita', 'berita');
        Route::get('/berita/{slug}', 'beritaDetail');
    });
});

/*
 * Bot Turnitin — dipanggil skrip Tampermonkey di Chrome admin
 * (public/bot/phoenix-turnitin.user.js). Token Bearer dibuat admin di dashboard.
 * Lihat App\Support\BotTurnitin untuk alurnya.
 */
Route::prefix('bot-turnitin')
    ->middleware([\App\Http\Middleware\TokenBotTurnitin::class, 'throttle:120,1'])
    ->controller(\App\Http\Controllers\BotTurnitinController::class)
    ->group(function () {
        Route::post('/detak', 'detak');
        // Laporan keadaan VPS + jemput perintah dari agen Telegram.
        Route::post('/lapor', 'lapor');
        Route::get('/tugas', 'tugas');
        Route::get('/tugas/{upload}/berkas', 'berkas');
        Route::post('/tugas/{upload}/terkirim', 'terkirim');
        Route::post('/tugas/{upload}/hasil', 'hasil');
        Route::post('/tugas/{upload}/gagal', 'gagal');
    });
