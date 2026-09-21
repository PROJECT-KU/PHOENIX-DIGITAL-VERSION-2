<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];
        // $lastmod opsional: hanya diisi untuk halaman yang punya waktu
        // perubahan sungguhan. Menebak tanggal untuk halaman statis justru
        // membuat sinyalnya tidak bisa dipercaya.
        $add = function (string $loc, string $priority = '0.7', string $freq = 'weekly', ?string $lastmod = null) use (&$urls) {
            $urls[] = ['loc' => $loc, 'priority' => $priority, 'freq' => $freq, 'lastmod' => $lastmod];
        };

        // Halaman utama
        $add(route('homepage'), '1.0', 'daily');
        $add(route('shop.index'), '0.9', 'daily');
        $add(route('bundling.product-bundlings'), '0.8', 'weekly');
        $add(route('services'), '0.8', 'weekly');
        $add(route('about'), '0.5', 'monthly');
        $add(route('contact'), '0.5', 'monthly');
        $add(route('faq'), '0.5', 'monthly');
        $add(route('testimoni.semua'), '0.6', 'weekly');
        $add(route('terms'), '0.3', 'yearly');
        $add(route('privacy'), '0.3', 'yearly');
        $add(route('blog.index'), '0.8', 'daily');

        // Detail produk
        Product::query()->select('id')->orderByDesc('id')->chunk(500, function ($chunk) use ($add) {
            foreach ($chunk as $p) {
                $add(route('shop.detail-product', $p->id), '0.7', 'weekly');
            }
        });

        // Artikel blog yang sudah terbit
        BlogPost::published()->select('slug', 'updated_at')->orderByDesc('published_at')->chunk(500, function ($chunk) use ($add) {
            foreach ($chunk as $post) {
                $add(route('blog.show', $post->slug), '0.6', 'weekly', optional($post->updated_at)->toAtomString());
            }
        });

        // Halaman kategori & tag blog — keduanya halaman nyata yang bisa
        // dibuka, jadi pantas diketahui mesin pencari.
        // Slug dipakai di alamatnya, bukan nama: "?kategori=tips-panduan"
        // lebih enak dibaca dan dibagikan daripada nama ber-spasi & ber-&.
        $slugKategori = \App\Models\BlogCategory::pluck('slug', 'name');

        BlogPost::published()
            ->whereNotNull('category')->where('category', '!=', '')
            ->selectRaw('category, MAX(updated_at) as diubah')
            ->groupBy('category')
            ->get()
            ->each(fn ($baris) => $add(
                route('blog.index', ['kategori' => $slugKategori[$baris->category] ?? $baris->category]),
                '0.5',
                'weekly',
                $baris->diubah ? \Illuminate\Support\Carbon::parse($baris->diubah)->toAtomString() : null
            ));

        BlogPost::published()->whereNotNull('tags')->get(['tags', 'updated_at'])
            ->flatMap(fn ($p) => collect($p->tagDaftar())->map(fn ($t) => ['tag' => $t, 'diubah' => $p->updated_at]))
            ->groupBy('tag')
            ->each(function ($baris, $tag) use ($add) {
                $add(
                    route('blog.index', ['tag' => $tag]),
                    '0.4',
                    'weekly',
                    optional($baris->max('diubah'))->toAtomString()
                );
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url>'
                .'<loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'
                .($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '')
                .'<changefreq>'.$u['freq'].'</changefreq>'
                .'<priority>'.$u['priority'].'</priority>'
                .'</url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
