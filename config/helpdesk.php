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
    | Penanggung jawab per topik
    |--------------------------------------------------------------------------
    | Dipakai pengingat tiket lewat batas yang BELUM ditugaskan: rekapnya
    | dikirim ke pemegang izin berikut, bukan ke semua orang. Topik yang tidak
    | terdaftar jatuh ke pemegang izin edit_customer_message.
    */
    'penanggung_topik' => [
        'pembayaran' => 'view_cashflow',
        'jasa' => 'view_pemesanantoko',
    ],

    /*
    |--------------------------------------------------------------------------
    | Masa berlaku tautan lacak tiket di surel (hari)
    |--------------------------------------------------------------------------
    | Surel bisa diteruskan ke orang lain; tautannya tidak boleh berlaku
    | selamanya. Halaman /tiket dengan nomor + surel tetap jadi jalan cadangan.
    */
    'masa_tautan_hari' => 90,

    /*
    |--------------------------------------------------------------------------
    | Batas lampiran per tiket
    |--------------------------------------------------------------------------
    | Berkasnya baru terbuang saat tiket dihapus permanen, jadi tanpa batas ini
    | satu tiket bisa menyeret puluhan MB ke disk hosting untuk selamanya.
    */
    'lampiran_maks' => 10,
    'lampiran_maks_mb' => 40,

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
