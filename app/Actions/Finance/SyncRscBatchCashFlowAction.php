<?php

namespace App\Actions\Finance;

use App\Models\PemesananRsc;

/**
 * Catat pemasukan (dan modal akun private) SATU batch RSC di cash flow.
 *
 * Satu entri untuk seluruh batch, bukan per peserta. Entri ditempel ke satu
 * baris representatif (yang tertua); cash flow baris lain dalam batch dihapus
 * supaya laporan tidak terpecah-pecah dan tidak pernah terhitung dobel.
 *
 * Dipakai form Pemesanan RSC dan perintah rsc:sinkron-cashflow. Idempoten.
 */
class SyncRscBatchCashFlowAction
{
    public function __construct(
        private SyncCashFlowAction $kas,
        private SyncRscPrivateCostAction $modal,
    ) {}

    public function execute(string $namaCamp, string $batchCamp): void
    {
        $rows = PemesananRsc::where('nama_camp', $namaCamp)
            ->where('batch_camp', $batchCamp)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $representatif = $rows->first();
        $totalBatch = (int) $rows->sum('total');

        // Sisakan hanya cash flow milik baris representatif — pemasukan DAN modal.
        foreach ($rows as $row) {
            if ($row->id !== $representatif->id) {
                $this->kas->delete($row);
                $this->modal->delete($row);
            }
        }

        // execute() self-guard lewat shouldRecord(): bila batch tidak layak
        // dicatat, cash flow representatif dihapus.
        $this->kas->execute($representatif, [
            'amount' => $totalBatch,
            'type' => 'income',
            'date' => $representatif->tanggal_pemesanan,
            'category' => 'PemesananRSC',
            'description' => 'Pesanan Rumah Scopus - '.$namaCamp.' Batch '.$batchCamp,
        ]);

        // Modal akun PRIVATE (bila akunnya private) — baris terpisah, idempoten.
        $this->modal->execute($representatif);
    }
}
