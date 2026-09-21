<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Mengubah berkas tulisan dari luar (Markdown, HTML, teks polos) menjadi
 * judul + isi HTML yang bisa dibuka editor artikel.
 *
 * Sengaja sederhana dan tanpa pustaka tambahan: hasilnya selalu masuk
 * sebagai DRAF untuk diperiksa manusia, jadi yang dibutuhkan cuma konversi
 * yang cukup baik, bukan yang sempurna.
 */
class ImporArtikel
{
    /**
     * @return array{0: string, 1: string} [judul, isi HTML]
     */
    public static function urai(string $isi, string $namaBerkas, string $jenis): array
    {
        [$judul, $tubuh] = self::uraiLengkap($isi, $namaBerkas, $jenis);

        return [$judul, $tubuh];
    }

    /**
     * Sama dengan urai(), tetapi juga mengembalikan medan dari front-matter.
     *
     * @return array{0: string, 1: string, 2: array<string, mixed>}
     */
    public static function uraiLengkap(string $isi, string $namaBerkas, string $jenis): array
    {
        $isi = str_replace(["\r\n", "\r"], "\n", trim($isi));

        // Berkas hasil ekspor sistem ini sendiri berkepala front-matter.
        // Tanpa ini, "title: ..." dan kawan-kawannya ikut jadi isi tulisan
        // dan judulnya diambil dari nama berkas — ekspor lalu impor kembali
        // menghasilkan draf yang salah.
        [$kepala, $isi] = self::pisahkanFrontMatter($isi);

        if (in_array($jenis, ['html', 'htm'], true)) {
            $judul = $kepala['title'] ?? self::judulDariHtml($isi, $namaBerkas);

            return [$judul, self::potongBadan($isi), $kepala];
        }

        [$judulMd, $tubuh] = self::dariMarkdown($isi, $namaBerkas);

        return [$kepala['title'] ?? $judulMd, $tubuh, $kepala];
    }

    /**
     * Pisahkan blok "---" di awal berkas beserta isinya.
     *
     * Penguraiannya sengaja sederhana — hanya "kunci: nilai" dan daftar
     * ["a", "b"] — karena itulah yang ditulis EksporMarkdown. Berkas YAML
     * rumit dari tempat lain tetap aman: yang tidak dikenali diabaikan.
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    private static function pisahkanFrontMatter(string $isi): array
    {
        if (! str_starts_with($isi, '---')) {
            return [[], $isi];
        }

        $baris = explode("\n", $isi);
        array_shift($baris);

        $kepala = [];
        $tutup = false;

        while ($baris) {
            $b = array_shift($baris);

            if (trim($b) === '---') {
                $tutup = true;
                break;
            }

            if (! preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*:\s*(.*)$/', trim($b), $m)) {
                continue;
            }

            $kepala[$m[1]] = self::nilaiFrontMatter(trim($m[2]));
        }

        // Tanpa penutup, "---" itu ternyata bukan front-matter (mis. garis
        // pemisah di awal tulisan). Naskahnya dikembalikan utuh.
        if (! $tutup) {
            return [[], $isi];
        }

        return [$kepala, ltrim(implode("\n", $baris))];
    }

    private static function nilaiFrontMatter(string $nilai)
    {
        if (str_starts_with($nilai, '[') && str_ends_with($nilai, ']')) {
            $daftar = array_map(
                fn ($v) => self::lepasKutip(trim($v)),
                array_filter(explode(',', trim($nilai, '[]')), fn ($v) => trim($v) !== '')
            );

            return array_values(array_filter($daftar, 'strlen'));
        }

        return self::lepasKutip($nilai);
    }

    private static function lepasKutip(string $nilai): string
    {
        $nilai = trim($nilai);

        if (strlen($nilai) >= 2 && $nilai[0] === '"' && str_ends_with($nilai, '"')) {
            $nilai = substr($nilai, 1, -1);
        }

        return str_replace(['\\"', '\\\\'], ['"', '\\'], $nilai);
    }

    private static function judulDariHtml(string $html, string $cadangan): string
    {
        foreach (['/<h1\b[^>]*>(.*?)<\/h1>/is', '/<title\b[^>]*>(.*?)<\/title>/is'] as $pola) {
            if (preg_match($pola, $html, $m)) {
                $judul = trim(html_entity_decode(strip_tags($m[1])));
                if ($judul !== '') {
                    return Str::limit($judul, 180, '');
                }
            }
        }

        return self::judulCadangan($cadangan);
    }

    /** Ambil isi <body> saja bila berkasnya halaman utuh. */
    private static function potongBadan(string $html): string
    {
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $m)) {
            $html = $m[1];
        }

        // Skrip dan gaya dari berkas luar tidak pernah ikut: isi artikel
        // disaring lagi saat ditampilkan, tapi menyimpannya pun tidak ada
        // gunanya.
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html);

        return trim((string) $html);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function dariMarkdown(string $teks, string $cadangan): array
    {
        $baris = explode("\n", $teks);
        $judul = null;

        // Judul = "# Judul" pertama, kalau ada; barisnya lalu dibuang supaya
        // tidak tampil dua kali di dalam artikel.
        foreach ($baris as $i => $b) {
            if (preg_match('/^#\s+(.+)$/', trim($b), $m)) {
                $judul = trim($m[1]);
                unset($baris[$i]);
                break;
            }
        }

        $html = self::markdownKeHtml(implode("\n", $baris));

        return [$judul ? Str::limit($judul, 180, '') : self::judulCadangan($cadangan), $html];
    }

    private static function markdownKeHtml(string $teks): string
    {
        $keluar = [];

        foreach (preg_split('/\n{2,}/', trim($teks)) as $blok) {
            $blok = trim($blok);

            if ($blok === '') {
                continue;
            }

            // Judul hanya memakan BARIS PERTAMA. Berkas dari tempat lain
            // sering menulis judul dan paragraf berikutnya tanpa baris kosong
            // pemisah; tanpa pemisahan ini keduanya melebur jadi satu judul.
            if (preg_match('/^(#{1,6})\s+([^\n]+)(?:\n(.*))?$/s', $blok, $m)) {
                $tingkat = min(6, max(2, strlen($m[1])));
                $keluar[] = '<h'.$tingkat.'>'.self::sebaris(trim($m[2])).'</h'.$tingkat.'>';

                $sisa = trim($m[3] ?? '');
                if ($sisa !== '') {
                    $keluar[] = self::markdownKeHtml($sisa);
                }

                continue;
            }

            if (preg_match('/^>\s?/', $blok)) {
                $kutipan = preg_replace('/^>\s?/m', '', $blok);
                $keluar[] = '<blockquote><p>'.self::sebaris($kutipan).'</p></blockquote>';

                continue;
            }

            if (preg_match('/^([-*+]|\d+\.)\s+/', $blok)) {
                $urut = (bool) preg_match('/^\d+\.\s+/', $blok);
                $butir = [];
                foreach (explode("\n", $blok) as $b) {
                    $b = preg_replace('/^\s*([-*+]|\d+\.)\s+/', '', trim($b));
                    if ($b !== '') {
                        $butir[] = '<li>'.self::sebaris($b).'</li>';
                    }
                }
                $tag = $urut ? 'ol' : 'ul';
                $keluar[] = '<'.$tag.'>'.implode('', $butir).'</'.$tag.'>';

                continue;
            }

            $keluar[] = '<p>'.self::sebaris($blok).'</p>';
        }

        return implode("\n", $keluar);
    }

    /** Tebal, miring, tautan, dan pindah baris di dalam satu blok. */
    private static function sebaris(string $teks): string
    {
        $aman = e($teks);

        $aman = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $aman);
        $aman = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $aman);
        $aman = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/s', '<a href="$2">$1</a>', $aman);

        return nl2br(trim((string) $aman), false);
    }

    private static function judulCadangan(string $namaBerkas): string
    {
        $judul = trim(str_replace(['-', '_'], ' ', $namaBerkas));

        return $judul === '' ? 'Artikel Impor' : Str::limit(Str::title($judul), 180, '');
    }
}
