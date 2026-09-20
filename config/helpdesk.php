<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Topik tiket
    |--------------------------------------------------------------------------
    | Dipakai untuk menyaring dan meringkas "keluhan terbanyak soal apa".
    | Kuncinya disimpan di kolom `kategori`, jadi jangan mengubah kunci yang
    | sudah dipakai — tambah yang baru saja.
    */
    'kategori' => [
        'tanya_produk' => 'Tanya produk',
        'komplain' => 'Komplain / kendala',
        'jasa' => 'Jasa cek plagiasi & AI',
        'pembayaran' => 'Pembayaran',
        'kerja_sama' => 'Kerja sama',
        'lainnya' => 'Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Batas waktu membalas (jam) per prioritas
    |--------------------------------------------------------------------------
    | Dihitung dari pesan masuk sampai DIBALAS. Lewat batas hanya jadi penanda
    | di layar — tidak ada yang dibatalkan otomatis.
    */
    'batas_jam' => [
        'urgent' => 2,
        'high' => 6,
        'medium' => 12,
        'low' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Jam kerja (untuk menghitung batas waktu)
    |--------------------------------------------------------------------------
    | Batas waktu membalas dihitung memakai jam ini, bukan jam kalender —
    | tiket yang masuk tengah malam tidak dianggap terlambat saat subuh.
    */
    'jam_kerja' => [
        'mulai' => 8,
        'selesai' => 21,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiket Selesai ditutup otomatis setelah sekian hari
    |--------------------------------------------------------------------------
    */
    'tutup_setelah_hari' => 7,

    /*
    |--------------------------------------------------------------------------
    | Kanal balasan yang bisa dicatat
    |--------------------------------------------------------------------------
    */
    'kanal' => [
        'whatsapp' => 'WhatsApp',
        'email' => 'Surel',
        'telepon' => 'Telepon',
        'lainnya' => 'Lainnya',
    ],

];
