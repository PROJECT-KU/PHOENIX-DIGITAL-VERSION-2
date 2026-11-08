<?php

/*
 | Situs profil PT. Asthana Cipta Mandiri (repo pt-asthana-cipta-mandiri).
 | Situsnya statis; lowongan, lamaran, pesan, dan isinya disajikan dari sini.
 */
return [
    // Kirim surel tanda terima ke pengirim pesan & pelamar dari situs.
    'balasan_otomatis' => (bool) env('ASTHANA_BALASAN_OTOMATIS', true),

    // Alamat situs, untuk tautan di surel. Kosong = tautan tidak ditampilkan.
    'situs_url' => rtrim((string) env('ASTHANA_SITUS_URL', ''), '/'),
];
