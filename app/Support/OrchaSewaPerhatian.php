<?php

namespace App\Support;

/**
 * Sewa kendaraan yang menuntut tindakan admin.
 *
 * Tiga hal, dan sengaja tetap terpisah karena beda urusannya:
 *
 *   baru  — pemesanan yang belum disentuh siapa pun. Pelanggan sudah mengirim
 *           formulir dan sedang menunggu dijawab.
 *
 *   telat — unit yang sudah lewat tenggat DAN BELUM dicatat kembali. Yang sudah
 *           kembali tidak ikut, sekalipun kembalinya telat: pekerjaan menagihnya
 *           sudah selesai.
 *
 *   denda — unit sudah kembali, sistem punya usulan dendanya, tetapi tidak satu
 *           rupiah pun ditetapkan. Uangnya belum ditagihkan dan nota yang
 *           dikirim ke penyewa masih menyebut Rp 0.
 *
 * Bilah samping menyebut jumlah keduanya — satu menu, satu angka — sementara
 * lonceng memisahnya jadi dua baris, karena di sana ada ruang untuk menjelaskan
 * dan kalimat yang dipakai admin untuk keduanya memang berbeda.
 */
class OrchaSewaPerhatian extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.penyewaan.perhatian';
    }

    protected static function jalur(): string
    {
        return '/penyewaan/perhatian';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['baru' => 0, 'telat' => 0, 'denda' => 0];
    }

    public static function jumlah(): int
    {
        $hitung = self::ambil();

        return $hitung['baru'] + $hitung['telat'] + $hitung['denda'];
    }
}
