<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Daftar URI yang dikecualikan dari verifikasi CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Webhook Telegram: dipanggil server Telegram, tidak punya sesi/token
        // CSRF. Keasliannya diverifikasi lewat header secret + daftar chat ID
        // di TelegramWebhookController.
        'telegram/webhook',
    ];
}
