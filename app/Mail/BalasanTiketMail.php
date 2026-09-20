<?php

namespace App\Mail;

use App\Models\CustomerMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;

/**
 * Balasan helpdesk yang dikirim LANGSUNG dari panel admin.
 *
 * Sebelumnya admin harus menempelkan isi balasan dua kali: sekali di klien
 * surel, sekali lagi di kotak "Catat balasan". Di sini satu kali tulis, surel
 * terkirim dan isinya sekaligus masuk linimasa tiket.
 */
class BalasanTiketMail extends Mailable
{
    /**
     * @param  array<int, \App\Models\CustomerMessageAttachment>  $lampiran
     */
    public function __construct(
        public CustomerMessage $pesan,
        public string $isi,
        public array $lampiran = [],
    ) {
        $this->mailer = 'phoenix';
    }

    public function envelope(): Envelope
    {
        $from = config('mail.mailers.phoenix.username', 'halo@phoenixdigitalwarehouse.com');

        return new Envelope(
            from: new Address($from, 'Phoenix Digital'),
            subject: 'Balasan '.$this->pesan->ticket.' — Phoenix Digital',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.balasan-tiket',
            with: [
                'pesan' => $this->pesan,
                'isi' => $this->isi,
            ],
        );
    }

    /** Lampiran admin ikut terkirim; berkasnya ada di disk privat. */
    public function attachments(): array
    {
        return collect($this->lampiran)
            ->filter(fn ($l) => rescue(fn () => Storage::disk('local')->exists($l->path), false, false))
            ->map(fn ($l) => Attachment::fromStorageDisk('local', $l->path)->as($l->nama_asli))
            ->values()
            ->all();
    }
}
