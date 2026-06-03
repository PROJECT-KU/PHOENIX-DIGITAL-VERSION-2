<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Promo;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $now = now();

    // Nonaktifkan yang selesai
    Promo::where('is_active', true)->where('selesai_promo', '<', $now)->update(['is_active' => false]);

    // Aktifkan yang mulai
    Promo::where('is_active', false)
        ->where('mulai_promo', '<=', $now)
        ->where('selesai_promo', '>=', $now)
        ->update(['is_active' => true]);
})->everyMinute(); // Jalankan setiap menit
