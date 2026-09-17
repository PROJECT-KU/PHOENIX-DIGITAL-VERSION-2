<?php

namespace App\Console\Commands;

use App\Actions\Finance\SyncRscBatchCashFlowAction;
use App\Models\PemesananRsc;
use Illuminate\Console\Command;

/**
 * Pulihkan pemasukan batch RSC lama yang hilang.
 *
 * Aturan sekarang: pemasukan dicatat sekali saat batch "baru" dan tidak
 * dilepas saat statusnya diganti. Kode lama justru MENGHAPUS pemasukan
 * begitu status diganti dari "baru", dan riwayat statusnya tidak disimpan —
 * jadi tidak bisa dipastikan batch lama mana yang dulunya "baru".
 *
 * Karena itu pemulihan dibatasi ke status yang dipilih (--status). Bawaannya
 * "habis": masa akun yang habis hampir pasti berawal dari batch baru.
 * Batch "baru" yang belum tercatat selalu ikut. Tidak ada yang dihapus.
 * Idempoten.
 */
class SinkronCashFlowRsc extends Command
{
    protected $signature = 'rsc:sinkron-cashflow
        {--status=habis : Status batch lama yang dianggap dulunya "baru" (pisahkan dengan koma)}
        {--kering : Hanya tampilkan, tidak menulis apa pun}';

    protected $description = 'Pulihkan pemasukan cash flow batch Pemesanan RSC lama';

    public function handle(SyncRscBatchCashFlowAction $sinkron): int
    {
        $pulihkan = array_filter(array_map('trim', explode(',', (string) $this->option('status'))));

        $batch = PemesananRsc::query()
            ->select('nama_camp', 'batch_camp', 'status')
            ->distinct()
            ->orderBy('nama_camp')
            ->orderBy('batch_camp')
            ->get()
            ->unique(fn ($b) => $b->nama_camp.'|'.$b->batch_camp);

        $dicatat = 0;
        $dilewati = [];
        foreach ($batch as $b) {
            $baris = PemesananRsc::where('nama_camp', $b->nama_camp)->where('batch_camp', $b->batch_camp);
            if ((clone $baris)->whereHas('cashFlow')->exists()) {
                // Sudah tercatat: tetap diselaraskan (jumlah & baris tunggal).
                if (! $this->option('kering')) {
                    $sinkron->execute($b->nama_camp, (string) $b->batch_camp);
                }

                continue;
            }

            if ($b->status !== 'baru' && ! in_array($b->status, $pulihkan, true)) {
                $dilewati[$b->status] = ($dilewati[$b->status] ?? 0) + 1;

                continue;
            }

            $dicatat++;
            $this->line("  dicatat: {$b->nama_camp} #{$b->batch_camp} ({$b->status})");

            if (! $this->option('kering')) {
                $sinkron->execute($b->nama_camp, (string) $b->batch_camp, paksaCatat: true);
            }
        }

        foreach ($dilewati as $status => $jumlah) {
            $this->line("  dilewati: {$jumlah} batch berstatus {$status} tanpa pemasukan (tambahkan ke --status bila dulunya baru)");
        }

        $this->info(($this->option('kering') ? '[kering] ' : '')
            ."{$batch->count()} batch diperiksa, {$dicatat} pemasukan dipulihkan.");

        return self::SUCCESS;
    }
}
