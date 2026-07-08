<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Promo;
use App\Observers\OrderObserver;
use App\Services\PromoService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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

        // memanggil data pormos di $headerPromos
        View::composer('layouts.guest', function ($view) {
            $promos = Promo::where('is_active', true)
                ->where(function ($query) {
                    $query->where('diskon_member_nominal', '>', 0)
                        ->orWhere('diskon_member_persen', '>', 0)
                        ->orWhere('diskon_non_member_nominal', '>', 0)
                        ->orWhere('diskon_non_member_persen', '>', 0);
                })
                ->get();

            $view->with('headerPromos', $promos);
        });
    }
}
