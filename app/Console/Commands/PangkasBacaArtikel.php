<?php

namespace App\Console\Commands;

use App\Models\BlogPostRead;
use Illuminate\Console\Command;

/**
 * Memangkas hitungan baca harian yang sudah terlalu lama.
 *
 * Bawaannya 400 hari — cukup untuk membandingkan satu bulan dengan bulan yang
 * sama tahun lalu, tanpa membuat tabelnya tumbuh selamanya. Total sepanjang
 * masa tetap aman karena disimpan terpisah di kolom blog_posts.views.
 */
class PangkasBacaArtikel extends Command
{
    protected $signature = 'artikel:pangkas-baca {--hari=400}';

    protected $description = 'Hapus baris hitungan baca harian artikel yang lebih tua dari N hari';

    public function handle(): int
    {
        $hari = max(60, (int) $this->option('hari'));
        $batas = now()->subDays($hari)->toDateString();

        $jumlah = BlogPostRead::where('tanggal', '<', $batas)->delete();

        $this->info($jumlah.' baris hitungan baca sebelum '.$batas.' dihapus.');

        return self::SUCCESS;
    }
}
