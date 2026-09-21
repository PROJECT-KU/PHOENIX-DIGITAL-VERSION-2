<?php

namespace App\Console\Commands;

use App\Models\BlogPostRevision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Memangkas riwayat versi artikel yang sudah terlalu tua.
 *
 * Batas 20 versi per artikel sudah menahan pertumbuhan menurut JUMLAH, tapi
 * tidak menurut UMUR: artikel yang sering disunting menyimpan dua puluh
 * salinan naskah selamanya. Yang terbaru selalu disisakan supaya jaring
 * pengamannya tidak pernah kosong.
 */
class PangkasRevisiArtikel extends Command
{
    protected $signature = 'artikel:pangkas-revisi {--hari=180} {--sisakan=5}';

    protected $description = 'Hapus revisi artikel yang lebih tua dari N hari, menyisakan beberapa yang terbaru';

    public function handle(): int
    {
        $hari = max(30, (int) $this->option('hari'));
        $sisakan = max(1, (int) $this->option('sisakan'));
        $batas = now()->subDays($hari);

        $dihapus = 0;

        BlogPostRevision::select('blog_post_id')
            ->groupBy('blog_post_id')
            ->pluck('blog_post_id')
            ->each(function ($idArtikel) use ($batas, $sisakan, &$dihapus) {
                $aman = BlogPostRevision::where('blog_post_id', $idArtikel)
                    ->orderByDesc('id')
                    ->take($sisakan)
                    ->pluck('id');

                $dihapus += BlogPostRevision::where('blog_post_id', $idArtikel)
                    ->whereNotIn('id', $aman)
                    ->where('created_at', '<=', $batas)
                    ->delete();
            });

        // Baris yatim: artikel yang terhapus permanen di basis data tanpa
        // kunci asing (mis. SQLite) meninggalkan revisinya.
        $yatim = BlogPostRevision::whereNotIn(
            'blog_post_id',
            DB::table('blog_posts')->select('id')
        )->delete();

        $this->info($dihapus.' revisi lama dihapus'.($yatim ? ', '.$yatim.' revisi yatim ikut dibersihkan' : '').'.');

        return self::SUCCESS;
    }
}
