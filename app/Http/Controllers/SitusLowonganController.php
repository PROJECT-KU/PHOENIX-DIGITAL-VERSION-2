<?php

namespace App\Http\Controllers;

use App\Models\Asthana\Berita;
use App\Models\Asthana\Portofolio;
use App\Models\Lowongan;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Situs Asthana statis dan halaman detail lowongannya dirakit di peramban
 * (detail.html?slug=…), sehingga WhatsApp/LinkedIn/Google hanya melihat
 * judul "Karir" yang sama untuk semua posisi. Dua hal di sini menutupnya:
 *
 * - /lowongan/{slug}: tautan yang DIBAGIKAN. Berisi meta OG & JobPosting per
 *   posisi untuk perayap, lalu langsung mengalihkan manusia ke situs.
 * - /api/situs/sitemap-lowongan.xml: daftar alamat detail lowongan untuk
 *   mesin pencari (dirujuk dari robots.txt situs).
 */
class SitusLowonganController extends Controller
{
    public function bagikan(string $slug)
    {
        $situs = config('asthana.situs_url');
        $lowongan = Lowongan::where('slug', $slug)->terbuka()->first();

        // Tanpa alamat situs tidak ada tujuan pengalihan; lowongan yang sudah
        // ditutup diarahkan ke daftar lowongan, bukan halaman galat.
        if (! $situs) {
            abort(404);
        }
        if (! $lowongan) {
            return redirect()->away($situs.'/karir/');
        }

        $tujuan = $situs.'/karir/detail.html?slug='.rawurlencode($lowongan->slug);
        $deskripsi = $lowongan->ringkasan
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $lowongan->descriptions))), 180);

        return response()->view('situs.bagikan', [
            'nama' => $lowongan->title,
            'tujuan' => $tujuan,
            'judul' => "Lowongan {$lowongan->title} — {$lowongan->unit_label}",
            'deskripsi' => $deskripsi ?: "Lowongan {$lowongan->title} di {$lowongan->unit_label}.",
            'gambar' => $situs.'/og-image.png',
            'jsonLd' => $lowongan->jobPosting($tujuan),
        ]);
    }

    /** /situs/portofolio/{slug} — tautan bagikan proyek portofolio. */
    public function portofolio(string $slug)
    {
        $situs = config('asthana.situs_url') ?: abort(404);
        $p = Portofolio::terbit()->where('slug', $slug)->first();
        if (! $p) {
            return redirect()->away($situs.'/portofolio/');
        }

        return $this->halamanBagikan(
            $p->judul,
            "{$p->judul} — Portofolio PT. Asthana Cipta Mandiri",
            $p->ringkasan ?: Str::limit(trim(strip_tags((string) $p->isi)), 180),
            Portofolio::urlGambar($p->sampul) ?: $situs.'/og-image.png',
            $situs.'/portofolio/detail.html?slug='.rawurlencode($p->slug),
        );
    }

    /** /situs/berita/{slug} — tautan bagikan berita. */
    public function berita(string $slug)
    {
        $situs = config('asthana.situs_url') ?: abort(404);
        $b = Berita::terbit()->where('slug', $slug)->first();
        if (! $b) {
            return redirect()->away($situs.'/berita/');
        }

        $tujuan = $situs.'/berita/baca.html?slug='.rawurlencode($b->slug);

        return $this->halamanBagikan(
            $b->judul,
            "{$b->judul} — Berita PT. Asthana Cipta Mandiri",
            $b->ringkasan ?: Str::limit(trim(strip_tags((string) $b->isi)), 180),
            Berita::urlGambar($b->sampul) ?: $situs.'/og-image.png',
            $tujuan,
            [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $b->judul,
                'datePublished' => $b->terbit_at?->toIso8601String(),
                'image' => Berita::urlGambar($b->sampul) ?: $situs.'/og-image.png',
                'mainEntityOfPage' => $tujuan,
                'publisher' => ['@type' => 'Organization', 'name' => 'PT. Asthana Cipta Mandiri'],
            ],
        );
    }

    private function halamanBagikan(string $nama, string $judul, string $deskripsi, string $gambar, string $tujuan, ?array $jsonLd = null)
    {
        return response()->view('situs.bagikan', compact('nama', 'judul', 'deskripsi', 'gambar', 'tujuan', 'jsonLd'));
    }

    public function sitemap(): Response
    {
        $situs = config('asthana.situs_url');
        $daftar = $situs ? Lowongan::terbuka()->orderBy('title')->get(['slug', 'updated_at']) : collect();

        $isi = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($daftar as $l) {
            foreach (['/karir/detail.html', '/en/careers/detail.html'] as $jalur) {
                $isi .= '  <url><loc>'.e($situs.$jalur.'?slug='.rawurlencode($l->slug)).'</loc>'
                    .'<lastmod>'.$l->updated_at?->toDateString().'</lastmod></url>'."\n";
            }
        }
        $isi .= '</urlset>'."\n";

        return response($isi, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
