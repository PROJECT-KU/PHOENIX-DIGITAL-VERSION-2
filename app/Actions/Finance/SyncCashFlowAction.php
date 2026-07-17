<?php

namespace App\Actions\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SyncCashFlowAction
{
    public function execute(Model $model, array $data): void
    {
        DB::transaction(function () use ($model, $data) {
            if (! $this->shouldRecord($model)) {
                $model->cashFlow()->delete();

                return;
            }

            // update jika ada, create jika belum ada
            $model->cashFlow()->updateOrCreate([
                'sourceable_id' => $model->id,
                'sourceable_type' => get_class($model),
            ], [
                'amount' => $data['amount'],
                'type' => $data['type'], // 'income' atau 'expense'
                'transaction_date' => $data['date'],
                'category' => $data['category'],
                'description' => $data['description'],
            ]);
        });
    }

    /**
     * Delete cash flow record
     */
    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $model->cashFlow()->delete();
        });
    }

    /**
     * Bulk delete cash flow records (optional - untuk efisiensi)
     */
    public function bulkDelete(iterable $models): void
    {
        DB::transaction(function () use ($models) {
            foreach ($models as $model) {
                $model->cashFlow()->delete();
            }
        });
    }

    // logika validasi kapan uang di anggap sah masuk/keluar
    private function shouldRecord(Model $model): bool
    {
        if ($model instanceof \App\Models\Order) {
            return in_array($model->status, ['paid', 'completed']);
        }
        if ($model instanceof \App\Models\GajiKaryawans) {
            return $model->status === 'completed';
        }
        if ($model instanceof \App\Models\Loan) {
            // Peminjaman = uang keluar; selalu dicatat di cash flow saat data dibuat.
            // (Status kini dihitung otomatis dan tidak lagi menjadi syarat pencatatan.)
            return true;
        }
        if ($model instanceof \App\Models\Pengembalian) {
            // Pengembalian = uang masuk; selalu dicatat di cash flow.
            return true;
        }
        if ($model instanceof \App\Models\Spending) {
            // Pembelian akun PRIVATE = katalog harga satuan (referensi), bukan kas nyata.
            // Biaya nyatanya dicatat per-order (SyncOrderPrivateCostAction).
            if ($model->jenis_pengeluaran === 'pembelian_akun'
                && optional($model->product)->tipe_akun === 'private') {
                return false;
            }

            return $model->status !== 'pending';
        }
        if ($model instanceof \App\Models\Modal) {
            // Setoran modal operasional = uang masuk; selalu dicatat.
            return true;
        }
        if ($model instanceof \App\Models\Pemasukan) {
            // Pemasukan lain = uang masuk; selalu dicatat.
            return true;
        }
        if ($model instanceof \App\Models\PemesananRsc) {
            return $model->status === 'baru';
        }

        return false;
    }
}
