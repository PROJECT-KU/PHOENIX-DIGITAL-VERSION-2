<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Membandingkan dua naskah dan menandai apa yang berubah.
 *
 * Dibandingkan per KATA, bukan per baris: artikel ditulis sebagai paragraf
 * panjang, dan beda per baris cuma menandai seluruh paragraf berubah tanpa
 * memberi tahu apa yang sebenarnya diganti.
 */
class BedaTeks
{
    /** Batas kata yang dibandingkan — naskah panjang dipotong supaya cepat. */
    public const BATAS_KATA = 4000;

    public static function antara(?string $lama, ?string $baru): HtmlString
    {
        $a = self::kata($lama);
        $b = self::kata($baru);

        if ($a === $b) {
            return new HtmlString('<span class="bd-sama">Tidak ada perbedaan isi.</span>');
        }

        $keping = self::selisih($a, $b);

        $html = '';
        foreach ($keping as [$tanda, $potongan]) {
            $teks = e(implode(' ', $potongan));

            $html .= match ($tanda) {
                'buang' => '<del class="bd-buang">'.$teks.'</del> ',
                'tambah' => '<ins class="bd-tambah">'.$teks.'</ins> ',
                default => $teks.' ',
            };
        }

        return new HtmlString(trim($html));
    }

    /** Ringkasan pendek: "+42 kata, −7 kata". */
    public static function ringkas(?string $lama, ?string $baru): string
    {
        $a = self::kata($lama);
        $b = self::kata($baru);

        $tambah = 0;
        $buang = 0;

        foreach (self::selisih($a, $b) as [$tanda, $potongan]) {
            if ($tanda === 'tambah') {
                $tambah += count($potongan);
            } elseif ($tanda === 'buang') {
                $buang += count($potongan);
            }
        }

        if ($tambah === 0 && $buang === 0) {
            return 'isi sama';
        }

        $bagian = [];
        if ($tambah) {
            $bagian[] = '+'.$tambah.' kata';
        }
        if ($buang) {
            $bagian[] = '−'.$buang.' kata';
        }

        return implode(', ', $bagian);
    }

    /** @return array<int, string> */
    private static function kata(?string $html): array
    {
        $teks = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))));

        if ($teks === '') {
            return [];
        }

        return array_slice(preg_split('/\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY), 0, self::BATAS_KATA);
    }

    /**
     * Selisih dua deret kata (suburutan sama terpanjang).
     *
     * @return array<int, array{0: string, 1: array<int, string>}>
     */
    private static function selisih(array $a, array $b): array
    {
        $panjangA = count($a);
        $panjangB = count($b);

        // Tabel LCS. Dibatasi BATAS_KATA supaya memakan memori yang terduga
        // (4000 × 4000 integer masih aman, dan artikel sepanjang itu langka).
        $tabel = array_fill(0, $panjangA + 1, array_fill(0, $panjangB + 1, 0));

        for ($i = $panjangA - 1; $i >= 0; $i--) {
            for ($j = $panjangB - 1; $j >= 0; $j--) {
                $tabel[$i][$j] = $a[$i] === $b[$j]
                    ? $tabel[$i + 1][$j + 1] + 1
                    : max($tabel[$i + 1][$j], $tabel[$i][$j + 1]);
            }
        }

        $hasil = [];
        $i = 0;
        $j = 0;

        $tambahkan = function (string $tanda, string $kata) use (&$hasil) {
            $akhir = count($hasil) - 1;
            if ($akhir >= 0 && $hasil[$akhir][0] === $tanda) {
                $hasil[$akhir][1][] = $kata;

                return;
            }

            $hasil[] = [$tanda, [$kata]];
        };

        while ($i < $panjangA && $j < $panjangB) {
            if ($a[$i] === $b[$j]) {
                $tambahkan('sama', $a[$i]);
                $i++;
                $j++;
            } elseif ($tabel[$i + 1][$j] >= $tabel[$i][$j + 1]) {
                $tambahkan('buang', $a[$i]);
                $i++;
            } else {
                $tambahkan('tambah', $b[$j]);
                $j++;
            }
        }

        while ($i < $panjangA) {
            $tambahkan('buang', $a[$i++]);
        }

        while ($j < $panjangB) {
            $tambahkan('tambah', $b[$j++]);
        }

        return $hasil;
    }
}
