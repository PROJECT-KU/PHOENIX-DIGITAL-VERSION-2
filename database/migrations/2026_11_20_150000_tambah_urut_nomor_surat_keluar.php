<?php

use App\Support\Surat\NomorSuratKeluar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tiga kolom pembantu penomoran surat keluar.
 *
 * Nomor surat keluar ACM berbentuk 016/03.D/ACM/VI/2026, dan dua angka di
 * depannya adalah DUA hitungan yang berjalan sendiri-sendiri: 016 nomor urut
 * seluruh surat keluar tahun itu, 03 nomor urut surat keluar berklasifikasi
 * D saja.
 *
 * Keduanya disimpan sebagai ANGKA, bukan dibaca ulang dari teks nomornya
 * tiap kali. Mengurutkan teks akan menaruh "100" sebelum "99", dan nomor yang
 * ditulis tangan sedikit berbeda ("16/3.D/...") akan hilang dari hitungan
 * tanpa ada yang tahu. Angkanya diisi sendiri setiap kali surat disimpan,
 * dibaca dari nomor yang BENAR-BENAR tertulis — jadi nomor yang diketik
 * manual pun tetap menyambung rantainya, bukan memutuskannya.
 *
 * kode_klasifikasi ikut disimpan apa adanya ("D"), bukan ditarik lewat relasi
 * ke master klasifikasi: kode di master boleh diubah admin bertahun-tahun
 * kemudian, dan hitungan nomor surat yang sudah terbit tidak boleh ikut
 * bergeser karenanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $t) {
            $t->unsignedInteger('urut_utama')->nullable()->after('nomor_surat');
            $t->unsignedInteger('urut_klasifikasi')->nullable()->after('urut_utama');
            $t->string('kode_klasifikasi', 20)->nullable()->after('urut_klasifikasi');

            $t->index(['arah', 'urut_utama'], 'surat_arah_urut_idx');
        });

        // Surat keluar yang sudah terlanjur tercatat ikut dibaca nomornya,
        // supaya nomor berikutnya menyambung dan tidak mengulang dari satu.
        DB::table('surat')->where('arah', 'keluar')->whereNotNull('nomor_surat')
            ->orderBy('id')->select('id', 'nomor_surat')->chunk(200, function ($baris) {
                foreach ($baris as $s) {
                    $urai = NomorSuratKeluar::urai((string) $s->nomor_surat);

                    if ($urai === null) {
                        continue;
                    }

                    DB::table('surat')->where('id', $s->id)->update([
                        'urut_utama' => $urai['urut_utama'],
                        'urut_klasifikasi' => $urai['urut_klasifikasi'],
                        'kode_klasifikasi' => $urai['kode_klasifikasi'],
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $t) {
            $t->dropIndex('surat_arah_urut_idx');
            $t->dropColumn(['urut_utama', 'urut_klasifikasi', 'kode_klasifikasi']);
        });
    }
};
