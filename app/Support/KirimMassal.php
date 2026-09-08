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
                Log::warning('Kirim massal gagal untuk satu kelompok: '.$e->getMessage());
            }
        }

        return $terkirim;
    }
}
