<?php

use App\Actions\Finance\SyncOrderPrivateCostAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductModalPrice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Perbaikan data modal JASA:
     *  1) Modal produk jasa yang terlanjur tersimpan bulan/tahun (satuan tak
     *     mungkin cocok dgn item jasa yg selalu 'kali') dijadikan '1 kali'
     *     (per pengecekan) — satu-satunya bentuk modal jasa yang bermakna.
     *  2) Re-sync pesanan jasa yang sudah dibayar agar baris expense modalnya
     *     tercatat di cash flow (dulu 0 karena satuan tak cocok).
     */
    public function up(): void
    {
        $jasaIds = Product::where('butuh_file', true)->pluck('id')->all();
        if (empty($jasaIds)) {
            return;
        }

        // 1) Salvage: setiap baris modal produk jasa -> durasi 1 'kali'.
        foreach (ProductModalPrice::whereIn('product_id', $jasaIds)->where('durasi_type', '!=', 'kali')->get() as $row) {
            $row->update(['durasi_value' => 1, 'durasi_type' => 'kali']);
        }

        // 2) Re-sync expense modal untuk pesanan jasa yang sudah dibayar.
        $sync = app(SyncOrderPrivateCostAction::class);
        Order::whereIn('status', ['paid', 'processing', 'completed'])
            ->whereHas('items', fn ($q) => $q->whereIn('product_id', $jasaIds))
            ->with('items.product')
            ->chunkById(100, function ($orders) use ($sync) {
                foreach ($orders as $order) {
                    $sync->execute($order);
                }
            });
    }

    public function down(): void
    {
        // Tidak dikembalikan: modal jasa memang harus 'kali', dan baris expense
        // yang benar sebaiknya tetap ada.
    }
};
