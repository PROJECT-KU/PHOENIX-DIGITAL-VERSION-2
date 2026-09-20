<?php

namespace App\Mail;

use App\Models\CustomerMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

/**
 * Tanda terima ke pelanggan begitu pesannya masuk.
 *
 * Nomor tiket sudah dibuat sejak awal dan dipakai di seluruh panel admin, tapi
 * sebelumnya tidak pernah sampai ke pemiliknya — pelanggan hanya melihat toast
 * "Pesan terkirim" lalu tidak punya apa-apa untuk ditanyakan kembali.
 */
class TiketDiterimaMail extends Mailable
{
    public function __construct(public CustomerMessage $pesan)
    {
        $this->mailer = 'phoenix';
    }

    public function envelope(): Envelope
    {
        $from = config('mail.mailers.phoenix.username', 'halo@phoenixdigitalwarehouse.com');

        return new Envelope(
            from: new Address($from, 'Phoenix Digital'),
            subject: 'Pesan Anda kami terima — '.$this->pesan->ticket,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tiket-diterima',
            with: [
                'pesan' => $this->pesan,
                // Tautan bertanda tangan & BERBATAS WAKTU: pelanggan tidak
                // perlu mengingat apa pun, tiket orang lain tidak bisa ditebak
                // dari URL, dan surel yang diteruskan tidak memberi akses
                // selamanya.
                'tautan' => URL::temporarySignedRoute(
                    'tiket.lacak',
                    now()->addDays((int) config('helpdesk.masa_tautan_hari', 90)),
                    ['ticket' => $this->pesan->ticket],
                ),
                'batas' => $this->pesan->tenggat()?->locale('id')->translatedFormat('l, d F Y H:i'),
            ],
        );
    }
}
