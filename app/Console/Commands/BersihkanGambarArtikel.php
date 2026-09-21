<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Menghapus gambar blog yang tidak lagi dipakai artikel mana pun.
 *
 * Gambar yang disisipkan ke dalam tulisan diunggah lebih dulu sebagai berkas.
 * Kalau admin lalu menghapus gambarnya dari naskah, berkasnya tetap tinggal —
 * dan menumpuk diam-diam sampai kuota penyimpanan habis.
 */
class BersihkanGambarArtikel extends Command
{
    protected $signature = 'artikel:bersihkan-gambar {--hari=7} {--kering : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus berkas gambar blog yang tidak dirujuk artikel mana pun';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('img/blog')) {
            $this->info('Folder gambar blog belum ada.');

            return self::SUCCESS;
        }

        // Tenggang: berkas yang baru diunggah belum tentu sudah tersimpan di
        // naskah (artikel masih dibuka, belum ditekan simpan). Menghapusnya
        // seketika berarti gambar hilang di tengah orang menulis.
        $batas = now()->subDays(max(1, (int) $this->option('hari')))->getTimestamp();

        $dipakai = $this->berkasTerpakai();
        $yatim = [];
        $lega = 0;

        foreach ($disk->files('img/blog') as $jalur) {
            $nama = basename($jalur);

            if (isset($dipakai[$nama]) || $disk->lastModified($jalur) > $batas) {
                continue;
            }

            $yatim[] = $nama;
            $lega += $disk->size($jalur);
        }

        if (! $yatim) {
            $this->info('Tidak ada gambar yatim.');

            return self::SUCCESS;
        }

        $ukuran = number_format($lega / 1048576, 2, ',', '.').' MB';

        if ($this->option('kering')) {
            foreach ($yatim as $nama) {
                $this->line('- '.$nama);
            }
            $this->info(count($yatim).' gambar AKAN dihapus ('.$ukuran.', mode kering).');

            return self::SUCCESS;
        }

        foreach ($yatim as $nama) {
            $disk->delete('img/blog/'.$nama);
        }

        $this->info(count($yatim).' gambar yatim dihapus, '.$ukuran.' dibebaskan.');

        return self::SUCCESS;
    }

    /**
     * Nama berkas yang masih dirujuk — sampul maupun gambar di dalam naskah.
     *
     * Artikel di tong sampah dan seluruh revisinya ikut dihitung: keduanya
     * masih bisa dikembalikan, dan gambar yang hilang membuat pemulihannya
     * setengah jadi.
     *
     * @return array<string, true>
     */
    private function berkasTerpakai(): array
    {
        $dipakai = [];

        $catat = function (?string $teks) use (&$dipakai) {
            if (! $teks) {
                return;
            }

            preg_match_all('#img/blog/([A-Za-z0-9._-]+)#', $teks, $m);
            foreach ($m[1] ?? [] as $nama) {
                $dipakai[$nama] = true;
            }
        };

        BlogPost::withTrashed()->select('cover', 'body')->chunk(200, function ($chunk) use (&$dipakai, $catat) {
            foreach ($chunk as $artikel) {
                if ($artikel->cover) {
                    $dipakai[$artikel->cover] = true;
                }

                $catat($artikel->body);
            }
        });

        \App\Models\BlogPostRevision::select('body')->chunk(200, function ($chunk) use ($catat) {
            foreach ($chunk as $revisi) {
                $catat($revisi->body);
            }
        });

        return $dipakai;
    }
}
