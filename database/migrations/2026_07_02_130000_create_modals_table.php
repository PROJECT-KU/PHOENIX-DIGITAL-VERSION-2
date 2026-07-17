<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('jenis')->default('operasional'); // operasional (manual) | pembelian_akun (auto, tidak disimpan di sini)
            $table->text('deskripsi')->nullable();
            $table->foreignId('penginput_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'tanggal']);
        });

        // ===== Permissions modal =====
        $now = now();
        $perms = [
            ['name' => 'view_modal', 'display_name' => 'Lihat Modal', 'group' => 'modal', 'description' => 'Dapat melihat modal operasional & modal pembelian akun'],
            ['name' => 'create_modal', 'display_name' => 'Tambah Modal', 'group' => 'modal', 'description' => 'Dapat menambah modal operasional'],
            ['name' => 'edit_modal', 'display_name' => 'Edit Modal', 'group' => 'modal', 'description' => 'Dapat mengubah modal operasional'],
            ['name' => 'delete_modal', 'display_name' => 'Hapus Modal', 'group' => 'modal', 'description' => 'Dapat menghapus modal operasional'],
        ];
        foreach ($perms as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p['name']],
                array_merge($p, ['updated_at' => $now, 'created_at' => $now])
            );
        }

        // Berikan ke role yang sudah punya view_spending (admin/finance).
        $vsId = DB::table('permissions')->where('name', 'view_spending')->value('id');
        $financeRoleIds = $vsId
            ? DB::table('role_permission')->where('permission_id', $vsId)->pluck('role_id')
            : collect();

        $permIds = DB::table('permissions')
            ->whereIn('name', ['view_modal', 'create_modal', 'edit_modal', 'delete_modal'])
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
                ->whereIn('name', ['view_modal', 'create_modal', 'edit_modal', 'delete_modal']);
        })->delete();
        DB::table('permissions')->whereIn('name', ['view_modal', 'create_modal', 'edit_modal', 'delete_modal'])->delete();

        Schema::dropIfExists('modals');
    }
};
