<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Pemberitahuan ke pelamar saat admin mengubah tahap seleksi lamarannya.
 * Templat per tahap + pesan opsional dari admin (mis. jadwal wawancara).
 * Lewat mailer bawaan atas nama Asthana, sama dengan TandaTerimaSitusMail.
 */
class StatusLamaranMail extends Mailable
{
    /** Judul surel & kalimat pembuka per tahap. */
    public const TEMPLAT = [
        'diseleksi' => [
            'Lamaran Anda sedang kami seleksi',
            'Kabar baik: lamaran Anda sudah masuk tahap seleksi berkas. Tim kami sedang meninjau CV dan berkas Anda.',
        ],
        'wawancara' => [
            'Undangan wawancara',
            'Selamat, Anda lolos seleksi berkas! Kami ingin mengenal Anda lebih jauh lewat wawancara.',
        ],
        'diterima' => [
            'Selamat, Anda diterima',
            'Dengan senang hati kami sampaikan bahwa Anda diterima. Selamat bergabung!',
        ],
        'ditolak' => [
            'Kabar mengenai lamaran Anda',
            'Terima kasih atas waktu dan minat Anda. Setelah meninjau dengan saksama, untuk saat ini kami belum dapat melanjutkan lamaran Anda. Data Anda tetap kami simpan untuk peluang lain yang mungkin lebih sesuai.',
        ],
    ];

    public function __construct(
        public JobApplication $lamaran,
        public ?string $pesan = null,
    ) {}

    public static function adaTemplat(string $status): bool
    {
        return isset(self::TEMPLAT[$status]);
    }

    public function envelope(): Envelope
    {
        $judul = self::TEMPLAT[$this->lamaran->status][0] ?? 'Kabar lamaran Anda';

        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'PT. Asthana Cipta Mandiri'),
            subject: "{$judul} — {$this->lamaran->nomor_lamaran}",
        );
    }

    public function content(): Content
    {
        $situs = config('asthana.situs_url');

        return new Content(
            view: 'emails.status-lamaran',
            with: [
                'judul' => self::TEMPLAT[$this->lamaran->status][0] ?? 'Kabar lamaran Anda',
                'pembuka' => self::TEMPLAT[$this->lamaran->status][1] ?? '',
                'posisi' => $this->lamaran->job?->title,
                'urlStatus' => $situs
                    ? $situs.'/karir/status.html?'.http_build_query(['nomor' => $this->lamaran->nomor_lamaran])
                    : null,
            ],
        );
    }
}
