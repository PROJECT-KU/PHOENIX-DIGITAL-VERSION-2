<?php

namespace App\Support;

/**
 * Pembuat tautan WhatsApp untuk pesan yang dikirim admin ke pelanggan.
 *
 * SELALU memakai endpoint api.whatsapp.com, JANGAN wa.me.
 *
 * Alasannya bukan selera: lewat wa.me, emoji dan sebagian tanda baca sering
 * tidak terbaca di WhatsApp Web maupun Desktop — pesan sampai dalam keadaan
 * rusak, dan admin tidak pernah tahu karena di layarnya sendiri tampak
 * baik-baik saja. Endpoint api.whatsapp.com menampilkannya konsisten.
 *
 * Dikumpulkan di satu kelas supaya aturan ini tidak perlu diingat ulang di
 * tiap tempat. Sebelumnya pola yang benar hanya hidup sebagai komentar di dua
 * berkas blade, sementara dua pemanggil di PHP tetap memakai wa.me.
 */
class TautanWa
{
    /**
     * Tautan WhatsApp siap pakai, atau string kosong bila nomornya tidak ada.
     *
     * String kosong dipilih alih-alih null supaya pemanggil bisa memakainya
     * langsung sebagai penanda tampil/sembunyi tombol di blade. Tanpa
     * penjagaan nomor, tautannya tetap terbuka tetapi ke percakapan kosong —
     * dan admin akan mengira pesannya sudah terkirim.
     */
    public static function kirim(?string $nomor, string $pesan): string
    {
        $telepon = self::nomor($nomor);

        if ($telepon === '') {
            return '';
        }

        return 'https://api.whatsapp.com/send?phone='.$telepon
            .'&text='.rawurlencode($pesan);
    }

    /**
     * Nomor ke bentuk internasional tanpa tanda baca: "0812…" -> "62812…".
     *
     * Mengembalikan string kosong bila tidak ada angka sama sekali.
     */
    public static function nomor(?string $nomor): string
    {
        $digit = preg_replace('/\D/', '', (string) $nomor);

        if ($digit === '') {
            return '';
        }

        return str_starts_with($digit, '0') ? '62'.substr($digit, 1) : $digit;
    }
}
