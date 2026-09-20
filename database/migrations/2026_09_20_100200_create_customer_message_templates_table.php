<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Template balasan cepat. Isinya langsung diisikan di migrasi supaya sesudah
 * deploy tombolnya tidak kosong dan tidak butuh perintah seeder susulan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('isi');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        $waktu = now();
        $baku = [
            ['nama' => 'Sapaan pembuka', 'isi' => 'Halo {nama}, terima kasih sudah menghubungi Phoenix Digital (tiket {tiket}). Pesan Anda sudah kami terima dan sedang kami cek ya.'],
            ['nama' => 'Minta detail pesanan', 'isi' => 'Halo {nama}, supaya bisa kami cek lebih cepat, boleh dibantu kirim nomor invoice dan tanggal pesanannya? Terima kasih. (tiket {tiket})'],
            ['nama' => 'Akun sedang disiapkan', 'isi' => 'Halo {nama}, pesanan Anda sedang kami siapkan. Estimasi selesai hari ini pada jam operasional 08.00-21.00 WIB. Nanti kami kabari lagi ya. (tiket {tiket})'],
            ['nama' => 'Penutup selesai', 'isi' => 'Halo {nama}, kendalanya sudah kami tangani. Kalau masih ada yang kurang jelas, balas pesan ini saja ya. Terima kasih sudah memakai Phoenix Digital. (tiket {tiket})'],
        ];

        foreach ($baku as $i => $baris) {
            DB::table('customer_message_templates')->insert($baris + [
                'urutan' => $i,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_message_templates');
    }
};
