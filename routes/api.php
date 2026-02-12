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

// Route::middleware(['verify.origin'])->group(function () {
Route::middleware([])->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{slug}', [JobController::class, 'show']);
    Route::post('/applications', [JobApplicationController::class, 'store']);
    Route::post('/message', [MessageController::class, 'store']);
});
