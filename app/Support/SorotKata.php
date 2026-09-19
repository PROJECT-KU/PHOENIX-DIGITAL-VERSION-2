<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Menyorot kata pencarian di dalam teks hasil.
 *
 * Teksnya di-escape LEBIH DULU, baru <mark> disisipkan — urutan sebaliknya
 * membuat isi testimoni (yang ditulis orang luar) bisa menyuntikkan HTML.
 */
class SorotKata
{
    public static function pada(?string $teks, ?string $kata): HtmlString
    {
        $teks = (string) $teks;
        $kata = trim((string) $kata);

        if ($kata === '' || mb_strlen($kata) < 2) {
            return new HtmlString(e($teks));
        }

        $aman = e($teks);
        $pola = '/'.preg_quote(e($kata), '/').'/iu';

        return new HtmlString((string) preg_replace($pola, '<mark class="tm-sorot-kata">$0</mark>', $aman));
    }
}
