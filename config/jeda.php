<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alamat surel penerima pemberitahuan jeda modul
    |--------------------------------------------------------------------------
    |
    | Diisi HANYA saat uji coba. Selama terisi, semua pemberitahuan jeda modul
    | admin dikirim ke alamat ini saja dan tidak ke seorang karyawan pun —
    | supaya percobaan tidak mengganggu tim.
    |
    | Kosongkan di server: penerimanya lalu diambil dari data karyawan.
    |
    */
    'email_uji' => env('JEDA_EMAIL_UJI'),

];
