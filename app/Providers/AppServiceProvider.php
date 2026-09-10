<?php

namespace App\Providers;

use App\Listeners\SendWebPushNotification;
use App\Models\Order;
use App\Models\Promo;
use App\Observers\OrderObserver;
use App\Services\PromoService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PromoService::class, function ($app) {
            return new PromoService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);
        RateLimiter::for('job-applications', function ($request) {
            return [Limit::perMinute(100)->by($request->ip())];
        });
        Order::observe(OrderObserver::class);
        Order::observe(\App\Observers\OrderEmailObserver::class);

        // Kirim Web Push otomatis untuk setiap notifikasi database (badge PWA di background).
        Event::listen(NotificationSent::class, SendWebPushNotification::class);

        // Log Aktivitas — jejak auth. Listener PASIF (hanya membaca event, tidak
        // mengubah alur login) & ActivityLogger menelan errornya sendiri, jadi
        // ini tidak mengganggu proses autentikasi.
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Support\ActivityLogger::auth('login', 'Login berhasil: '.($event->user->name ?? '-'));
        });
        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            \App\Support\ActivityLogger::auth('logout', 'Logout: '.($event->user->name ?? '-'));
        });
        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            $email = $event->credentials['email'] ?? ($event->credentials['name'] ?? '-');
            \App\Support\ActivityLogger::auth('login_failed', 'Login gagal untuk: '.$email, 'warning');
        });

        // Email reset kata sandi kustom (desain lemon, seragam dengan login).
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Reset Kata Sandi — lemon by ACM')
                ->view('emails.reset-password', [
                    'url' => $url,
                    'user' => $notifiable,
                    'expire' => $expire,
                ]);
        });

        // Blade directive untuk check permission
        Blade::if('hasPermission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // Blade directive untuk check any permission
        Blade::if('hasAnyPermission', function (...$permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });

        // Blade directive untuk check role (yang sudah ada)
        Blade::if('hasRole', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Promo yang diumumkan di pita puncak SETIAP halaman.
        //
        // Sebelumnya penyaringnya hanya `is_active` dan "ada potongannya" —
        // TANPA memeriksa tanggal sama sekali. Promo yang jadwalnya sudah lewat
        // berbulan-bulan tetap diumumkan di puncak halaman, dan pengunjung yang
        // mengkliknya menemukan potongan yang tidak berlaku lagi. Diperbaiki
        // dengan scope active(): jendela tanggal DAN sisa kuota sekaligus,
        // aturan yang sama yang dipakai seluruh perhitungan promo lain.
        View::composer('layouts.guest', function ($view) {
            $promos = Promo::active()
                ->where(function ($query) {
                    $query->where('diskon_member_nominal', '>', 0)
                        ->orWhere('diskon_member_persen', '>', 0)
                        ->orWhere('diskon_non_member_nominal', '>', 0)
                        ->orWhere('diskon_non_member_persen', '>', 0);
                })
                ->orderByDesc('prioritas')
                ->get();

            $view->with('headerPromos', $promos);
        });
    }
}
