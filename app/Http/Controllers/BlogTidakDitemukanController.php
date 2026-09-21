<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Halaman "artikel tidak ditemukan" yang menawarkan jalan keluar.
 *
 * Alamat artikel sering salah ketik atau terpotong saat disalin ke pesan.
 * Halaman 404 polos membuat orang menutup tab; menawarkan judul yang mirip
 * membuatnya sampai ke tulisan yang dicari.
 */
class BlogTidakDitemukanController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $slug = (string) $request->segment(2);

        return response()->view('errors.blog-404', [
            'slug' => $slug,
            'saran' => $this->saran($slug),
            'terbaru' => BlogPost::published()->latest('published_at')->take(3)->get(),
        ], 404);
    }

    /**
     * Artikel yang judulnya memuat salah satu kata dari alamat yang diminta.
     */
    private function saran(string $slug)
    {
        $kata = collect(preg_split('/[-_\/]+/', $slug, -1, PREG_SPLIT_NO_EMPTY))
            // Kata sangat pendek ("di", "ke", "5") mencocokkan apa saja dan
            // membuat saran jadi acak.
            ->filter(fn ($k) => mb_strlen($k) >= 4)
            ->take(6);

        if ($kata->isEmpty()) {
            return collect();
        }

        return BlogPost::published()
            ->where(function ($q) use ($kata) {
                foreach ($kata as $k) {
                    $q->orWhere('title', 'like', '%'.$k.'%');
                }
            })
            ->orderByDesc('published_at')
            ->take(5)
            ->get();
    }
}
