<?php

namespace Tests\Feature;

use App\Support\RingkasanOtomatis;
use Tests\TestCase;

/**
 * Pembuat ringkasan otomatis untuk blog Orcha.
 *
 * Dua hal yang dijaga di sini pernah membuat tombol "Acak lagi" tampak rusak:
 * ia mengembalikan kalimat yang sama terus-menerus. Tidak ada galat apa pun —
 * admin hanya menekannya berkali-kali dan menyimpulkan fiturnya tidak jalan.
 */
class RingkasanOtomatisTest extends TestCase
{
    /**
     * Batas antar-blok harus jadi spasi.
     *
     * strip_tags() menyambung "<p>A.</p><p>B.</p>" jadi "A.B." — tanpa spasi
     * sesudah titik. Akibatnya pemecah kalimat gagal, seluruh paragraf menyatu
     * jadi satu kalimat raksasa yang lalu dibuang karena melebihi 240 huruf.
     * Pada satu artikel nyata, 19 dari 33 kalimat hilang karena ini.
     */
    public function test_paragraf_tidak_berdempet_saat_tag_dibuang(): void
    {
        $html = '<p>Kalimat pertama.</p><p>Kalimat kedua.</p><ul><li>Poin satu.</li><li>Poin dua.</li></ul>';

        $teks = RingkasanOtomatis::teksPolos($html);

        $this->assertStringNotContainsString('pertama.Kalimat', $teks);
        $this->assertStringContainsString('pertama. Kalimat kedua.', $teks);
        $this->assertStringContainsString('satu. Poin dua.', $teks);
    }

    public function test_br_juga_dihitung_sebagai_jarak(): void
    {
        $this->assertStringContainsString(
            'Atas. Bawah',
            RingkasanOtomatis::teksPolos('<p>Atas.<br>Bawah.</p>')
        );
    }

    /**
     * "Acak lagi" harus benar-benar berganti.
     *
     * Sebelumnya kandidatnya disaring "skor dalam jarak 2 dari tertinggi".
     * Aturan itu runtuh begitu satu kalimat menang telak — pada artikel nyata
     * skornya 7 lalu 4, sehingga jendelanya memuat SATU kalimat saja dan
     * pengacaknya selalu mengambil yang sama.
     */
    public function test_acak_menghasilkan_kalimat_yang_berbeda_beda(): void
    {
        $teks = RingkasanOtomatis::teksPolos($this->artikelPanjang());

        $hasil = [];
        for ($i = 0; $i < 12; $i++) {
            $hasil[] = RingkasanOtomatis::kalimatMenarik($teks, 200);
        }

        $this->assertGreaterThan(
            1,
            count(array_unique($hasil)),
            'Tombol "Acak lagi" mengembalikan kalimat yang sama terus — kandidatnya terlalu sempit.'
        );
    }

    /** Kalimat yang sedang dipakai tidak boleh terpilih lagi. */
    public function test_kalimat_yang_dikecualikan_tidak_pernah_terpilih(): void
    {
        $teks = RingkasanOtomatis::teksPolos($this->artikelPanjang());

        $pertama = RingkasanOtomatis::kalimatMenarik($teks, 200);

        for ($i = 0; $i < 12; $i++) {
            $this->assertNotSame(
                $pertama,
                RingkasanOtomatis::kalimatMenarik($teks, 200, $pertama),
                'Kalimat yang sedang dipakai masih bisa terpilih ulang.'
            );
        }
    }

    /** Isi kosong tidak boleh meledak, cukup kembalikan kosong. */
    public function test_isi_kosong_aman(): void
    {
        $this->assertSame('', RingkasanOtomatis::kalimatMenarik('', 200));
        $this->assertSame('', RingkasanOtomatis::teksPolos(''));
    }

    /**
     * Artikel contoh dengan banyak kalimat yang panjangnya layak (40–240 huruf),
     * meniru bentuk tulisan sungguhan.
     */
    private function artikelPanjang(): string
    {
        $kalimat = [
            'Panduan ini menjelaskan cara memilih perlengkapan yang benar sebelum berangkat pagi.',
            'Tips paling penting adalah menyiapkan jaket tebal karena suhunya bisa turun drastis.',
            'Banyak peserta lupa membawa sarung tangan, dan itu kesalahan yang paling sering terjadi.',
            'Langkah pertama yang wajib dilakukan adalah memeriksa perkiraan cuaca dua hari sebelumnya.',
            'Cara termudah menghemat waktu adalah menyiapkan tas sejak malam sebelum keberangkatan.',
            'Keuntungan berangkat lebih awal adalah jalur menuju titik pandang masih relatif kosong.',
            'Hindari membawa barang berlebihan karena jalur setapaknya cukup menanjak dan licin.',
            'Manfaat memakai sepatu bersol kasar baru terasa saat menuruni jalur berbatu itu.',
        ];

        return '<p>'.implode('</p><p>', $kalimat).'</p>';
    }
}
