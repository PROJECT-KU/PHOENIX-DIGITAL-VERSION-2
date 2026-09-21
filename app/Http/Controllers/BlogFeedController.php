<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

/**
 * Umpan RSS blog.
 *
 * Sitemap memberi tahu mesin pencari artikel apa saja yang ada; umpan ini
 * untuk orang yang mau berlangganan lewat pembaca RSS dan tahu begitu ada
 * tulisan baru tanpa harus membuka situsnya.
 */
class BlogFeedController extends Controller
{
    public function __invoke(): Response
    {
        $artikel = BlogPost::published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(20)
            ->get();

        $xml = view('feeds.blog', [
            'artikel' => $artikel,
            'diperbarui' => optional($artikel->first()?->published_at ?? now())->toRfc7231String(),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            // Umpan tidak perlu sesegar halaman: satu jam cukup, dan
            // meringankan hosting saat banyak pembaca menariknya.
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
