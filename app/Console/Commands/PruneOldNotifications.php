<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneOldNotifications extends Command
{
    protected $signature = 'notifications:prune';

    protected $description = 'Hapus notifikasi sebelum awal bulan berjalan agar DB tidak menumpuk.';

    public function handle(): int
    {
        $deleted = DB::table('notifications')
            ->where('created_at', '<', now()->startOfMonth())
            ->delete();

        $this->info("Notifikasi bulan lama dihapus: {$deleted} baris.");

        return self::SUCCESS;
    }
}
