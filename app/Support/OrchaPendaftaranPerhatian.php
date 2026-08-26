<?php

namespace App\Support;

/**
 * Pendaftaran open trip yang menuntut tindakan admin.
 *
 * Dihitung PER KEADAAN, bukan digabung jadi satu angka. Tiap keadaan menuntut
 * perbuatan yang berbeda, dan admin yang membaca penandanya perlu tahu langsung
 * mana yang harus dikerjakan lebih dulu:
 *
 *   baru        — belum disentuh siapa pun; pemesannya menunggu dijawab.
 *   dihubungi   — sudah dihubungi, tetapi belum satu rupiah pun masuk.
 *   telat_lunas — sudah DP, belum lunas, DAN tenggat pelunasannya sudah lewat.
 *                 Ini yang paling mahal dibiarkan: kursinya tertahan atas nama
 *                 orang yang belum tentu berangkat, dan makin dekat hari-H
 *                 makin sulit dijual ulang.
 *
 * Yang lunas dan yang batal tidak dihitung — keduanya sudah selesai.
 */
class OrchaPendaftaranPerhatian extends HitunganOrcha
{
    protected static function kunci(): string
    {
        return 'orcha.pendaftaran.perhatian';
    }

    protected static function jalur(): string
    {
        return '/pendaftaran/perhatian';
    }

    /** @return array<string, int> */
    protected static function bawaan(): array
    {
        return ['baru' => 0, 'dihubungi' => 0, 'telat_lunas' => 0];
    }

    public static function jumlah(): int
    {
        $hitung = self::ambil();

        return $hitung['baru'] + $hitung['dihubungi'] + $hitung['telat_lunas'];
    }
}
