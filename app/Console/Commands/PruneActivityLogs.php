<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-logs:prune';

    protected $description = 'Bersihkan Log Aktivitas agar DB tidak membengkak: kunjungan lama dihapus cepat, error/auth disimpan lebih lama.';

    /** Kunjungan biasa: volume besar, cukup disimpan singkat. */
    private const HARI_KUNJUNGAN = 7;

    /** Error & auth: lebih penting untuk audit/maintenance, simpan lebih lama. */
    private const HARI_PENTING = 30;

    public function handle(): int
    {
        // Kunjungan (type 'visit') yang TIDAK lambat: buang setelah beberapa hari.
        $kunjungan = ActivityLog::where('type', 'visit')
            ->where('duration_ms', '<', 1000)
            ->where('created_at', '<', now()->subDays(self::HARI_KUNJUNGAN))
            ->delete();

        // Sisanya (error, auth, kunjungan lambat) disimpan lebih lama.
        $penting = ActivityLog::where('created_at', '<', now()->subDays(self::HARI_PENTING))
            ->delete();

        $this->info("Log dihapus — kunjungan lama: {$kunjungan}, lainnya (>".self::HARI_PENTING." hari): {$penting}.");

        return self::SUCCESS;
    }
}
