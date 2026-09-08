<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Kabar ke PEMBELI bahwa satu halaman toko sedang diperbaiki.
 *
 * Nadanya berbeda dari kabar ke karyawan: pembeli tidak peduli istilah modul
 * atau izin — yang ia perlu tahu hanyalah apa yang sedang tidak bisa dipakai,
 * sampai kapan, dan ke mana bertanya bila mendesak.
 *
 * Selalu membawa versi teks di samping HTML, karena surel HTML-saja adalah
 * penanda spam yang paling sering dipakai penyaring.
 */
class FiturPublikDitutupMail extends Mailable
{
    public function __construct(
        public string $namaHalaman,
        public bool $ditutup,
        public string $pesan,
        public ?\Illuminate\Support\Carbon $mulai = null,
        public ?\Illuminate\Support\Carbon $sampai = null,
    ) {}

    public function build()
    {
        $judul = $this->ditutup
            ? $this->namaHalaman.' sedang kami perbaiki'
            : $this->namaHalaman.' sudah bisa diakses lagi';

        $isi = [
            'judul' => $judul,
            'namaHalaman' => $this->namaHalaman,
            'ditutup' => $this->ditutup,
            'pesan' => $this->pesan,
            'mulai' => $this->mulai,
            'sampai' => $this->sampai,
        ];

        return $this->subject($judul.' — Phoenix Digital')
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->text('emails.fitur-publik-ditutup-teks', $isi)
            ->view('emails.fitur-publik-ditutup', $isi);
    }
}
