<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            // Pemilik testimoni — HANYA diisi bila nomor WA-nya cocok dgn pelanggan
            // yang punya pesanan 'completed'. NULL = testimoni tamu: tetap boleh
            // tampil, tapi tanpa label "sudah belanja" & tidak jadi member.
            //
            // Testimoni lama (12 buah, source=admin/customer) otomatis NULL,
            // jadi perilakunya tidak berubah sama sekali.
            $table->char('customer_id', 36)->nullable()->after('id');

            // Dipakai admin saat memoderasi (mencocokkan pemilik nomor).
            // TIDAK PERNAH ditampilkan ke publik.
            $table->string('no_hp')->nullable()->after('peran');

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonis', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
            $table->dropColumn(['customer_id', 'no_hp']);
        });
    }
};
