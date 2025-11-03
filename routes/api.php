<?php

use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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

// Route::middleware(['verify.origin'])->group(function () {
Route::middleware([])->group(function () {
    Route::get('/jobs', [JobController::class, 'index']); // untuk dropdown posisi

    Route::post('/applications', [JobApplicationController::class, 'store']);
});
