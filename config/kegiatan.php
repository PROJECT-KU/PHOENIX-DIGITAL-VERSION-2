<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alamat surel penerima kabar kegiatan
    |--------------------------------------------------------------------------
    |
    | Diisi HANYA saat uji coba. Selama terisi, seluruh undangan, perubahan,
    | dan pembatalan kegiatan dikirim ke alamat ini saja dan tidak ke seorang
    | peserta pun — supaya percobaan tidak mengganggu tim.
    |
    | Kosongkan di server: penerimanya lalu peserta yang benar-benar dipilih.
    |
    | Bawaannya mengikuti JEDA_EMAIL_UJI agar satu sakelar sudah cukup untuk
    | seluruh surel internal, tapi tetap bisa dipisah bila suatu saat perlu.
    |
    */
    'email_uji' => env('KEGIATAN_EMAIL_UJI', env('JEDA_EMAIL_UJI')),

];
