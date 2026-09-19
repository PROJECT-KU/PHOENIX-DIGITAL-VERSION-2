<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moderasi ulasan produk: izin terpisah + jejak moderasi + tautan pembeli.
 *
 * Sebelumnya hanya ada `view_productreview`, sehingga siapa pun yang boleh
 * MELIHAT layar moderasi juga bisa menghapus ulasan pembeli secara permanen.
 * Izin baru diberikan otomatis ke peran yang SUDAH punya izin lihat, supaya
 * tidak ada admin yang kehilangan akses setelah deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'ditinjau_at')) {
                $table->timestamp('ditinjau_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('product_reviews', 'ditinjau_oleh')) {
                $table->uuid('ditinjau_oleh')->nullable()->after('ditinjau_at');
            }
            // Nomor pengirim (opsional) dipakai mencocokkan pembeli sungguhan.
            if (! Schema::hasColumn('product_reviews', 'no_hp')) {
                $table->string('no_hp', 30)->nullable()->after('nama');
            }
            if (! Schema::hasColumn('product_reviews', 'customer_id')) {
                $table->uuid('customer_id')->nullable()->after('no_hp')->index();
            }
            if (! Schema::hasColumn('product_reviews', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $lihat = DB::table('permissions')->where('name', 'view_productreview')->first();
        if (! $lihat) {
            return;
        }

        foreach ([
            'edit_productreview' => 'Moderasi Ulasan Produk (setujui/sembunyikan)',
            'delete_productreview' => 'Hapus Ulasan Produk',
        ] as $nama => $label) {
            $id = DB::table('permissions')->where('name', $nama)->value('id');

            if (! $id) {
                $id = DB::table('permissions')->insertGetId([
                    'name' => $nama,
                    'display_name' => $label,
                    'group' => 'productreview',
                    'description' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Peran yang sudah boleh melihat tetap bisa memoderasi seperti kemarin.
            $peran = DB::table('role_permission')->where('permission_id', $lihat->id)->pluck('role_id');
            foreach ($peran as $roleId) {
                $ada = DB::table('role_permission')
                    ->where('role_id', $roleId)->where('permission_id', $id)->exists();

                if (! $ada) {
                    DB::table('role_permission')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            foreach (['ditinjau_at', 'ditinjau_oleh', 'no_hp', 'customer_id', 'deleted_at'] as $kolom) {
                if (Schema::hasColumn('product_reviews', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });

        $ids = DB::table('permissions')->whereIn('name', ['edit_productreview', 'delete_productreview'])->pluck('id');
        DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
