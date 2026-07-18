<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pesanan dari PUBLIC (punya guest_token) selalu QRIS dinamis, tapi alur
     * checkout lama tak pernah mencatat payment_method — sehingga di cash flow
     * "Metode Bayar" tampil "-". Isi mundur untuk pesanan public yang kosong.
     *
     * Aman & terbatas: hanya menyentuh baris ber-guest_token yang payment_method-
     * nya benar-benar kosong. Pesanan admin dan pesanan lunas-poin tidak tersentuh.
     */
    public function up(): void
    {
        DB::table('orders')
            ->whereNotNull('guest_token')
            ->where('guest_token', '!=', '')
            ->where(function ($q) {
                $q->whereNull('payment_method')->orWhere('payment_method', '');
            })
            ->update(['payment_method' => 'qris_dinamis']);
    }

    public function down(): void
    {
        // Tidak dikembalikan: data metode bayar yang benar sebaiknya tetap ada.
    }
};
