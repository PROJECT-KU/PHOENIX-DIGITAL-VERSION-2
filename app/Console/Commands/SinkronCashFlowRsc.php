<?php

namespace App\Console\Commands;

use App\Actions\Finance\SyncRscBatchCashFlowAction;
use App\Models\PemesananRsc;
use Illuminate\Console\Command;

/**
 * Selaraskan cash flow SEMUA batch RSC dengan aturan pencatatan terkini.
 *
 * Dijalankan sekali sesudah deploy perubahan "semua status RSC dicatat":
 * batch lama berstatus habis/pengganti/perpanjang belum punya baris cash
 * flow, karena dulu hanya status "baru" yang dicatat. Idempoten — aman
 * dijalankan berulang.
 */
class SinkronCashFlowRsc extends Command
{
    protected $signature = 'rsc:sinkron-cashflow {--kering : Hanya tampilkan batch yang akan diselaraskan}';

    protected $description = 'Selaraskan pemasukan & modal cash flow seluruh batch Pemesanan RSC';

    public function handle(SyncRscBatchCashFlowAction $sinkron): int
    {
        $batch = PemesananRsc::query()
            ->select('nama_camp', 'batch_camp')
            ->distinct()
            ->orderBy('nama_camp')
            ->orderBy('batch_camp')
            ->get();

        $belumTercatat = 0;
        foreach ($batch as $b) {
            $ada = PemesananRsc::where('nama_camp', $b->nama_camp)
                ->where('batch_camp', $b->batch_camp)
                ->whereHas('cashFlow')
                ->exists();
            if (! $ada) {
                $belumTercatat++;
                $this->line("  belum tercatat: {$b->nama_camp} #{$b->batch_camp}");
            }

            if (! $this->option('kering')) {
                $sinkron->execute($b->nama_camp, (string) $b->batch_camp);
            }
        }

        $this->info(($this->option('kering') ? '[kering] ' : '')
            ."{$batch->count()} batch diperiksa, {$belumTercatat} sebelumnya belum tercatat di cash flow.");

        return self::SUCCESS;
    }
}
