<?php

namespace App\Console\Commands;

use App\Models\TaskComment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOldComments extends Command
{
    protected $signature = 'comments:prune';

    protected $description = 'Hapus komentar task (chat + file/gambar) dari tahun-tahun sebelumnya agar DB & storage tidak menumpuk.';

    public function handle(): int
    {
        // Batas: awal tahun berjalan. Yang dibuat SEBELUM ini (tahun lalu ke bawah) dihapus.
        $batas = now()->startOfYear();

        // 1) Hapus file/gambar lampiran komentar dari storage lebih dulu.
        $files = 0;
        TaskComment::where('created_at', '<', $batas)
            ->whereNotNull('file_path')
            ->select(['id', 'file_path'])
            ->cursor()
            ->each(function (TaskComment $c) use (&$files) {
                if ($c->file_path && Storage::disk('public')->exists($c->file_path)) {
                    Storage::disk('public')->delete($c->file_path);
                    $files++;
                }
            });

        // 2) Hapus baris komentarnya (chat) dari DB.
        $rows = TaskComment::where('created_at', '<', $batas)->delete();

        $this->info("Komentar tahun lalu dihapus: {$rows} chat, {$files} file/gambar.");

        return self::SUCCESS;
    }
}
