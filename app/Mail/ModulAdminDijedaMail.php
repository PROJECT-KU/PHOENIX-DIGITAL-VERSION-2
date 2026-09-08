<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Kabar bahwa satu modul admin ditutup atau dibuka kembali.
 *
 * Dikirim sinkron seperti NikReminderMail: kejadiannya jarang, dan antrean di
 * server ini tidak bisa diandalkan karena proc_open dimatikan hosting.
 * Kegagalan kirim TIDAK boleh membatalkan penutupan modulnya — itu diurus
 * pemanggilnya, bukan di sini.
 *
 * Selalu membawa versi TEKS di samping HTML-nya. Surel yang hanya berisi HTML
 * adalah salah satu penanda spam yang paling sering dipakai penyaring, dan
 * kabar ini justru yang tidak boleh nyasar ke folder spam: karyawan yang tidak
 * membacanya akan mengira sistemnya rusak. Alamat balasan juga diarahkan ke
 * kotak yang sungguh dibaca, bukan alamat pengirim otomatis.
 */
class ModulAdminDijedaMail extends Mailable
{
    public function __construct(
        public string $namaModul,
        public bool $ditutup,
        public string $pesan,
        public string $olehSiapa,
        public ?\Illuminate\Support\Carbon $mulai = null,
        public ?\Illuminate\Support\Carbon $sampai = null,
    ) {}

    public function build()
    {
        $judul = $this->ditutup
            ? 'Modul '.$this->namaModul.' ditutup sementara'
            : 'Modul '.$this->namaModul.' dibuka kembali';

        $isi = [
            'judul' => $judul,
            'namaModul' => $this->namaModul,
            'ditutup' => $this->ditutup,
            'pesan' => $this->pesan,
            'olehSiapa' => $this->olehSiapa,
            'mulai' => $this->mulai,
            'sampai' => $this->sampai,
        ];

        // Kabar internal tetap atas nama perusahaan — panel lemon bernaung di
        // sana, dan karyawan mengenalnya. Dipatok tegas, bukan mengandalkan
        // bawaan, supaya tidak ikut berubah bila pengirim bawaan diganti.
        $dari = config('mail.from.address');

        return $this->subject($judul.' — lemon by ACM')
            ->from($dari, config('mail.from.name'))
            ->replyTo($dari, config('mail.from.name'))
            ->text('emails.modul-admin-dijeda-teks', $isi)
            ->view('emails.modul-admin-dijeda', $isi);
    }
}
