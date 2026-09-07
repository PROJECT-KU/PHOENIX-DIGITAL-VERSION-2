<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Izin untuk halaman Jeda Layanan.
 *
 * Dipisah dari izin produk supaya menutup-membuka layanan bisa diberikan
 * kepada orang yang TIDAK boleh mengubah harga atau data produk — mis. staf
 * yang berjaga saat penyedia pengecekan sedang gangguan.
 */
return new class extends Migration
{
    private const IZIN = [
        ['name' => 'view_jeda_layanan', 'display_name' => 'Jeda Layanan', 'group' => 'jasa', 'description' => 'Dapat melihat status buka/tutup layanan jasa'],
        ['name' => 'manage_jeda_layanan', 'display_name' => 'Kelola Jeda Layanan', 'group' => 'jasa', 'description' => 'Dapat menutup & membuka pemesanan layanan jasa'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::IZIN as $izin) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $izin['name']],
                array_merge($izin, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $idIzin = DB::table('permissions')
            ->whereIn('name', array_column(self::IZIN, 'name'))
            ->pluck('id', 'name');

        // Peran yang sudah boleh mengubah produk dianggap pengelola toko, jadi
        // langsung diberi kedua izin ini — tanpa itu fiturnya tak terlihat
        // siapa pun setelah deploy, dan admin mengira menunya gagal terpasang.
        $idEditProduct = DB::table('permissions')->where('name', 'edit_product')->value('id');

        $peranPengelola = $idEditProduct
            ? DB::table('role_permission')->where('permission_id', $idEditProduct)->pluck('role_id')
            : collect();

        foreach ($peranPengelola as $idPeran) {
            foreach ($idIzin as $pid) {
                DB::table('role_permission')->updateOrInsert(
                    ['role_id' => $idPeran, 'permission_id' => $pid],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        $idIzin = DB::table('permissions')
            ->whereIn('name', array_column(self::IZIN, 'name'))
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $idIzin)->delete();
        DB::table('permissions')->whereIn('id', $idIzin)->delete();
    }
};
