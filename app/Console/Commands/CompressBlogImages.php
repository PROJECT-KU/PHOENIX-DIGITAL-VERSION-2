<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\BlogImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CompressBlogImages extends Command
{
    protected $signature = 'blog:compress-covers {--dry-run : Hanya menampilkan apa yang akan diproses tanpa mengubah apa pun}';

    protected $description = 'Kompres ulang cover & gambar isi artikel blog lama yang masih besar menjadi WEBP ringan (tanpa bikin blur).';

    public function handle(BlogImageService $svc): int
    {
        if (! $svc->available()) {
            $this->error('Ekstensi GD dengan dukungan WEBP tidak tersedia di server ini.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $posts = BlogPost::all();

        if ($posts->isEmpty()) {
            $this->info('Belum ada artikel blog.');

            return self::SUCCESS;
        }

        $this->info(($dry ? '[DRY-RUN] ' : '').'Memproses '.$posts->count().' artikel...');
        $this->newLine();

        $coverDone = 0;
        $bodyDone = 0;

        foreach ($posts as $post) {
            $changed = false;
            $notes = [];

            // 1) Cover
            if ($post->cover && $svc->coverNeedsRecompress($post->cover)) {
                if ($dry) {
                    $notes[] = 'cover perlu dikompres';
                    $coverDone++;
                } else {
                    $old = $post->cover;
                    $before = $this->sizeKB('img/blog/'.$old);
                    $new = $svc->recompressStoredCover($post->cover);
                    if ($new && $new !== $old) {
                        $post->cover = $new;
                        $changed = true;
                        $coverDone++;
                        $after = $this->sizeKB('img/blog/'.$new);
                        $notes[] = "cover {$before}KB → {$after}KB";
                    }
                }
            }

            // 2) Gambar base64 di isi artikel
            if ($post->body && $svc->bodyHasBase64($post->body)) {
                if ($dry) {
                    $notes[] = 'ada gambar base64 di isi';
                    $bodyDone++;
                } else {
                    $newBody = $svc->processBodyImages($post->body);
                    if ($newBody !== $post->body) {
                        $post->body = $newBody;
                        $changed = true;
                        $bodyDone++;
                        $notes[] = 'gambar isi artikel dikompres';
                    }
                }
            }

            if ($changed) {
                // Simpan tanpa menyentuh updated_at agar urutan artikel tak berubah.
                $post->timestamps = false;
                $post->save();
            }

            if ($notes) {
                $this->line('  • #'.$post->id.' '.\Illuminate\Support\Str::limit($post->title, 40).' — '.implode(', ', $notes));
            }
        }

        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] Perlu diproses: ' : 'Selesai: ')."cover={$coverDone}, isi artikel={$bodyDone}.");

        if ($dry) {
            $this->comment('Jalankan tanpa --dry-run untuk benar-benar mengompres.');
        }

        return self::SUCCESS;
    }

    private function sizeKB(string $relPath): int
    {
        $disk = Storage::disk('public');

        return $disk->exists($relPath) ? (int) round($disk->size($relPath) / 1024) : 0;
    }
}
