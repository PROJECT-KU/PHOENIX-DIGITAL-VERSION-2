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
        $add = function (string $loc, string $priority = '0.7', string $freq = 'weekly') use (&$urls) {
            $urls[] = ['loc' => $loc, 'priority' => $priority, 'freq' => $freq];
        };

        // Halaman utama
        $add(route('homepage'), '1.0', 'daily');
        $add(route('shop.index'), '0.9', 'daily');
        $add(route('bundling.product-bundlings'), '0.8', 'weekly');
        $add(route('services'), '0.8', 'weekly');
        $add(route('about'), '0.5', 'monthly');
        $add(route('contact'), '0.5', 'monthly');
        $add(route('faq'), '0.5', 'monthly');
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
        BlogPost::published()->select('slug')->orderByDesc('published_at')->chunk(500, function ($chunk) use ($add) {
            foreach ($chunk as $post) {
                $add(route('blog.show', $post->slug), '0.6', 'weekly');
            }
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url>'
                .'<loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'
                .'<changefreq>'.$u['freq'].'</changefreq>'
                .'<priority>'.$u['priority'].'</priority>'
                .'</url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
