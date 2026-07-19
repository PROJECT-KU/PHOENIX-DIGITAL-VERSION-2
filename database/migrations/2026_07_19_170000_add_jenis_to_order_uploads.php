<?php

use App\Models\Order;
use App\Models\OrderUpload;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis pemeriksaan tiap unggahan: 'ai' | 'plagiasi' | 'parafrase' |
 * 'pengecekan' (fallback). Diperlukan agar satu pesanan bisa memuat beberapa
 * jenis pemeriksaan berbeda (mis. cek AI + add-on cek plagiasi), masing-masing
 * dengan dokumen & kuota sendiri.
 *
 * Backfill: unggahan lama diberi jenis dari pesanannya bila pesanan itu hanya
 * punya SATU jenis (semua data lama seperti ini). Yang ambigu dibiarkan null
 * dan diperlakukan sebagai fallback di kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('order_id');
        });

        Order::with('items.product', 'uploads')->has('uploads')->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $jenisPesanan = array_keys($order->kuotaPerJenis());

                // Hanya isi bila tak ambigu (pesanan satu jenis).
                if (count($jenisPesanan) !== 1) {
                    continue;
                }

                foreach ($order->uploads as $up) {
                    if ($up->jenis === null) {
                        $up->forceFill(['jenis' => $jenisPesanan[0]])->saveQuietly();
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_uploads', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
