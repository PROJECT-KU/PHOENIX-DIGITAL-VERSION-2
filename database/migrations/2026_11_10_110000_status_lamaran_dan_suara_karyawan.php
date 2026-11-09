<?php

use App\Models\JobApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Halaman karir tahap 2:
 * - lamaran punya nomor berkode (AST/RKA/ORC-ddmmyy-ACAK) dan status seleksi
 *   yang bisa dicek pelamar sendiri di situs;
 * - "Suara Karyawan": kutipan karyawan untuk halaman karir.
 *
 * Murni tambahan. Lamaran lama diberi nomor (dari tanggal kirim & unit
 * lowongannya) dan status 'baru'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->string('nomor_lamaran', 30)->nullable()->unique()->after('id');
            $table->string('status', 20)->default('baru')->index()->after('sumber_info');
            $table->timestamp('status_diubah_at')->nullable()->after('status');
            $table->text('catatan_internal')->nullable()->after('status_diubah_at');
        });

        // Nomor untuk lamaran yang sudah ada.
        DB::table('tbl_job_applications')->whereNull('nomor_lamaran')->orderBy('id')
            ->each(function ($baris) {
                $unit = $baris->job_id ? DB::table('lowongans')->where('id', $baris->job_id)->value('unit') : null;
                DB::table('tbl_job_applications')->where('id', $baris->id)->update([
                    'nomor_lamaran' => JobApplication::buatNomor($unit, Carbon::parse($baris->created_at)),
                ]);
            });

        Schema::create('asthana_suara_karyawan', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('jabatan');
            $t->string('perusahaan')->nullable();
            $t->text('isi');
            $t->string('foto')->nullable();
            $t->boolean('tampil')->default(true);
            $t->unsignedInteger('urutan')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asthana_suara_karyawan');

        Schema::table('tbl_job_applications', function (Blueprint $table) {
            $table->dropUnique(['nomor_lamaran']);
            $table->dropIndex(['status']);
            $table->dropColumn(['nomor_lamaran', 'status', 'status_diubah_at', 'catatan_internal']);
        });
    }
};
