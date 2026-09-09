<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mengirim satu kabar ke banyak alamat TANPA saling membocorkan.
 *
 * `Mail::to([...])` menaruh seluruh alamat di kolom To, sehingga setiap
 * penerima melihat surel semua orang lain. Untuk karyawan itu sudah tidak
 * pantas; untuk 123 pelanggan itu kebocoran data pelanggan.
 *
 * Karena itu penerimanya selalu masuk BCC, dan kolom To diisi alamat pengirim
 * sendiri supaya suratnya tetap sah di mata penyaring.
 *
 * Dipecah per kelompok karena sebagian server surel menolak jumlah penerima
 * yang terlalu besar dalam satu pesan.
 */
class KirimMassal
{
    public const PER_KELOMPOK = 40;

    /**
     * @param  array<int, string>  $penerima
     * @param  callable():Mailable  $buatSurat  dipanggil sekali per kelompok,
     *                                          karena satu Mailable tidak boleh
     *                                          dipakai ulang antar pengiriman
     * @param  string|null  $mailer  nama sambungan SMTP. WAJIB disebut bila
     *                               pengirimnya bukan kotak bawaan: sifat
     *                               $mailer di dalam Mailable TIDAK terbawa
     *                               lewat Mail::to(), dan server menolaknya
     *                               dengan "553 Sender address rejected:
     *                               not owned by user ...". Kode lain di proyek
     *                               ini pun selalu menyebutnya tegas.
     * @return int jumlah alamat yang berhasil dikirimi
     */
    public static function bcc(array $penerima, callable $buatSurat, ?string $mailer = null): int
    {
        $penerima = array_values(array_unique(array_filter(
            $penerima,
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        )));

        if (empty($penerima)) {
            return 0;
        }

        // Kolom To diisi alamat pengirimnya sendiri; bila memakai sambungan
        // lain, alamat itu harus milik sambungan tersebut agar tidak ditolak.
        $dari = $mailer
            ? config("mail.mailers.{$mailer}.username", config('mail.from.address'))
            : config('mail.from.address');

        $terkirim = 0;

        foreach (array_chunk($penerima, self::PER_KELOMPOK) as $kelompok) {
            try {
                $pengirim = $mailer ? Mail::mailer($mailer) : Mail::mailer();
                $pengirim->to($dari)->bcc($kelompok)->send($buatSurat());
                $terkirim += count($kelompok);
            } catch (\Throwable $e) {
                // Satu kelompok gagal tidak boleh menghentikan sisanya.
                //
                // Dicatat sebagai GALAT, bukan peringatan: server memakai
                // LOG_LEVEL=error, sehingga peringatan tidak pernah sampai ke
                // berkas log. Kabar yang gagal terkirim tanpa jejak apa pun
                // adalah keadaan terburuk — orang mengira sudah memberi tahu,
                // penerimanya mengira tidak ada apa-apa, dan tidak seorang pun
                // punya cara mengetahuinya.
                Log::error('Kirim massal gagal untuk satu kelompok ('.count($kelompok).' alamat): '.$e->getMessage());
            }
        }

        return $terkirim;
    }

    /**
     * Kirim satu kabar ke tiap penerima SECARA TERPISAH, satu surat per orang.
     *
     * Bedanya dengan bcc(): di sini kolom To berisi alamat penerimanya sendiri.
     * Itu penting untuk kabar internal yang jumlahnya sedikit, karena surat
     * yang kolom To-nya bukan si penerima — dan daftar penerimanya kosong —
     * adalah pola yang dipakai pengirim massal, dan penyaring Gmail
     * memperlakukannya begitu. Undangan rapat yang mendarat di folder spam
     * sama saja dengan undangan yang tidak pernah dikirim.
     *
     * Karena tiap orang menerima suratnya sendiri, tidak ada yang bisa melihat
     * alamat rekannya — kerahasiaan yang sama seperti bcc(), tanpa harganya.
     *
     * Hanya untuk kelompok kecil (karyawan, peserta rapat). Untuk ratusan
     * pelanggan tetap pakai bcc(): seratus sambungan SMTP dalam satu permintaan
     * web akan kehabisan waktu jauh sebelum selesai.
     *
     * @param  array<int, string>  $penerima
     * @param  callable():Mailable  $buatSurat  dipanggil sekali per penerima
     * @return int jumlah alamat yang berhasil dikirimi
     */
    public static function perOrang(array $penerima, callable $buatSurat, ?string $mailer = null): int
    {
        $penerima = array_values(array_unique(array_filter(
            $penerima,
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        )));

        $terkirim = 0;

        foreach ($penerima as $alamat) {
            try {
                $pengirim = $mailer ? Mail::mailer($mailer) : Mail::mailer();
                $pengirim->to($alamat)->send($buatSurat());
                $terkirim++;
            } catch (\Throwable $e) {
                // Satu alamat gagal tidak boleh menghentikan sisanya; galat,
                // bukan peringatan, dengan alasan yang sama seperti di bcc().
                Log::error('Kirim ke '.$alamat.' gagal: '.$e->getMessage());
            }
        }

        return $terkirim;
    }
}
