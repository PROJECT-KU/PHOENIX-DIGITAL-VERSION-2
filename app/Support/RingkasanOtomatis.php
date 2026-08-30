<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Memilih kalimat menarik dari isi artikel untuk dijadikan ringkasan.
 *
 * Perilakunya SAMA dengan yang sudah dipakai formulir blog Phoenix — admin
 * yang berpindah antara blog Phoenix dan blog Orcha tidak boleh merasa sedang
 * memakai dua alat berbeda.
 *
 * CATATAN untuk yang membaca nanti: logika ini masih kembar dengan metode
 * privat di App\Livewire\Pages\Admin\Blog\BlogForm. Dibiarkan begitu SENGAJA —
 * menyatukannya berarti menyunting formulir blog Phoenix yang sudah berjalan,
 * dan tuntutannya saat fitur ini dibuat adalah tidak menyentuh apa pun yang
 * sudah ada. Bila suatu saat ingin disatukan, lakukan sebagai perubahan
 * tersendiri yang bisa diuji dan dibatalkan sendiri, bukan menumpang di sini.
 */
class RingkasanOtomatis
{
    /**
     * Kata yang menaikkan skor "menarik" sebuah kalimat.
     *
     * Daftarnya condong ke kalimat yang MENJANJIKAN sesuatu kepada pembaca
     * ("cara", "tips", "hindari"), bukan kalimat yang sekadar menerangkan.
     * Itu yang dicari orang di hasil pencarian.
     */
    private const PEMIKAT = [
        'tips', 'cara', 'kenapa', 'mengapa', 'penting', 'gratis', 'mudah', 'terbaik',
        'hemat', 'aman', 'bergaransi', 'rahasia', 'panduan', 'solusi', 'cepat', 'wajib',
        'harus', 'manfaat', 'keuntungan', 'trik', 'langkah', 'pilih', 'hindari', 'kunci',
    ];

    /**
     * Buang tag dan rapikan spasi, sisakan teksnya saja.
     *
     * Batas antar-blok DIGANTI SPASI lebih dulu, bukan langsung dibuang.
     * strip_tags() menyambung "<p>A.</p><p>B.</p>" jadi "A.B." — tanpa spasi
     * sesudah titik. Akibatnya dua hal sekaligus:
     *
     *   1. Pemecah kalimat menuntut spasi sesudah titik, jadi seluruh paragraf
     *      menyatu jadi satu "kalimat" raksasa yang lalu dibuang karena
     *      melebihi 240 huruf. Pada satu artikel nyata, 19 dari 33 kalimat
     *      hilang karena ini.
     *   2. Ringkasan yang terlanjur dipakai terbaca sebagai "literaturLebih
     *      baik bangun" — dua kata mendempet, dan itu tampil di hasil Google.
     */
    public static function teksPolos(string $html): string
    {
        $t = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);

        // Tag penutup blok dan <br> jadi spasi, supaya kalimat tidak berdempet.
        $t = preg_replace('/<\/(p|div|li|h[1-6]|blockquote|tr|td|th)\s*>/i', ' ', (string) $t);
        $t = preg_replace('/<br\s*\/?>/i', ' ', (string) $t);

        $t = strip_tags((string) $t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /**
     * Ambil satu kalimat yang layak jadi ringkasan.
     *
     * Kalimat PERTAMA sengaja dibuang: pembuka artikel hampir selalu berupa
     * ancang-ancang yang belum menyatakan apa-apa, dan justru itulah yang akan
     * terpotong jadi ringkasan kalau teksnya sekadar dipenggal dari awal.
     *
     * Pilihannya diacak di antara kandidat terbaik, bukan selalu yang teratas,
     * supaya tombol "Acak lagi" benar-benar memberi pilihan berbeda.
     *
     * @param  string|null  $kecuali  kalimat yang sudah dipakai di tempat lain
     */
    public static function kalimatMenarik(string $teks, int $panjangMaks, ?string $kecuali = null): string
    {
        if ($teks === '') {
            return '';
        }

        $kalimat = preg_split('/(?<=[.!?])\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $kalimat = array_map('trim', $kalimat);

        if (count($kalimat) > 1) {
            array_shift($kalimat);
        }

        $berskor = [];

        foreach ($kalimat as $satu) {
            $panjang = mb_strlen($satu);

            // Terlalu pendek belum menyatakan apa-apa; terlalu panjang pasti
            // terpenggal di tengah saat ditampilkan Google.
            if ($panjang < 40 || $panjang > 240) {
                continue;
            }

            if ($kecuali !== null && $satu === $kecuali) {
                continue;
            }

            $skor = 0;
            $kecil = mb_strtolower($satu);

            foreach (self::PEMIKAT as $kata) {
                if (str_contains($kecil, $kata)) {
                    $skor += 3;
                }
            }

            if (preg_match('/\d/', $satu)) {
                $skor += 2;   // ada angka → cenderung informatif
            }

            if ($panjang >= 80 && $panjang <= 170) {
                $skor += 2;   // panjang ideal untuk ringkasan
            }

            $berskor[] = ['kalimat' => $satu, 'skor' => $skor];
        }

        if ($berskor === []) {
            return Str::limit($teks, $panjangMaks);
        }

        usort($berskor, fn ($a, $b) => $b['skor'] <=> $a['skor']);

        /*
         | Kandidat diambil sebanyak LIMA teratas, bukan "yang skornya dalam
         | jarak 2 dari tertinggi".
         |
         | Aturan jarak-2 runtuh begitu satu kalimat menang telak: pada artikel
         | nyata skornya 7 lalu 4, sehingga jendelanya hanya memuat satu
         | kalimat — dan tombol "Acak lagi" mengembalikan kalimat yang sama
         | terus-menerus. Tombol yang tidak mengubah apa pun lebih buruk
         | daripada tidak ada tombol: admin menekannya berkali-kali dan
         | mengira fiturnya rusak.
         */
        $kandidat = array_slice($berskor, 0, 5);

        return Str::limit($kandidat[array_rand($kandidat)]['kalimat'], $panjangMaks, '');
    }
}
