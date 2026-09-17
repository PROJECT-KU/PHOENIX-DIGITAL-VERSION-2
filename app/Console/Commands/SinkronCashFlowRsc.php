<?php

namespace App\Console\Commands;

use App\Actions\Finance\SyncRscBatchCashFlowAction;
use App\Models\PemesananRsc;
use Illuminate\Console\Command;

/**
 * Selaraskan cash flow SEMUA batch RSC dengan aturan pencatatan terkini
 * (PemesananRsc::STATUS_DICATAT).
 *
 * Dijalankan sekali sesudah deploy perubahan aturan: batch lama berstatus
 * habis/perpanjang belum punya baris cash flow karena dulu hanya "baru" yang
 * dicatat, sedangkan batch pengganti tidak boleh tercatat. Satu batch selalu
 * berakhir dengan paling banyak satu baris pemasukan. Idempoten.
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

        $akanDicatat = 0;
        $akanDihapus = 0;
        foreach ($batch as $b) {
            $baris = PemesananRsc::where('nama_camp', $b->nama_camp)
                ->where('batch_camp', $b->batch_camp);
            $ada = (clone $baris)->whereHas('cashFlow')->exists();
            $layak = PemesananRsc::dicatatDiKas(
                (clone $baris)->orderBy('created_at')->orderBy('id')->value('status')
            );

            if ($layak && ! $ada) {
                $akanDicatat++;
                $this->line("  akan dicatat: {$b->nama_camp} #{$b->batch_camp}");
            } elseif (! $layak && $ada) {
                $akanDihapus++;
                $this->line("  akan dihapus (tidak dihitung): {$b->nama_camp} #{$b->batch_camp}");
            }

            if (! $this->option('kering')) {
                $sinkron->execute($b->nama_camp, (string) $b->batch_camp);
            }
        }

        $this->info(($this->option('kering') ? '[kering] ' : '')
            ."{$batch->count()} batch diperiksa, {$akanDicatat} dicatat, {$akanDihapus} dihapus dari cash flow.");

        return self::SUCCESS;
    }
}
