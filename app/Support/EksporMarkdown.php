<?php

namespace App\Support;

use App\Models\BlogPost;

/**
 * Mengubah satu artikel menjadi naskah Markdown berkepala front-matter.
 *
 * Bentuk ini yang dipahami hampir semua penerbit statis (Hugo, Astro,
 * Jekyll), jadi tulisan di sini tidak terkunci di satu aplikasi.
 */
class EksporMarkdown
{
    public static function naskah(BlogPost $artikel): string
    {
        $kepala = [
            'title' => $artikel->title,
            'slug' => $artikel->slug,
            'category' => $artikel->category,
            'tags' => $artikel->tagDaftar(),
            'status' => $artikel->status,
            'published_at' => optional($artikel->published_at)->toAtomString(),
            'excerpt' => $artikel->excerpt,
            'meta_title' => $artikel->meta_title,
            'meta_description' => $artikel->meta_description,
            'cover' => $artikel->cover ? asset('storage/img/blog/'.$artikel->cover) : null,
            'cover_alt' => $artikel->cover_alt,
        ];

        $baris = ['---'];

        foreach ($kepala as $kunci => $nilai) {
            if ($nilai === null || $nilai === '' || $nilai === []) {
                continue;
            }

            $baris[] = is_array($nilai)
                ? $kunci.': ['.collect($nilai)->map(fn ($v) => self::kutip($v))->implode(', ').']'
                : $kunci.': '.self::kutip((string) $nilai);
        }

        $baris[] = '---';
        $baris[] = '';
        $baris[] = self::htmlKeMarkdown((string) $artikel->body);

        return implode("\n", $baris)."\n";
    }

    private static function kutip(string $nilai): string
    {
        return '"'.str_replace(['\\', '"', "\n"], ['\\\\', '\"', ' '], $nilai).'"';
    }

    /**
     * Konversi HTML artikel ke Markdown.
     *
     * Sengaja hanya menangani tag yang benar-benar dipakai editor. Sisanya
     * dibiarkan sebagai HTML — Markdown memang mengizinkan HTML di dalamnya,
     * dan menebak-nebak konversi justru merusak naskah.
     */
    private static function htmlKeMarkdown(string $html): string
    {
        $t = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html);

        $t = preg_replace('/<h([1-6])\b[^>]*>(.*?)<\/h\1>/is', "\n".'$1HASH$ $2'."\n", $t);
        $t = preg_replace_callback('/(\d)HASH\$ /', fn ($m) => str_repeat('#', (int) $m[1]).' ', $t);

        $t = preg_replace('/<(strong|b)\b[^>]*>(.*?)<\/\1>/is', '**$2**', $t);
        $t = preg_replace('/<(em|i)\b[^>]*>(.*?)<\/\1>/is', '*$2*', $t);
        $t = preg_replace('/<a\b[^>]*href=("|\')(.*?)\1[^>]*>(.*?)<\/a>/is', '[$3]($2)', $t);
        $t = preg_replace('/<img\b[^>]*alt=("|\')(.*?)\1[^>]*src=("|\')(.*?)\3[^>]*>/is', '![$2]($4)', $t);
        $t = preg_replace('/<img\b[^>]*src=("|\')(.*?)\1[^>]*>/is', '![]($2)', $t);

        $t = preg_replace('/<li\b[^>]*>(.*?)<\/li>/is', '- $1'."\n", $t);
        $t = preg_replace('/<\/?(ul|ol)\b[^>]*>/i', "\n", $t);
        $t = preg_replace('/<blockquote\b[^>]*>(.*?)<\/blockquote>/is', "\n".'> $1'."\n", $t);
        $t = preg_replace('/<pre\b[^>]*>(.*?)<\/pre>/is', "\n```\n".'$1'."\n```\n", $t);
        $t = preg_replace('/<br\s*\/?>/i', "\n", $t);
        $t = preg_replace('/<\/p>/i', "\n\n", $t);
        $t = preg_replace('/<p\b[^>]*>/i', '', $t);
        $t = preg_replace('/<hr\s*\/?>/i', "\n---\n", $t);

        $t = strip_tags($t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = preg_replace('/[ \t]+$/m', '', $t);
        $t = preg_replace('/\n{3,}/', "\n\n", $t);

        return trim((string) $t);
    }
}
