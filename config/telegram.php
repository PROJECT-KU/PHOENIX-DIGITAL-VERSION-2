<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bot Telegram (agen operasional)
    |--------------------------------------------------------------------------
    |
    | Bot ini HANYA melayani chat yang terdaftar di `chat_ids` dan hanya bisa
    | menjalankan aksi dari daftar putih (App\Services\Telegram\AksiAgen).
    | Tidak ada eksekusi perintah bebas.
    |
    | Token kosong => webhook 404 (bot nonaktif). Aman by default, sama pola
    | dengan config/cron.php.
    |
    */
    'token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    | Secret yang dikirim Telegram di header X-Telegram-Bot-Api-Secret-Token
    | pada tiap update. Dipasang saat `php artisan telegram:webhook pasang`.
    | Kosong => webhook menolak semua permintaan (tidak bisa dipakai tanpa ini).
    */
    'secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    /*
    | Daftar chat ID yang boleh memerintah bot, dipisah koma.
    | Chat lain diabaikan diam-diam (tidak dibalas, tidak bocor bahwa bot ada).
    | Belum tahu ID Anda? Jalankan `php artisan telegram:webhook id` lalu kirim
    | pesan apa pun ke bot.
    */
    'chat_ids' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TELEGRAM_ALLOWED_CHAT_IDS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Lapisan AI (OPSIONAL)
    |--------------------------------------------------------------------------
    |
    | Bila diisi, pesan bahasa bebas ("cron mati ya? tolong cek") diterjemahkan
    | menjadi salah satu aksi daftar putih. Bila KOSONG, bot tetap jalan penuh
    | lewat perintah slash — tanpa biaya dan tanpa memanggil layanan luar.
    |
    | driver 'openai'    => endpoint OpenAI-compatible: 9Router, OpenRouter, dll.
    |                       base_url contoh: http://127.0.0.1:20128/v1
    | driver 'anthropic' => API Anthropic langsung (https://api.anthropic.com/v1)
    |
    | PENTING: yang dikirim ke layanan AI HANYA teks perintah Anda + daftar nama
    | aksi. Tidak ada data customer, isi pesanan, atau kredensial yang dikirim.
    | Lihat AgenAi::promptSistem().
    |
    */
    'ai' => [
        'driver' => env('TELEGRAM_AI_DRIVER', 'openai'),
        'base_url' => env('TELEGRAM_AI_BASE_URL'),
        'api_key' => env('TELEGRAM_AI_API_KEY'),
        'model' => env('TELEGRAM_AI_MODEL'),
        'timeout' => (int) env('TELEGRAM_AI_TIMEOUT', 20),
    ],
];
