<?php

namespace App\Support;

/**
 * Pesan kontak yang menuntut dibaca admin.
 *
 * Dihitung dari yang BELUM DIBACA, bukan dari yang belum dibalas.
 *
 * Bukan karena itu ukuran yang paling tepat — yang sebenarnya ingin diketahui
 * admin adalah mana yang belum dijawab — melainkan karena balasannya dikirim
 * lewat WhatsApp, di luar sistem. Orcha tidak pernah tahu sebuah pesan sudah
 * dibalas atau belum, jadi satu-satunya penutup yang benar-benar tercatat
 * adalah "sudah dibaca". Labelnya di bilah samping dan lonceng menyebutnya apa
 * adanya, supaya angkanya tidak dibaca sebagai janji yang tidak bisa ditepati.
 *
 * Dipecah menurut umur, meniru penanda sewa:
 *
 *   baru — belum dibaca, masuk dalam sehari terakhir. Wajar masih menunggu.
 *   lama — belum dibaca lewat sehari. Ini yang menyakiti: orang yang bertanya
 *          sudah menunggu semalaman tanpa satu pun tanda pesannya sampai, dan
 *          yang datang berikutnya biasanya bukan pertanyaan lagi.
 */
class OrchaPesanPerhatian extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.pesan.perhatian';
    }

    protected static function jalur(): string
    {
        return '/pesan/perhatian';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['belum_dibaca' => 0, 'baru' => 0, 'lama' => 0];
    }

    /**
     * Seluruh yang belum dibaca.
     *
     * Dibaca dari 'belum_dibaca', bukan baru + lama: keduanya dihitung dari
     * kueri yang sama di Orcha, tetapi menjumlahkannya di sini berarti angka di
     * bilah samping bisa meleset diam-diam bila suatu saat pemecahannya
     * berubah.
     */
    public static function jumlah(): int
    {
        return self::ambil()['belum_dibaca'];
    }
}
