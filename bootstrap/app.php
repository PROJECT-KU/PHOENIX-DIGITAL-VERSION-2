<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware global
        $middleware->use([
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Middleware groups (web, api)
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            \App\Http\Middleware\LastUserActivity::class,
            \App\Http\Middleware\IdleTimeout::class,
            \App\Http\Middleware\EnsureGuestToken::class,
            \App\Http\Middleware\EnsureProfileComplete::class,
            \App\Http\Middleware\KickScheduler::class,

            // Catat request lambat ke Log Aktivitas (diukur di terminate,
            // setelah response terkirim — tidak menambah waktu tunggu).
            \App\Http\Middleware\TrackRequestPerformance::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Middleware alias
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'checkrole' => CheckRole::class,
            'verify.origin' => \App\Http\Middleware\VerifyAllowedOrigin::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Catat setiap error ke Log Aktivitas (public & admin) untuk maintenance.
        // report() hanya MENAMBAH pencatatan — penanganan error bawaan Laravel
        // tetap berjalan (tidak return false). ActivityLogger menelan errornya
        // sendiri, jadi ini tidak dapat mengganggu alur aplikasi mana pun.
        $exceptions->report(function (\Throwable $e) {
            \App\Support\ActivityLogger::exception($e);
        });
    })
    ->create();
