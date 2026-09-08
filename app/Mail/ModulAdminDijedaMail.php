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
 */
class ModulAdminDijedaMail extends Mailable
{
    public function __construct(
        public string $namaModul,
        public bool $ditutup,
        public string $pesan,
        public string $olehSiapa,
    ) {}

    public function build()
    {
        $judul = $this->ditutup
            ? 'Modul '.$this->namaModul.' ditutup sementara'
            : 'Modul '.$this->namaModul.' dibuka kembali';

        return $this->subject($judul.' — lemon by ACM')
            ->view('emails.modul-admin-dijeda', [
                'judul' => $judul,
                'namaModul' => $this->namaModul,
                'ditutup' => $this->ditutup,
                'pesan' => $this->pesan,
                'olehSiapa' => $this->olehSiapa,
            ]);
    }
}
