<?php

namespace App\Support;

/**
 * Ikon & warna kartu add-on jasa di halaman detail produk.
 *
 * Murni tampilan: add-on tidak punya kolom ikon, jadi jenisnya ditebak dari
 * sifatnya (cek_ai) lalu namanya. Urutan pengecekan penting — "Cek Plagiasi
 * Turnitin" dan "Cek Plagiasi AI" sama-sama memuat kata "plagiasi".
 */
class IkonAddon
{
    public const BAWAAN = ['ikon' => 'bi-stars', 'warna' => '#d97706'];

    /** @return array{ikon: string, warna: string} */
    public static function untuk(?string $nama, bool $cekAi = false): array
    {
        $n = mb_strtolower((string) $nama);

        if ($cekAi || preg_match('/\bai\b/u', $n)) {
            return ['ikon' => 'bi-robot', 'warna' => '#7c3aed'];
        }

        if (str_contains($n, 'turnitin')) {
            return ['ikon' => 'bi-patch-check', 'warna' => '#2563eb'];
        }

        if (str_contains($n, 'plagiasi') || str_contains($n, 'similarity')) {
            return ['ikon' => 'bi-shield-check', 'warna' => '#16a34a'];
        }

        if (str_contains($n, 'kilat') || str_contains($n, 'express') || str_contains($n, 'cepat')) {
            return ['ikon' => 'bi-lightning-charge', 'warna' => '#e11d48'];
        }

        return self::BAWAAN;
    }
}
