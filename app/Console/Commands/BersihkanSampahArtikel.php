<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Menghapus permanen artikel yang sudah lama duduk di tong sampah.
 *
 * Tong sampah adalah jaring pengaman untuk penghapusan keliru, bukan gudang
 * permanen. Pola dan tenggangnya sama dengan testimoni & ulasan (90 hari).
 */
class BersihkanSampahArtikel extends Command
{
    protected $signature = 'artikel:bersihkan-sampah {--hari=90} {--kering : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus permanen artikel di tong sampah yang lebih tua dari N hari';

    public function handle(): int
    {
        $hari = max(1, (int) $this->option('hari'));
        $batas = now()->subDays($hari);

        $daftar = BlogPost::onlyTrashed()->where('deleted_at', '<=', $batas)->get();

        if ($daftar->isEmpty()) {
            $this->info('Tidak ada artikel di tong sampah yang lebih tua dari '.$hari.' hari.');

            return self::SUCCESS;
        }

        if ($this->option('kering')) {
            foreach ($daftar as $artikel) {
                $this->line('- '.$artikel->title.' (dibuang '.$artikel->deleted_at->format('d/m/Y').')');
            }
            $this->info($daftar->count().' artikel AKAN dihapus permanen (mode kering).');

            return self::SUCCESS;
        }

        foreach ($daftar as $artikel) {
            if ($artikel->cover && Storage::disk('public')->exists('img/blog/'.$artikel->cover)) {
                Storage::disk('public')->delete('img/blog/'.$artikel->cover);
            }

            $artikel->forceDelete();
        }

        $this->info($daftar->count().' artikel dihapus permanen dari tong sampah.');

        return self::SUCCESS;
    }
}
