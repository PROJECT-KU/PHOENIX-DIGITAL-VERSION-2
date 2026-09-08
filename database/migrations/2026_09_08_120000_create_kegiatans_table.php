<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kalender kegiatan: rapat, tenggat, kunjungan, libur, dan acara lain yang
 * perlu diketahui bersama.
 *
 * Waktu selesai boleh kosong — banyak kegiatan hanya punya jam mulai dan
 * selesai "kalau sudah selesai". Memaksa mengisinya membuat orang mengarang
 * angka, dan angka karangan lebih buruk daripada kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('jenis', 20)->default('rapat'); // rapat|tenggat|acara|libur|lainnya
            $table->string('lokasi')->nullable();

            $table->dateTime('mulai');
            $table->dateTime('selesai')->nullable();
            $table->boolean('seharian')->default(false);

            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Kalender selalu dibaca per rentang tanggal.
            $table->index('mulai');
        });

        Schema::create('kegiatan_peserta', function (Blueprint $table) {
            // Kunci menaik biasa, bukan UUID: baris pivot dibuat lewat attach(),
            // yang tidak melewati model apa pun sehingga tak ada yang mengisi UUID-nya.
            $table->id();
            $table->foreignUuid('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kegiatan_id', 'user_id']);
        });

        // ===== Izin =====
        $now = now();
        $izin = [
            ['name' => 'view_kegiatan', 'display_name' => 'Kalender Kegiatan', 'group' => 'kegiatan', 'description' => 'Dapat melihat kalender kegiatan'],
            ['name' => 'create_kegiatan', 'display_name' => 'Tambah Kegiatan', 'group' => 'kegiatan', 'description' => 'Dapat menambah kegiatan'],
            ['name' => 'edit_kegiatan', 'display_name' => 'Ubah Kegiatan', 'group' => 'kegiatan', 'description' => 'Dapat mengubah kegiatan'],
            ['name' => 'delete_kegiatan', 'display_name' => 'Hapus Kegiatan', 'group' => 'kegiatan', 'description' => 'Dapat menghapus kegiatan'],
        ];

        foreach ($izin as $i) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $i['name']],
                array_merge($i, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $idIzin = DB::table('permissions')->whereIn('name', array_column($izin, 'name'))->pluck('id', 'name');

        // MELIHAT kalender diberikan ke SEMUA peran: kegiatan bersama tidak ada
        // gunanya bila hanya sebagian orang tahu. Menambah/mengubah/menghapus
        // menyusul peran yang sudah mengelola karyawan.
        foreach (DB::table('roles')->pluck('id') as $idPeran) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $idPeran, 'permission_id' => $idIzin['view_kegiatan']],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $idKaryawan = DB::table('permissions')->where('name', 'view_karyawan')->value('id');

        $peranPengelola = $idKaryawan
            ? DB::table('role_permission')->where('permission_id', $idKaryawan)->pluck('role_id')
            : collect();

        foreach ($peranPengelola as $idPeran) {
            foreach (['create_kegiatan', 'edit_kegiatan', 'delete_kegiatan'] as $nama) {
                DB::table('role_permission')->updateOrInsert(
                    ['role_id' => $idPeran, 'permission_id' => $idIzin[$nama]],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_peserta');
        Schema::dropIfExists('kegiatans');

        $id = DB::table('permissions')
            ->whereIn('name', ['view_kegiatan', 'create_kegiatan', 'edit_kegiatan', 'delete_kegiatan'])
            ->pluck('id');

        DB::table('role_permission')->whereIn('permission_id', $id)->delete();
        DB::table('permissions')->whereIn('id', $id)->delete();
    }
};
