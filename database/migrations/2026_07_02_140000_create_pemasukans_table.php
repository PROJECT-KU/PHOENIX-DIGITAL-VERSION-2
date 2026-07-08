<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemasukans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('id_transaksi');
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('kategori')->nullable(); // sumber: Jasa Web, Pariwisata, dll
            $table->text('deskripsi')->nullable();
            $table->foreignId('penginput_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('kategori');
        });

        // ===== Permissions pemasukan =====
        $now = now();
        $perms = [
            ['name' => 'view_pemasukan', 'display_name' => 'Lihat Pemasukan Lainnya', 'group' => 'pemasukan', 'description' => 'Dapat melihat pemasukan lain di luar pemesanan'],
            ['name' => 'create_pemasukan', 'display_name' => 'Tambah Pemasukan Lainnya', 'group' => 'pemasukan', 'description' => 'Dapat menambah pemasukan lain'],
            ['name' => 'edit_pemasukan', 'display_name' => 'Edit Pemasukan Lainnya', 'group' => 'pemasukan', 'description' => 'Dapat mengubah pemasukan lain'],
            ['name' => 'delete_pemasukan', 'display_name' => 'Hapus Pemasukan Lainnya', 'group' => 'pemasukan', 'description' => 'Dapat menghapus pemasukan lain'],
        ];
        foreach ($perms as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p['name']],
                array_merge($p, ['updated_at' => $now, 'created_at' => $now])
            );
        }

        // Berikan ke role yang punya view_spending (admin/finance).
        $vsId = DB::table('permissions')->where('name', 'view_spending')->value('id');
        $financeRoleIds = $vsId
            ? DB::table('role_permission')->where('permission_id', $vsId)->pluck('role_id')
            : collect();

        $permIds = DB::table('permissions')
            ->whereIn('name', ['view_pemasukan', 'create_pemasukan', 'edit_pemasukan', 'delete_pemasukan'])
            ->pluck('id', 'name');

        foreach ($financeRoleIds as $rid) {
            foreach ($permIds as $pid) {
                DB::table('role_permission')->updateOrInsert(
                    ['role_id' => $rid, 'permission_id' => $pid],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('role_permission')->whereIn('permission_id', function ($q) {
            $q->select('id')->from('permissions')
                ->whereIn('name', ['view_pemasukan', 'create_pemasukan', 'edit_pemasukan', 'delete_pemasukan']);
        })->delete();
        DB::table('permissions')->whereIn('name', ['view_pemasukan', 'create_pemasukan', 'edit_pemasukan', 'delete_pemasukan'])->delete();

        Schema::dropIfExists('pemasukans');
    }
};
