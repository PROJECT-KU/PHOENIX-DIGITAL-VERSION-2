<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fondasi jasa lanjutan (Cek Plagiasi AI & Parafrase).
     * SEMUA aditif & bernilai default aman — produk non-jasa tidak terpengaruh.
     */
    public function up(): void
    {
        // 1) Mode jasa & mode add-on per produk (dinamis, diatur admin).
        Schema::table('products', function (Blueprint $table) {
            // 'paket'   : dijual per paket pengecekan (1x/5x/10x) — spt cek plagiasi.
            // 'halaman' : dijual per halaman dokumen (parafrase), file diunggah dulu.
            $table->string('jasa_mode')->default('paket')->after('butuh_file');
            // 'multi'   : add-on bisa dipilih beberapa (checkbox).
            // 'tunggal' : add-on saling menggantikan, pilih salah satu (radio).
            $table->string('addon_mode')->default('multi')->after('jasa_mode');
        });

        // 2) Katalog add-on per produk (mis. "+ Cek Turnitin", "Target < 20%").
        Schema::create('product_addons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('harga')->default(0); // tambahan harga
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('product_id');
        });

        // 3) Add-on terpilih + jumlah halaman disimpan di item pesanan (untuk
        //    ditampilkan ke admin & jejak audit harga).
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('addons')->nullable()->after('duration_value');       // [{nama,harga}]
            $table->unsignedBigInteger('addons_total')->default(0)->after('addons');
            $table->unsignedInteger('jumlah_halaman')->nullable()->after('addons_total');
        });

        // 4) Harga per halaman disimpan di katalog harga: durasi_type 'halaman'.
        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali','halaman') NOT NULL");
        DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun','sekali','kali','halaman') NOT NULL DEFAULT 'bulan'");
        DB::statement("ALTER TABLE product_modal_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali','halaman') NOT NULL");

        // 5) File yang diunggah customer SEBELUM bayar (parafrase) — dipakai untuk
        //    menghitung halaman & harga, lalu dipindahkan ke pesanan saat checkout.
        Schema::create('jasa_draft_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('nama_asli');
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedInteger('jumlah_halaman')->default(0);
            $table->string('session_token')->nullable(); // pemilik draft (guest)
            $table->timestamps();

            $table->index('session_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jasa_draft_uploads');
        Schema::dropIfExists('product_addons');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['addons', 'addons_total', 'jumlah_halaman']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['jasa_mode', 'addon_mode']);
        });

        DB::statement("ALTER TABLE product_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali') NOT NULL");
        DB::statement("ALTER TABLE order_items MODIFY duration_type ENUM('bulan','tahun','sekali','kali') NOT NULL DEFAULT 'bulan'");
        DB::statement("ALTER TABLE product_modal_prices MODIFY durasi_type ENUM('bulan','tahun','sekali','kali') NOT NULL");
    }
};
