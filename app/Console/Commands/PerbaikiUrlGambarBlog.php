<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Perbaiki URL gambar berawalan "//storage/" di dalam isi artikel blog.
 *
 * Saat APP_URL berakhiran "/", URL gambar yang tersimpan di body artikel
 * menjadi "//storage/img/..." — browser membaca dua garis miring di awal
 * sebagai nama host ("https://storage/..."), sehingga gambar tidak pernah
 * tampil meski berkasnya ada di server.
 *
 * Sumber masalahnya sudah ditutup (config/filesystems.php + BlogImageService),
 * tapi artikel yang terlanjur tersimpan perlu diperbaiki sekali lewat perintah
 * ini. Bawaannya simulasi — tidak menulis apa pun tanpa --terapkan.
 */
class PerbaikiUrlGambarBlog extends Command
{
    protected $signature = 'blog:perbaiki-url-gambar
                            {--terapkan : Benar-benar simpan perubahan (tanpa ini hanya simulasi)}';

    protected $description = 'Perbaiki URL gambar "//storage/" jadi "/storage/" di isi artikel blog';

    public function handle(): int
    {
        $terapkan = (bool) $this->option('terapkan');

        $posts = BlogPost::query()
            ->where('body', 'like', '%//storage/%')
            ->get(['id', 'title', 'body']);

        if ($posts->isEmpty()) {
            $this->info('Tidak ada artikel yang perlu diperbaiki.');

            return self::SUCCESS;
        }

        $this->line($terapkan ? 'MODE: menerapkan perubahan' : 'MODE: simulasi (tidak menyimpan apa pun)');
        $this->newLine();

        $totalGambar = 0;

        foreach ($posts as $post) {
            $baru = str_replace('"//storage/', '"/storage/', $post->body);
            $baru = str_replace("'//storage/", "'/storage/", $baru);

            $jumlah = substr_count($post->body, '//storage/') - substr_count($baru, '//storage/');
            $totalGambar += $jumlah;

            $this->line(sprintf('  %s  %-55s %d gambar',
                $terapkan ? 'diperbaiki' : 'akan diperbaiki',
                mb_strimwidth($post->title, 0, 55, '…'),
                $jumlah
            ));

            if ($terapkan) {
                DB::table('blog_posts')->where('id', $post->id)->update(['body' => $baru]);
            }
        }

        $this->newLine();
        $this->info(sprintf('%d artikel, %d gambar%s.',
            $posts->count(),
            $totalGambar,
            $terapkan ? ' diperbaiki' : ' akan diperbaiki — jalankan ulang dengan --terapkan'
        ));

        return self::SUCCESS;
    }
}
