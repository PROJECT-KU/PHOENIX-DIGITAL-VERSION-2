<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

/**
 * Membuang draf yang lahir dari SIMPAN OTOMATIS lalu tidak pernah disentuh.
 *
 * Yang dibuang hanya yang belum pernah ditekan simpan sekali pun
 * (disimpan_manual = false) — draf yang sengaja dibuat orang tidak pernah
 * ikut. Dibuang pun cuma ke TONG SAMPAH, jadi masih bisa dikembalikan.
 */
class BersihkanDrafArtikel extends Command
{
    protected $signature = 'artikel:bersihkan-draf {--hari=60} {--kering : Tampilkan saja, jangan buang}';

    protected $description = 'Pindahkan draf hasil simpan otomatis yang terbengkalai ke tong sampah';

    public function handle(): int
    {
        $hari = max(7, (int) $this->option('hari'));
        $batas = now()->subDays($hari);

        $daftar = BlogPost::where('status', 'draft')
            ->where('disimpan_manual', false)
            ->where('updated_at', '<=', $batas)
            ->get();

        if ($daftar->isEmpty()) {
            $this->info('Tidak ada draf terbengkalai yang lebih tua dari '.$hari.' hari.');

            return self::SUCCESS;
        }

        if ($this->option('kering')) {
            foreach ($daftar as $artikel) {
                $this->line('- '.$artikel->title.' (terakhir diubah '.$artikel->updated_at->format('d/m/Y').')');
            }
            $this->info($daftar->count().' draf AKAN dipindahkan ke tong sampah (mode kering).');

            return self::SUCCESS;
        }

        foreach ($daftar as $artikel) {
            $artikel->delete();
        }

        $this->info($daftar->count().' draf terbengkalai dipindahkan ke tong sampah.');

        return self::SUCCESS;
    }
}
