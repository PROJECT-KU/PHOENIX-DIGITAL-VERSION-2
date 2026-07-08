<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_modal_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->unsignedSmallInteger('durasi_value');
            $table->enum('durasi_type', ['bulan', 'tahun']);
            $table->decimal('harga', 15, 0)->default(0);
            $table->date('berlaku_mulai'); // harga berlaku sejak tanggal ini
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'durasi_value', 'durasi_type', 'berlaku_mulai'], 'pmp_lookup_idx');
        });

        // ===== Migrasi katalog harga private lama (dari pengeluaran pembelian_akun) =====
        $now = now();
        $privateIds = DB::table('products')->where('tipe_akun', 'private')->pluck('id');

        if ($privateIds->isNotEmpty()) {
            $katalog = DB::table('spendings')
                ->where('jenis_pengeluaran', 'pembelian_akun')
                ->whereIn('product_id', $privateIds)
                ->whereNotNull('durasi_value')
                ->get();

            foreach ($katalog as $s) {
                DB::table('product_modal_prices')->insert([
                    'id' => (string) Str::uuid(),
                    'product_id' => $s->product_id,
                    'durasi_value' => $s->durasi_value,
                    'durasi_type' => $s->durasi_type ?: 'bulan',
                    'harga' => (int) $s->nominal,
                    'berlaku_mulai' => $s->tanggal_transaksi,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Hapus katalog private dari pengeluaran (kini dikelola terpisah; bukan kas nyata).
            $ids = $katalog->pluck('id');
            if ($ids->isNotEmpty()) {
                DB::table('cash_flows')
                    ->where('sourceable_type', 'App\\Models\\Spending')
                    ->whereIn('sourceable_id', $ids)->delete();
                DB::table('spendings')->whereIn('id', $ids)->delete();
            }
        }

        // ===== Permissions =====
        $perms = [
            ['name' => 'view_harga_modal', 'display_name' => 'Lihat Harga Modal Produk', 'group' => 'harga_modal', 'description' => 'Dapat melihat harga modal akun per produk'],
            ['name' => 'manage_harga_modal', 'display_name' => 'Kelola Harga Modal Produk', 'group' => 'harga_modal', 'description' => 'Dapat menambah/mengubah/menghapus harga modal akun'],
        ];
        foreach ($perms as $p) {
            DB::table('permissions')->updateOrInsert(['name' => $p['name']], array_merge($p, ['updated_at' => $now, 'created_at' => $now]));
        }
        $vsId = DB::table('permissions')->where('name', 'view_spending')->value('id');
        $roleIds = $vsId ? DB::table('role_permission')->where('permission_id', $vsId)->pluck('role_id') : collect();
        $permIds = DB::table('permissions')->whereIn('name', ['view_harga_modal', 'manage_harga_modal'])->pluck('id', 'name');
        foreach ($roleIds as $rid) {
            foreach ($permIds as $pid) {
                DB::table('role_permission')->updateOrInsert(['role_id' => $rid, 'permission_id' => $pid], ['created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->whereIn('permission_id', function ($q) {
            $q->select('id')->from('permissions')->whereIn('name', ['view_harga_modal', 'manage_harga_modal']);
        })->delete();
        DB::table('permissions')->whereIn('name', ['view_harga_modal', 'manage_harga_modal'])->delete();

        Schema::dropIfExists('product_modal_prices');
    }
};
