<?php

namespace App\Support;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Alamat pratinjau untuk berkas yang baru diunggah dan belum disimpan.
 *
 * Dipakai di tampilan sebagai pengganti langsung `->temporaryUrl()`:
 *
 *     <img src="{{ \App\Support\PratinjauUnggahan::url($berkas) }}">
 *
 * Alasan tidak memakai bawaan Livewire ada di PratinjauUnggahanController —
 * ringkasnya, alamat bawaannya berakhiran .jpg dan lapisan pengoptimal gambar
 * di hosting membuang query string dari alamat gambar, padahal izin aksesnya
 * ada di situ.
 */
class PratinjauUnggahan
{
    /**
     * base64url: base64 biasa memakai "+" dan "/" yang keduanya berarti
     * sesuatu di dalam alamat. "/" bahkan akan memecah satu ruas jalur
     * menjadi dua dan membuat rutenya tidak cocok sama sekali.
     */
    public static function sandi(string $nama): string
    {
        return rtrim(strtr(base64_encode($nama), '+/', '-_'), '=');
    }

    public static function bacaSandi(string $sandi): ?string
    {
        $base64 = strtr($sandi, '-_', '+/');
        $nama = base64_decode($base64, true);

        return $nama === false ? null : $nama;
    }

    /**
     * Menerima apa pun yang diberikan tampilan.
     *
     * Nilai yang dilewatkan bisa berupa berkas sementara, bisa juga null saat
     * admin belum memilih apa-apa, atau bahkan jalur gambar lama berupa teks.
     * Yang bukan berkas sementara dikembalikan apa adanya, supaya pemanggilnya
     * tidak perlu bercabang lebih dulu.
     */
    public static function url($berkas): ?string
    {
        if ($berkas instanceof TemporaryUploadedFile) {
            return route('pratinjau.unggahan', ['sandi' => static::sandi($berkas->getFilename())]);
        }

        return is_string($berkas) && $berkas !== '' ? $berkas : null;
    }
}
