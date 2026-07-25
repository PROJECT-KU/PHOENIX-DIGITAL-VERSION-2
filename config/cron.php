<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token pemicu cron eksternal
    |--------------------------------------------------------------------------
    |
    | Dipakai route /cron/run/{token} sebagai CADANGAN bila cron hPanel
    | (schedule:run) mati. Layanan cron eksternal (mis. cron-job.org) memanggil
    | URL itu tiap menit. Kosong = route dinonaktifkan (aman by default).
    |
    */
    'token' => env('CRON_TOKEN'),
];
