<?php

namespace App\Http\Controllers;

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

        return response()->view('situs.lowongan-bagikan', [
            'lowongan' => $lowongan,
            'tujuan' => $tujuan,
            'judul' => "Lowongan {$lowongan->title} — {$lowongan->unit_label}",
            'deskripsi' => $deskripsi ?: "Lowongan {$lowongan->title} di {$lowongan->unit_label}.",
            'gambar' => $situs.'/og-image.png',
            'jsonLd' => $lowongan->jobPosting($tujuan),
        ]);
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
