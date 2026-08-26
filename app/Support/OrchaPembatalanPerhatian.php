<?php

namespace App\Support;

/**
 * Pengajuan pembatalan yang menuntut tindakan admin.
 *
 * Dihitung PER STATUS, bukan digabung jadi satu angka. Tiap status menuntut
 * perbuatan yang berbeda, dan admin yang membaca penandanya perlu tahu langsung
 * mana yang harus dikerjakan lebih dulu:
 *
 *   diajukan  — belum disentuh siapa pun; pemohon menunggu dijawab.
 *   diproses  — sudah dipegang, keputusannya belum selesai.
 *   disetujui — sudah diputuskan tetapi dananya BELUM dikirim. Ini yang paling
 *               mahal dibiarkan: uang pelanggan sudah dinyatakan kembali tetapi
 *               belum berangkat ke mana-mana. Yang menunggu bukan lagi jawaban,
 *               melainkan uangnya sendiri.
 *
 * dana_dikirim dan ditolak tidak dihitung: keduanya sudah selesai.
 */
class OrchaPembatalanPerhatian extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.pembatalan.perhatian';
    }

    protected static function jalur(): string
    {
        return '/pembatalan/perhatian';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0];
    }

    public static function jumlah(): int
    {
        $hitung = self::ambil();

        return $hitung['diajukan'] + $hitung['diproses'] + $hitung['disetujui'];
    }
}
