<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Izin TAMBAHAN untuk satu orang, di atas izin perannya.
 *
 * Sampai sekarang izin hanya datang dari peran, dan satu pengguna hanya
 * punya satu peran. Akibatnya "Hanifah dan Wiam boleh masuk Orcha, karyawan
 * lain tidak" tidak bisa dituliskan sama sekali — pilihannya cuma memberi
 * akses ke SELURUH karyawan, atau tidak sama sekali.
 *
 * Jalan keluar yang biasa dipakai adalah membuat peran baru hasil salinan:
 * "karyawan-orcha" = sebelas izin karyawan + satu izin Orcha. Itu bekerja
 * sekali, lalu membusuk: dua daftar izin yang harus terus disamakan, dan
 * yang tertinggal selalu yang jarang dibuka. Permintaan berikutnya —
 * "Wiam juga perlu keuangan" — melahirkan peran ketiga, lalu keempat.
 *
 * Tabel ini membalik susunannya: PERAN tetap jadi dasar, ORANG dapat
 * pengecualian. Yang tidak punya baris di sini berperilaku persis seperti
 * sebelumnya.
 *
 * Sengaja hanya MENAMBAH, tidak mencabut. Pencabutan per orang adalah
 * kebutuhan yang jauh lebih jarang, dan menambahkannya sekarang berarti
 * setiap pertanyaan "kenapa dia tidak bisa?" punya dua tempat untuk
 * diperiksa alih-alih satu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $t) {
            $t->id();

            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();

            /*
             | Siapa yang memberikan, dan kapan.
             |
             | Pemberian izin adalah tindakan yang paling mungkin ditanyakan
             | belakangan ("sejak kapan dia bisa membuka ini?"), dan
             | pertanyaan itu tidak bisa dijawab oleh tabel yang hanya berisi
             | dua kunci asing. NAMA, bukan id: tetap terbaca sesudah akun
             | pemberinya hilang.
             */
            $t->string('diberi_oleh', 120)->nullable();
            $t->string('alasan', 255)->nullable();

            $t->timestamps();

            // Satu orang, satu izin, satu baris. Dua baris untuk pasangan
            // yang sama tidak menambah arti apa pun.
            $t->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
