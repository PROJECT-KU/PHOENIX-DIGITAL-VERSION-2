<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tanda terima untuk pengirim pesan / pelamar dari situs Asthana.
 *
 * Lewat mailer bawaan (kotak surat Asthana), bukan mailer 'phoenix': yang
 * menulis ke situs Asthana harus menerima balasan atas nama Asthana.
 */
class TandaTerimaSitusMail extends Mailable
{
    /**
     * @param  'pesan'|'lamaran'  $jenis
     */
    public function __construct(
        public string $jenis,
        public string $nama,
        public ?string $posisi = null,
        public ?string $nomor = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'PT. Asthana Cipta Mandiri'),
            subject: $this->jenis === 'lamaran'
                ? 'Lamaran Anda sudah kami terima — PT. Asthana Cipta Mandiri'
                : 'Pesan Anda sudah kami terima — PT. Asthana Cipta Mandiri',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tanda-terima-situs',
            with: ['situs' => config('asthana.situs_url')],
        );
    }
}
