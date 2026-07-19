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

    // Unggah 2 PDF — paling mahal, jadi dibatasi paling ketat.
    Route::post('/applications', [JobApplicationController::class, 'store'])
        ->middleware('throttle:5,60');

    Route::post('/message', [MessageController::class, 'store'])
        ->middleware('throttle:10,60');
});
