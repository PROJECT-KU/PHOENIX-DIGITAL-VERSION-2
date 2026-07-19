<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Penyaring HTML untuk konten kaya (isi blog dari editor Quill).
 *
 * Isi blog ditampilkan mentah dengan {!! !!} supaya format editornya utuh.
 * Tanpa penyaring, siapa pun yang bisa menulis blog dapat menyisipkan
 * <script> yang berjalan di browser SEMUA pengunjung publik.
 *
 * Dipakai saat MENAMPILKAN, bukan saat menyimpan, supaya tulisan lama yang
 * terlanjur tersimpan ikut aman tanpa perlu mengubah datanya.
 *
 * Memakai DOMDocument bawaan PHP — tanpa pustaka tambahan, agar tidak
 * menggeser dependensi lain.
 */
class HtmlSanitizer
{
    /** Tag yang boleh tampil — cukup untuk keluaran editor Quill. */
    private const TAG_AMAN = [
        'p', 'br', 'hr', 'span', 'div',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /** Atribut yang boleh bertahan, per tag ('*' = berlaku untuk semua). */
    private const ATRIBUT_AMAN = [
        '*' => ['class', 'style', 'title'],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /** Skema tautan yang diizinkan pada href/src. */
    private const SKEMA_AMAN = ['http', 'https', 'mailto', 'tel'];

    public static function bersihkan(?string $html): string
    {
        if (! $html || trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument;

        // Bungkus supaya fragmen HTML terbaca utuh & dianggap UTF-8.
        $sebelumnya = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"?><div id="ph-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $root = $doc->getElementById('ph-root');
        if (! $root) {
            return '';
        }

        self::saring($root);

        $keluaran = '';
        foreach ($root->childNodes as $anak) {
            $keluaran .= $doc->saveHTML($anak);
        }

        return $keluaran;
    }

    /** Buang elemen & atribut berbahaya secara rekursif. */
    private static function saring(\DOMNode $induk): void
    {
        $xpath = new DOMXPath($induk->ownerDocument);

        // Komentar bisa dipakai menyelundupkan markup pada parser tertentu.
        foreach (iterator_to_array($xpath->query('.//comment()', $induk)) as $komentar) {
            $komentar->parentNode?->removeChild($komentar);
        }

        foreach (iterator_to_array($xpath->query('.//*', $induk)) as $el) {
            if (! $el instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($el->nodeName);

            if (! in_array($tag, self::TAG_AMAN, true)) {
                // <script>/<style> dibuang beserta isinya; tag lain cukup
                // dilucuti sambil mempertahankan teks di dalamnya.
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form'], true)) {
                    $el->parentNode?->removeChild($el);
                } else {
                    self::lucuti($el);
                }

                continue;
            }

            self::bersihkanAtribut($el, $tag);
        }
    }

    /** Ganti elemen dengan isinya (tag hilang, teks tetap). */
    private static function lucuti(DOMElement $el): void
    {
        $induk = $el->parentNode;
        if (! $induk) {
            return;
        }

        while ($el->firstChild) {
            $induk->insertBefore($el->firstChild, $el);
        }

        $induk->removeChild($el);
    }

    private static function bersihkanAtribut(DOMElement $el, string $tag): void
    {
        $boleh = array_merge(
            self::ATRIBUT_AMAN['*'],
            self::ATRIBUT_AMAN[$tag] ?? []
        );

        foreach (iterator_to_array($el->attributes) as $attr) {
            $nama = strtolower($attr->nodeName);

            // on* menampung JavaScript (onclick, onerror, onload, ...).
            if (str_starts_with($nama, 'on') || ! in_array($nama, $boleh, true)) {
                $el->removeAttribute($attr->nodeName);

                continue;
            }

            if (in_array($nama, ['href', 'src'], true) && ! self::tautanAman($attr->nodeValue)) {
                $el->removeAttribute($attr->nodeName);
            }

            // expression()/url(javascript:) di style.
            if ($nama === 'style' && preg_match('/expression\s*\(|javascript\s*:|behaviou?r\s*:/i', (string) $attr->nodeValue)) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        // Tautan keluar dibuka aman (cegah tabnabbing).
        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function tautanAman(?string $nilai): bool
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return false;
        }

        // Relatif (/foo, foo.jpg, #bagian) selalu aman.
        if (! preg_match('/^([a-z][a-z0-9+.-]*):/i', $nilai, $m)) {
            return true;
        }

        $skema = strtolower($m[1]);

        // data:image dipakai Quill untuk gambar tempel — hanya gambar.
        if ($skema === 'data') {
            return (bool) preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $nilai);
        }

        return in_array($skema, self::SKEMA_AMAN, true);
    }
}
