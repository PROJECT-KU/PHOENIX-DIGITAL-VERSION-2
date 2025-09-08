<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage')->name('home');

Route::view('dashboard', 'pages.admin.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
