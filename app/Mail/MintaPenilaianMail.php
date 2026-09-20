<?php

namespace App\Mail;

use App\Models\CustomerMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

/**
 * Ajakan menilai penanganan, dikirim saat tiket ditandai Selesai.
 *
 * Tanpa ini penilaian hanya datang dari pelanggan yang kebetulan membuka
 * halaman statusnya — angkanya jadi mewakili segelintir orang saja.
 */
class MintaPenilaianMail extends Mailable
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
            subject: 'Bagaimana penanganan kami? — '.$this->pesan->ticket,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.minta-penilaian',
            with: [
                'pesan' => $this->pesan,
                'tautan' => URL::temporarySignedRoute(
                    'tiket.lacak',
                    now()->addDays((int) config('helpdesk.masa_tautan_hari', 90)),
                    ['ticket' => $this->pesan->ticket],
                ),
            ],
        );
    }
}
