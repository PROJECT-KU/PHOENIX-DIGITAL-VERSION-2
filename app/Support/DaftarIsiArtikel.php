<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Daftar isi artikel blog, disusun dari subjudul <h2> di isinya.
 *
 * Disusun di SERVER, bukan dengan JavaScript: halaman blog dibuka lewat
 * wire:navigate, dan skrip yang membangun daftar isi dari DOM harus diikat
 * ulang di tiap navigasi — kalau lupa, daftarnya kosong tanpa galat apa pun.
 *
 * Menerima HTML yang SUDAH disaring HtmlSanitizer. Penyaring membuang atribut
 * id, jadi id jangkar ditambahkan di sini, sesudahnya, dan hanya berupa slug.
 */
class DaftarIsiArtikel
{
    /** Subjudul yang ditulis penulis sebagai daftar isi manual — bukan bagian. */
    private const BUKAN_BAGIAN = ['daftar isi', 'table of contents'];

    /**
     * @return array{html: string, daftar: list<array{id: string, teks: string}>}
     */
    public static function susun(string $html): array
    {
        $daftar = [];
        $dipakai = [];

        $html = preg_replace_callback('~<h2(\s[^>]*)?>(.*?)</h2>~is', function ($m) use (&$daftar, &$dipakai) {
            $atribut = $m[1] ?? '';
            $teks = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

            if ($teks === '' || in_array(mb_strtolower($teks), self::BUKAN_BAGIAN, true)
                || preg_match('~\sid\s*=~i', $atribut)) {
                return $m[0];
            }

            $dasar = 'bagian-'.(Str::slug($teks) ?: 'judul');
            $id = $dasar;
            for ($i = 2; isset($dipakai[$id]); $i++) {
                $id = $dasar.'-'.$i;
            }
            $dipakai[$id] = true;
            // Daftar isi sudah memberi nomor sendiri; "1. Parafrase…" dari
            // judul penulis tidak diulang di sana (judul di artikel tetap utuh).
            $daftar[] = ['id' => $id, 'teks' => preg_replace('/^\d+\s*[.)]\s*/u', '', $teks) ?: $teks];

            return '<h2 id="'.$id.'"'.$atribut.'>'.$m[2].'</h2>';
        }, $html) ?? $html;

        return ['html' => $html, 'daftar' => $daftar];
    }
}
